<?php

namespace App\Controller\MonthlyStoryAdventure;

use App\Controller\PoppySeedPetsController;
use App\Entity\MonthlyStoryAdventure;
use App\Enum\SerializationGroupEnum;
use App\Repository\MonthlyStoryAdventureRepository;
use App\Service\ResponseService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/monthlyStoryAdventure")
 */
class Search extends PoppySeedPetsController
{
    /**
     * @Route("/", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function handle(
        MonthlyStoryAdventureRepository $monthlyStoryAdventureRepository,
        ResponseService $responseService
    )
    {
        $stories = $monthlyStoryAdventureRepository->findAll();

        $results = array_map(fn(MonthlyStoryAdventure $story) => [
            'id' => $story->getId(),
            'title' => $story->getTitle(),
            'releaseYear' => $story->getReleaseYear(),
            'releaseMonth' => $story->getReleaseMonth()
        ], $stories);

        return $responseService->success($results);
    }
}