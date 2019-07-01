<?php
namespace App\Controller;

use App\Entity\Article;
use App\Enum\SerializationGroup;
use App\Repository\ArticleRepository;
use App\Service\Filter\ArticleFilterService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

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
     * @Route("/{article}", methods={"GET"}, requirements={"article"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function getArticle(Article $article, ResponseService $responseService, Request $request)
    {
        $this->adminIPsOnly($request);

        return $responseService->success($article, [ SerializationGroup::ARTICLE_ADMIN ]);
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

    /**
     * @Route("", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function createNew(Request $request, ResponseService $responseService, EntityManagerInterface $em)
    {
        $this->adminIPsOnly($request);

        $title = trim($request->request->get('title', ''));
        $body = trim($request->request->get('body', ''));

        if($title === '' || $body === '')
            throw new UnprocessableEntityHttpException('title and body are both required.');

        $article = (new Article())
            ->setTitle($title)
            ->setBody($body)
            ->setAuthor($this->getUser())
        ;

        $em->persist($article);
        $em->flush();

        return $responseService->success();
    }
}