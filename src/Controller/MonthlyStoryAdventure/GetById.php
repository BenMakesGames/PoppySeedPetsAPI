<?php

namespace App\Controller\MonthlyStoryAdventure;

use App\Controller\PoppySeedPetsController;
use App\Entity\MonthlyStoryAdventure;
use App\Enum\SerializationGroupEnum;
use App\Repository\MonthlyStoryAdventureRepository;
use App\Repository\MonthlyStoryAdventureStepRepository;
use App\Repository\UserMonthlyStoryAdventureStepCompletedRepository;
use App\Service\ResponseService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/monthlyStoryAdventure")
 */
class GetById extends PoppySeedPetsController
{
    /**
     * @Route("/{story}", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function handle(
        MonthlyStoryAdventure $story,
        MonthlyStoryAdventureStepRepository $monthlyStoryAdventureStepRepository,
        UserMonthlyStoryAdventureStepCompletedRepository $userMonthlyStoryAdventureStepCompletedRepository,
        ResponseService $responseService
    )
    {
        $complete = $userMonthlyStoryAdventureStepCompletedRepository->findComplete($this->getUser(), $story);
        $available = $monthlyStoryAdventureStepRepository->findAvailable($this->getUser(), $story, $complete);

        return $responseService->success(
            [
                'story' => $story,
                'stepsAvailable' => $available,
                'stepsComplete' => $complete,
            ],
            [
                SerializationGroupEnum::STAR_KINDRED_STORY_DETAILS,
                SerializationGroupEnum::STAR_KINDRED_STORY_STEP_AVAILABLE,
                SerializationGroupEnum::STAR_KINDRED_STORY_STEP_COMPLETE
            ]
        );
    }
}