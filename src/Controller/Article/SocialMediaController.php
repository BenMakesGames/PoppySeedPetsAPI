<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Controller\Article;

use App\Attributes\DoesNotRequireHouseHours;
use App\Controller\AdminController;
use App\Entity\Article;
use App\Service\RedditService;
use App\Service\ResponseService;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    ): JsonResponse
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
