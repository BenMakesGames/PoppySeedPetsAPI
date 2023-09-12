<?php
namespace App\Service\PetActivity\Holiday;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ArrayFunctions;
use App\Model\PetChanges;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\PetRepository;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\StatusEffectService;

class AwaOdoriService
{
    private PetRepository $petRepository;
    private IRandom $rng;
    private StatusEffectService $statusEffectService;
    private ResponseService $responseService;
    private PetExperienceService $petExperienceService;
    private PetActivityLogTagRepository $petActivityLogTagRepository;

    public function __construct(
        PetRepository $petRepository, Squirrel3 $rng, StatusEffectService $statusEffectService,
        ResponseService $responseService, PetExperienceService $petExperienceService,
        PetActivityLogTagRepository $petActivityLogTagRepository
    )
    {
        $this->petRepository = $petRepository;
        $this->rng = $rng;
        $this->statusEffectService = $statusEffectService;
        $this->responseService = $responseService;
        $this->petExperienceService = $petExperienceService;
        $this->petActivityLogTagRepository = $petActivityLogTagRepository;
    }

    public function adventure(Pet $pet): ?PetActivityLog
    {
        $qb = $this->petRepository->createQueryBuilder('p');

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

        $awaOdoriTag = $this->petActivityLogTagRepository->deprecatedFindByNames([ 'Awa Odori' ]);

        $petNames = [ '%pet:' . $pet->getId() . '.name%' ];

        foreach($dancingBuddies as $buddy)
            $petNames[] = '%pet:' . $buddy->getId() . '.name%';

        $listOfPetNames = ArrayFunctions::list_nice($petNames);

        $activityLog = $this->dance($pet, $listOfPetNames, $awaOdoriTag);

        foreach($dancingBuddies as $buddy)
        {
            $buddyActivityLog = $this->dance($buddy, $listOfPetNames, $awaOdoriTag);

            if($buddy->getOwner()->getId() == $pet->getOwner()->getId())
                $buddyActivityLog->setViewed();
        }

        return $activityLog;
    }

    private function dance(Pet $pet, $listOfPetNames, $activityLogTags): PetActivityLog
    {
        $changes = new PetChanges($pet);

        $this->statusEffectService->applyStatusEffect($pet, StatusEffectEnum::DANCING_LIKE_A_FOOL, PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT);
        $this->petExperienceService->spendSocialEnergy($pet, PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT);

        $pet->increaseSafety(4)->increaseLove(4)->increaseEsteem(4);

        return $this->responseService->createActivityLog($pet, $listOfPetNames . ' went out dancing together!', 'ui/holidays/awa-odori', $changes->compare($pet))
            ->addTags($activityLogTags)
            ->addInterestingness(PetActivityLogInterestingnessEnum::HOLIDAY_OR_SPECIAL_EVENT)
        ;
    }
}