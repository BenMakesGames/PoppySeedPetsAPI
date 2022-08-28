<?php

namespace App\Controller\MonthlyStoryAdventure;

use App\Controller\PoppySeedPetsController;
use App\Entity\MonthlyStoryAdventure;
use App\Enum\SerializationGroupEnum;
use App\Repository\MonthlyStoryAdventureRepository;
use App\Service\Filter\MonthlyStoryAdventureFilterService;
use App\Service\ResponseService;
use Symfony\Component\HttpFoundation\Request;
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
        MonthlyStoryAdventureFilterService $filterService,
        Request $request,
        ResponseService $responseService
    )
    {
        return $responseService->success(
            $filterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::STAR_KINDRED_STORY ]
        );
    }
}