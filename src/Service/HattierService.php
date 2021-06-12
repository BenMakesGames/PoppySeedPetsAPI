<?php
namespace App\Service;

use App\Entity\Enchantment;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\User;
use App\Entity\UserUnlockedAura;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Functions\ActivityHelpers;
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

    public function __construct(
        EnchantmentRepository $enchantmentRepository, ResponseService $responseService,
        UserUnlockedAuraRepository $userUnlockedAuraRepository,
        EntityManagerInterface $em
    )
    {
        $this->enchantmentRepository = $enchantmentRepository;
        $this->userUnlockedAuraRepository = $userUnlockedAuraRepository;
        $this->em = $em;
        $this->responseService = $responseService;
    }

    public function userHasUnlocked(User $user, Enchantment $enchantment): bool
    {
        $cacheKey = $user->getId() . '-' . $enchantment->getId();

        if(!array_key_exists($cacheKey, $this->userAurasPerRequestCache))
        {
            $this->userAurasPerRequestCache[$cacheKey] = $this->userUnlockedAuraRepository->findOneBy([
                'user' => $user,
                'aura' => $enchantment
            ]);;
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
                        'id' => $e->getId(),
                        'unlockedOn' => $unlockedAura->getUnlockedOn(),
                        'comment' => $unlockedAura->getUnlockedOn(),
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
                }
                else
                    $activityLog->setEntry($activityLog->getEntry() . ' (A new style has been added to the Hattier!)');
            }

            $this->userAurasPerRequestCache[$cacheKey] = $unlockedAura;
        }

        return $this->userAurasPerRequestCache[$cacheKey];
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