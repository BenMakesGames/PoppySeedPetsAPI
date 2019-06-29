<?php
namespace App\Controller;

use App\Enum\SerializationGroup;
use App\Repository\ArticleRepository;
use App\Service\ResponseService;
use Symfony\Component\Routing\Annotation\Route;

/**
* @Route("/farticle")
*/
class ArticleController extends PsyPetsController
{
    /**
     * @Route("", methods={"GET"})
     */
    public function getArticles(ResponseService $responseService, ArticleRepository $articleRepository)
    {
        return $responseService->success(
            $articleRepository->findBy([], [ 'createdOn' => 'DESC' ], 10),
            [ SerializationGroup::ARTICLE ]
        );
    }
}