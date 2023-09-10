<?php
namespace App\Controller\Article;

use App\Controller\AdminController;
use App\Entity\Article;
use App\Service\RedditService;
use App\Service\ResponseService;
use Symfony\Component\HttpFoundation\Request;
use App\Annotations\DoesNotRequireHouseHours;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
* @Route("/article")
*/
class SocialMediaController extends AdminController
{
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
