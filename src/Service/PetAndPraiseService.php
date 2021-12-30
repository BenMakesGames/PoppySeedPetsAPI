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

        if($pet->getLastInteracted() < $now->modify('-4 hours'))
        {
            $diff = $now->diff($pet->getLastInteracted());
            $hours = min(48, $diff->h + $diff->days * 24);

            $affection = (int)($hours / 4);
            $gain = ceil($hours / 2.5) + 3;

            $safetyBonus = 0;
            $esteemBonus = 0;

            if($pet->getSafety() > $pet->getEsteem())
            {
                $safetyBonus -= floor($gain / 4);
                $esteemBonus += floor($gain / 4);
            }
            else if($pet->getEsteem() > $pet->getSafety())
            {
                $safetyBonus += floor($gain / 4);
                $esteemBonus -= floor($gain / 4);
            }

            $pet->increaseSafety($gain + $safetyBonus);
            $pet->increaseLove($gain);
            $pet->increaseEsteem($gain + $esteemBonus);
            $this->petExperienceService->gainAffection($pet, $affection);
        }
        else
            throw new \InvalidArgumentException('You\'ve already interacted with this pet recently.');

        $pet->setLastInteracted($now);

        $this->cravingService->maybeAddCraving($pet);

        $this->responseService->createActivityLog($pet, '%user:' . $pet->getOwner()->getId() . '.Name% pet ' . '%pet:' . $pet->getId() . '.name%'. '.', 'ui/affection', $changes->compare($pet));
        $this->userStatsRepository->incrementStat($pet->getOwner(), UserStatEnum::PETTED_A_PET);
    }

}