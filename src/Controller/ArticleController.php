<?php
namespace App\Controller;

use App\Annotations\DoesNotRequireHouseHours;
use App\Entity\Article;
use App\Enum\SerializationGroupEnum;
use App\Repository\ArticleRepository;
use App\Service\Filter\ArticleFilterService;
use App\Service\ResponseService;
use App\Service\TwitterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
* @Route("/article")
*/
class ArticleController extends PoppySeedPetsController
{
    /**
     * @DoesNotRequireHouseHours()
     * @Route("/latest", methods={"GET"})
     */
    public function getLatest(
        ResponseService $responseService, ArticleRepository $articleRepository, EntityManagerInterface $em
    )
    {
        if($this->getUser() && $this->getUser()->getUnreadNews() === 1)
        {
            $this->getUser()->setUnreadNews(0);
            $em->flush();
        }

        return $responseService->success(
            $articleRepository->findOneBy([], [ 'createdOn' => 'DESC' ]),
            [ SerializationGroupEnum::ARTICLE ]
        );
    }

    /**
     * @DoesNotRequireHouseHours()
     * @Route("", methods={"GET"})
     */
    public function getAll(
        Request $request, ResponseService $responseService, ArticleFilterService $articleFilterService,
        EntityManagerInterface $em
    )
    {
        if($this->getUser() && $this->getUser()->getUnreadNews() > 0)
        {
            $this->getUser()->setUnreadNews(0);
            $em->flush();
        }

        return $responseService->success(
            $articleFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::ARTICLE ]
        );
    }

    /**
     * @DoesNotRequireHouseHours()
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

        if(\mb_strlen($title) > 255)
            throw new UnprocessableEntityHttpException('title may not be longer than 255 characters.');

        $article = (new Article())
            ->setTitle($title)
            ->setBody($body)
            ->setAuthor($this->getUser())
        ;

        $em->persist($article);
        $em->flush();

        $em->createQuery('UPDATE App\\Entity\\User u SET u.unreadNews=u.unreadNews+1')->execute();

        return $responseService->success();
    }

    /**
     * @DoesNotRequireHouseHours()
     * @Route("/{article}", methods={"POST"}, requirements={"article"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function getArticle(
        Article $article, ResponseService $responseService, Request $request, EntityManagerInterface $em
    )
    {
        $this->adminIPsOnly($request);

        $title = trim($request->request->get('title', ''));
        $body = trim($request->request->get('body', ''));

        if($title === '' || $body === '')
            throw new UnprocessableEntityHttpException('title and body are both required.');

        $article
            ->setTitle($title)
            ->setBody($body)
        ;

        $em->flush();

        return $responseService->success();
    }

    /**
     * @DoesNotRequireHouseHours()
     * @Route("/{article}/tweet", methods={"POST"}, requirements={"article"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function tweetArticle(
        Article $article, ResponseService $responseService, TwitterService $twitterService, Request $request
    )
    {
        $this->adminIPsOnly($request);

        try
        {
            $twitterService->postArticle($article);
        }
        catch(\Exception $e)
        {
            return $responseService->error(500, [ $e->getMessage() ]);
        }

        return $responseService->success();
    }
}
