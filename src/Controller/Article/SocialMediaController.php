<?php
declare(strict_types=1);

namespace App\Controller\Article;

use App\Attributes\DoesNotRequireHouseHours;
use App\Controller\AdminController;
use App\Entity\Article;
use App\Service\RedditService;
use App\Service\ResponseService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/article")]
class SocialMediaController extends AdminController
{
    #[DoesNotRequireHouseHours]
    #[IsGranted("ROLE_ADMIN")]
    #[Route("/{article}/reddit", methods: ["POST"], requirements: ["article" => "\d+"])]
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
