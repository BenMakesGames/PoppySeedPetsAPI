<?php
namespace App\Service;

use App\Entity\Pet;
use App\Enum\UserStatEnum;
use App\Model\PetChanges;
use App\Repository\UserStatsRepository;

class PetAndPraiseService
{
    private PetExperienceService $petExperienceService;
    private CravingService $cravingService;
    private ResponseService $responseService;
    private UserStatsRepository $userStatsRepository;

    public function __construct(
        PetExperienceService $petExperienceService, CravingService $cravingService, ResponseService $responseService,
        UserStatsRepository $userStatsRepository
    )
    {
        $this->petExperienceService = $petExperienceService;
        $this->cravingService = $cravingService;
        $this->responseService = $responseService;
        $this->userStatsRepository = $userStatsRepository;
    }

    public function doPet(Pet $pet)
    {
        if(!$pet->isAtHome()) throw new \InvalidArgumentException('Pets that aren\'t home cannot be interacted with.');

        $now = new \DateTimeImmutable();

        $changes = new PetChanges($pet);

        if($pet->getLastInteracted() < $now->modify('-48 hours'))
        {
            $pet->setLastInteracted($now->modify('-20 hours'));
            $pet->increaseSafety(15);
            $pet->increaseLove(15);
            $this->petExperienceService->gainAffection($pet, 10);
        }
        else if($pet->getLastInteracted() < $now->modify('-20 hours'))
        {
            $pet->setLastInteracted($now->modify('-4 hours'));
            $pet->increaseSafety(10);
            $pet->increaseLove(10);
            $this->petExperienceService->gainAffection($pet, 5);
        }
        else if($pet->getLastInteracted() < $now->modify('-4 hours'))
        {
            $pet->setLastInteracted($now);
            $pet->increaseSafety(7);
            $pet->increaseLove(7);
            $this->petExperienceService->gainAffection($pet, 1);
        }
        else
            throw new \InvalidArgumentException('You\'ve already interacted with this pet recently.');

        $this->cravingService->maybeAddCraving($pet);

        $this->responseService->createActivityLog($pet, '%user:' . $pet->getOwner()->getId() . '.Name% pet ' . '%pet:' . $pet->getId() . '.name%'. '.', 'ui/affection', $changes->compare($pet));
        $this->userStatsRepository->incrementStat($pet->getOwner(), UserStatEnum::PETTED_A_PET);
    }

    public function doPraise(Pet $pet)
    {
        if(!$pet->isAtHome()) throw new \InvalidArgumentException('Pets that aren\'t home cannot be interacted with.');

        $now = new \DateTimeImmutable();

        $changes = new PetChanges($pet);

        if($pet->getLastInteracted() < $now->modify('-48 hours'))
        {
            $pet->setLastInteracted($now->modify('-20 hours'));
            $pet->increaseLove(15);
            $pet->increaseEsteem(15);
            $this->petExperienceService->gainAffection($pet, 10);
        }
        else if($pet->getLastInteracted() < $now->modify('-20 hours'))
        {
            $pet->setLastInteracted($now->modify('-4 hours'));
            $pet->increaseLove(10);
            $pet->increaseEsteem(10);
            $this->petExperienceService->gainAffection($pet, 5);
        }
        else if($pet->getLastInteracted() < $now->modify('-4 hours'))
        {
            $pet->setLastInteracted($now);
            $pet->increaseLove(7);
            $pet->increaseEsteem(7);
            $this->petExperienceService->gainAffection($pet, 1);
        }
        else
            throw new \InvalidArgumentException('You\'ve already interacted with this pet recently.');

        $this->cravingService->maybeAddCraving($pet);

        $this->responseService->createActivityLog($pet, '%user:' . $pet->getOwner()->getId() . '.Name% praised ' . '%pet:' . $pet->getId() . '.name%'. '.', 'ui/affection', $changes->compare($pet));
        $this->userStatsRepository->incrementStat($pet->getOwner(), UserStatEnum::PRAISED_A_PET);
    }

}