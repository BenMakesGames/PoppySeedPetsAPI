<?php

namespace App\Controller\MonthlyStoryAdventure;

use App\Controller\PoppySeedPetsController;
use App\Entity\MonthlyStoryAdventure;
use App\Enum\SerializationGroupEnum;
use App\Repository\MonthlyStoryAdventureRepository;
use App\Repository\MonthlyStoryAdventureStepRepository;
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
        ResponseService $responseService
    )
    {
        $complete = $monthlyStoryAdventureStepRepository->findComplete($this->getUser());
        $available = $monthlyStoryAdventureStepRepository->findAvailable($this->getUser());

        return $responseService->success(
            [
                'story' => $story,
                'stepsAvailable' => $complete,
                'stepsComplete' => $available,
            ],
            [
                SerializationGroupEnum::STAR_KINDRED_STORY_DETAILS,
                SerializationGroupEnum::STAR_KINDRED_STORY_STEP_AVAILABLE,
                SerializationGroupEnum::STAR_KINDRED_STORY_STEP_COMPLETE
            ]
        );
    }
}