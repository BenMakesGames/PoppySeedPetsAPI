<?php
namespace App\Controller;

use App\Enum\SerializationGroup;
use App\Repository\ArticleRepository;
use App\Service\Filter\ArticleFilterService;
use App\Service\ResponseService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
* @Route("/article")
*/
class ArticleController extends PsyPetsController
{
    /**
     * @Route("/latest", methods={"GET"})
     */
    public function getLatest(ResponseService $responseService, ArticleRepository $articleRepository)
    {
        return $responseService->success(
            $articleRepository->findOneBy([], [ 'createdOn' => 'DESC' ]),
            [ SerializationGroup::ARTICLE ]
        );
    }

    /**
     * @Route("", methods={"GET"})
     */
    public function getAll(Request $request, ResponseService $responseService, ArticleFilterService $articleFilterService)
    {
        return $responseService->success(
            $articleFilterService->getResults($request->query),
            [ SerializationGroup::FILTER_RESULTS, SerializationGroup::ARTICLE ]
        );
    }
}