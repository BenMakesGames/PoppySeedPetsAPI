<?php
namespace App\Service\PetActivity\Holiday;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ArrayFunctions;
use App\Functions\PetActivityLogFactory;
use App\Model\PetChanges;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\PetRepository;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\StatusEffectService;
use Doctrine\ORM\EntityManagerInterface;

class AwaOdoriService
{
    private IRandom $rng;
    private StatusEffectService $statusEffectService;
    private EntityManagerInterface $em;
    private PetExperienceService $petExperienceService;

    public function __construct(
        Squirrel3 $rng, StatusEffectService $statusEffectService,
        EntityManagerInterface $em, PetExperienceService $petExperienceService
    )
    {
        $this->rng = $rng;
        $this->statusEffectService = $statusEffectService;
        $this->em = $em;
        $this->petExperienceService = $petExperienceService;
    }

    public function adventure(Pet $pet): ?PetActivityLog
    {
        $qb = $this->em->getRepository(Pet::class)->createQueryBuilder('p');

        $qb
            ->leftJoin('p.houseTime', 'houseTime')
            ->andWhere('p.id != :petId')
            ->andWhere('p.lastInteracted >= :threeDaysAgo')
            ->andWhere('houseTime.socialEnergy >= :minimumSocialEnergy')
            ->andWhere('p.food > 0')
            ->setParameter('petId', $pet->getId())
            ->setParameter('threeDaysAgo', new \DateTimeImmutable('-3 days'))
            ->setParameter('minimumSocialEnergy', (PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT * 3) / 2)
        ;

        if($pet->hasStatusEffect(StatusEffectEnum::WEREFORM))
        {
            $qb
                ->join('p.statusEffects', 'se')
                ->andWhere('se.status = :wereform')
                ->setParameter('wereform', StatusEffectEnum::WEREFORM)
            ;
        }

        $count = $qb->select('COUNT(p.id)')->getQuery()->getSingleScalarResult();

        if($count == 0)
        {
            return null;
        }

        if($count > 1)
        {
            $numberOfDancingBuddies = $this->rng->rngNextInt(1, 4);

            $qb
                ->setFirstResult($this->rng->rngNextInt(0, max(1, $count - $numberOfDancingBuddies)))
                ->setMaxResults($numberOfDancingBuddies)
            ;
        }
        else
        {
            $qb->setMaxResults(1);
        }

        /** @var Pet[] $dancingBuddies */
        $dancingBuddies = $qb
            ->select('p')
            ->getQuery()
            ->execute();

        $awaOdoriTag = PetActivityLogTagRepository::findByNames($this->em, [ 'Awa Odori' ]);

        $petNames = [ '%pet:' . $pet->getId() . '.name%' ];

        foreach($dancingBuddies as $buddy)
            $petNames[] = '%pet:' . $buddy->getId() . '.name%';

        $listOfPetNames = ArrayFunctions::list_nice($petNames);

        $activityLog = $this->dance($pet, $listOfPetNames, $awaOdoriTag, false);

        foreach($dancingBuddies as $buddy)
        {
            $buddyActivityLog = $this->dance($buddy, $listOfPetNames, $awaOdoriTag, $buddy->getOwner()->getId() == $pet->getOwner()->getId());
        }

        return $activityLog;
    }

    private function dance(Pet $pet, $listOfPetNames, $activityLogTags, bool $markLogAsRead): PetActivityLog
    {
        $changes = new PetChanges($pet);

        $this->statusEffectService->applyStatusEffect($pet, StatusEffectEnum::DANCING_LIKE_A_FOOL, PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT);
        $this->petExperienceService->spendSocialEnergy($pet, PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT);

        $pet->increaseSafety(4)->increaseLove(4)->increaseEsteem(4);

        $log = $markLogAsRead
            ? PetActivityLogFactory::createReadLog($this->em, $pet, $listOfPetNames . ' went out dancing together!')
            : PetActivityLogFactory::createUnreadLog($this->em, $pet, $listOfPetNames . ' went out dancing together!');

        $log
            ->setIcon('ui/holidays/awa-odori')
            ->setChanges($changes->compare($pet))
            ->addTags($activityLogTags)
            ->addInterestingness(PetActivityLogInterestingnessEnum::HOLIDAY_OR_SPECIAL_EVENT)
        ;

        return $log;
    }
}