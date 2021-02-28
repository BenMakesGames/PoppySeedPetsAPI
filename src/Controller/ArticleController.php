<?php
namespace App\Controller;

use App\Annotations\DoesNotRequireHouseHours;
use App\Entity\Article;
use App\Entity\DesignGoal;
use App\Enum\SerializationGroupEnum;
use App\Functions\ArrayFunctions;
use App\Repository\ArticleRepository;
use App\Repository\DesignGoalRepository;
use App\Service\Filter\ArticleFilterService;
use App\Service\RedditService;
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
    public function createNew(
        Request $request, ResponseService $responseService, EntityManagerInterface $em,
        DesignGoalRepository $designGoalRepository
    )
    {
        $this->adminIPsOnly($request);

        $title = trim($request->request->get('title', ''));
        $body = trim($request->request->get('body', ''));

        if($title === '' || $body === '')
            throw new UnprocessableEntityHttpException('title and body are both required.');

        if(\mb_strlen($title) > 255)
            throw new UnprocessableEntityHttpException('title may not be longer than 255 characters.');

        $designGoals = $designGoalRepository->findByIdsFromParameters($request->request, 'designGoals');

        $article = (new Article())
            ->setTitle($title)
            ->setBody($body)
            ->setAuthor($this->getUser())
        ;

        foreach($designGoals as $designGoal)
            $article->addDesignGoal($designGoal);

        $em->persist($article);
        $em->flush();

        $em->createQuery('UPDATE App\Entity\User u SET u.unreadNews=u.unreadNews+1')->execute();

        return $responseService->success();
    }

    /**
     * @DoesNotRequireHouseHours()
     * @Route("/{article}", methods={"POST"}, requirements={"article"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function getArticle(
        Article $article, ResponseService $responseService, Request $request, EntityManagerInterface $em,
        DesignGoalRepository $designGoalRepository
    )
    {
        $this->adminIPsOnly($request);

        $title = trim($request->request->get('title', ''));
        $body = trim($request->request->get('body', ''));

        if($title === '' || $body === '')
            throw new UnprocessableEntityHttpException('title and body are both required.');

        $designGoals = $designGoalRepository->findByIdsFromParameters($request->request, 'designGoals');

        $article
            ->setTitle($title)
            ->setBody($body)
        ;

        $currentDesignGoals = $article->getDesignGoals()->toArray();
        $designGoalsToAdd = ArrayFunctions::except($designGoals, $currentDesignGoals, function(DesignGoal $dg) { return $dg->getId(); });
        $designGoalsToRemove = ArrayFunctions::except($currentDesignGoals, $designGoals, function(DesignGoal $dg) { return $dg->getId(); });

        /*
        echo 'current design goals:' . "\r\n";
        foreach($currentDesignGoals as $dg) echo $dg->getId() . ', ' . $dg->getName() . "\r\n";

        echo "\r\n" . 'design goals from request body:' . "\r\n";
        foreach($designGoals as $dg) echo $dg->getId() . ', ' . $dg->getName() . "\r\n";

        echo "\r\ndesign goals to add:\r\n";
        foreach($designGoalsToAdd as $dg) echo $dg->getId() . ', ' . $dg->getName() . "\r\n";

        echo "\r\ndesign goals to remove:\r\n";
        foreach($designGoalsToRemove as $dg) echo $dg->getId() . ', ' . $dg->getName() . "\r\n";

        die;
        */

        foreach($designGoalsToRemove as $toRemove)
            $article->removeDesignGoal($toRemove);

        foreach($designGoalsToAdd as $toAdd)
            $article->addDesignGoal($toAdd);

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

    /**
     * @DoesNotRequireHouseHours()
     * @Route("/{article}/reddit", methods={"POST"}, requirements={"article"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function redditArticle(
        Article $article, ResponseService $responseService, RedditService $redditService, Request $request
    )
    {
        $this->adminIPsOnly($request);

        try
        {
            $redditService->postArticle($article);
        }
        catch(\Exception $e)
        {
            return $responseService->error(500, [ $e->getMessage() ]);
        }

        return $responseService->success();
    }
}
