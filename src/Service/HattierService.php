<?php
namespace App\Service;

use App\Entity\Enchantment;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\User;
use App\Entity\UserUnlockedAura;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Functions\ArrayFunctions;
use App\Repository\EnchantmentRepository;
use App\Repository\UserUnlockedAuraRepository;
use Doctrine\ORM\EntityManagerInterface;

class HattierService
{
    private EnchantmentRepository $enchantmentRepository;
    private UserUnlockedAuraRepository $userUnlockedAuraRepository;
    private EntityManagerInterface $em;
    private ResponseService $responseService;
    private CommentFormatter $commentFormatter;

    public function __construct(
        EnchantmentRepository $enchantmentRepository, ResponseService $responseService, CommentFormatter $commentFormatter,
        UserUnlockedAuraRepository $userUnlockedAuraRepository, EntityManagerInterface $em
    )
    {
        $this->enchantmentRepository = $enchantmentRepository;
        $this->userUnlockedAuraRepository = $userUnlockedAuraRepository;
        $this->em = $em;
        $this->responseService = $responseService;
        $this->commentFormatter = $commentFormatter;
    }

    public function userHasUnlocked(User $user, Enchantment $enchantment): bool
    {
        $cacheKey = $user->getId() . '-' . $enchantment->getId();

        if(!array_key_exists($cacheKey, $this->userAurasPerRequestCache))
        {
            $this->userAurasPerRequestCache[$cacheKey] = $this->userUnlockedAuraRepository->findOneBy([
                'user' => $user,
                'aura' => $enchantment
            ]);
        }

        return $this->userAurasPerRequestCache[$cacheKey] !== null;
    }

    public function getAurasAvailable(User $user): array
    {
        $allAuras = $this->enchantmentRepository->createQueryBuilder('e')
            ->andWhere('e.aura IS NOT NULL')
            ->getQuery()
            ->execute()
        ;

        $unlocked = $user->getUnlockedAuras()->toArray();

        return array_map(
            function(Enchantment $e) use($unlocked) {
                $unlockedAura = ArrayFunctions::find_one($unlocked, fn(UserUnlockedAura $a) => $a->getAura()->getId() === $e->getId());

                if($unlockedAura)
                {
                    return [
                        'id' => $unlockedAura->getId(),
                        'unlockedOn' => $unlockedAura->getUnlockedOn(),
                        'comment' => $this->commentFormatter->format($unlockedAura->getComment()),
                        'name' => $e->getAura()->getName(),
                        'aura' => $e->getAura(),
                    ];
                }
                else
                {
                    return [
                        'id' => null,
                        'unlockedOn' => null,
                        'comment' => null,
                        'name' => $e->getAura()->getName(),
                        'aura' => null,
                    ];
                }
            },
            $allAuras
        );
    }

    private $userAurasPerRequestCache = [];

    public function unlockAura(User $user, Enchantment $enchantment, string $comment, PetActivityLog $activityLog, ?string $customActivityUnlockMessage = null): UserUnlockedAura
    {
        $cacheKey = $user->getId() . '-' . $enchantment->getId();

        if(!array_key_exists($cacheKey, $this->userAurasPerRequestCache) || $this->userAurasPerRequestCache[$cacheKey] === null)
        {
            $unlockedAura = $this->userUnlockedAuraRepository->findOneBy([
                'user' => $user,
                'aura' => $enchantment
            ]);

            if(!$unlockedAura)
            {
                $unlockedAura = (new UserUnlockedAura())
                    ->setUser($user)
                    ->setAura($enchantment)
                    ->setComment($comment)
                ;

                $this->em->persist($unlockedAura);

                if(!$user->getUnlockedHattier())
                {
                    $user->setUnlockedHattier();

                    if($customActivityUnlockMessage)
                        $activityLog->setEntry($activityLog->getEntry() . ' ' . $customActivityUnlockMessage);
                    else
                        $activityLog->setEntry($activityLog->getEntry() . ' (The Hattier has been unlocked! Check it out in the menu!)');

                    $this->unlockStartingAuras($user);
                }
                else
                    $activityLog->setEntry($activityLog->getEntry() . ' (A new style has been added to the Hattier!)');
            }

            $this->userAurasPerRequestCache[$cacheKey] = $unlockedAura;
        }

        return $this->userAurasPerRequestCache[$cacheKey];
    }

    private function unlockStartingAuras(User $user)
    {
        $startingAuras = $this->enchantmentRepository->findBy([
            'name' => [
                'Bubbling',
                '(New!)',
                'of Squares',
                'with Paint'
            ],
        ]);

        foreach($startingAuras as $aura)
        {
            $cacheKey = $user->getId() . '-' . $aura->getId();

            $unlockedAura = (new UserUnlockedAura())
                ->setUser($user)
                ->setAura($aura)
                ->setComment('The Hattier has made this style available to you as a courtesy.')
                ->setUnlockedOn(\DateTimeImmutable::createFromFormat('Y-m-d', '2019-06-22'))
            ;

            $this->em->persist($unlockedAura);

            $this->userAurasPerRequestCache[$cacheKey] = $unlockedAura;
        }
    }

    /**
     * @param string|Enchantment $enchantmentName
     */
    public function petMaybeUnlockAura(
        Pet $pet, $enchantment, string $logIfHatGetsEnchanted, string $logIfHatDoesNotGetEnchanted,
        string $auraUnlockMessage
    ): ?PetActivityLog
    {
        if(is_string($enchantment))
            $enchantment = $this->enchantmentRepository->findOneByName($enchantment);

        if($this->userHasUnlocked($pet->getOwner(), $enchantment))
            return null;

        if($pet->getHat() && !$pet->getHat()->getEnchantment())
        {
            $activityLog = $this->responseService->createActivityLog($pet, $logIfHatGetsEnchanted, '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
            ;

            $pet->getHat()->setEnchantment($enchantment);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, $logIfHatDoesNotGetEnchanted, '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
            ;
        }

        $this->unlockAura($pet->getOwner(), $enchantment, $auraUnlockMessage, $activityLog);

        return $activityLog;
    }

    public function unlockAuraDuringPetActivity(
        Pet $pet, PetActivityLog $activityLog, Enchantment $enchantment,
        string $addedToHatDescription,
        string $notAddedToHatDescription,
        string $auraUnlockMessage
    )
    {
        if($pet->getHat() && !$pet->getHat()->getEnchantment())
        {
            $activityLog
                ->setEntry($activityLog->getEntry() . ' ' . $addedToHatDescription)
                ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
            ;

            $pet->getHat()->setEnchantment($enchantment);
        }
        else
        {
            $activityLog
                ->setEntry($activityLog->getEntry() . ' ' . $notAddedToHatDescription)
                ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
            ;
        }

        $this->unlockAura($pet->getOwner(), $enchantment, $auraUnlockMessage, $activityLog);
    }
}