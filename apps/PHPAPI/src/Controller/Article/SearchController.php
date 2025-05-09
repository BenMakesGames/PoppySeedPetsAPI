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
use App\Enum\SerializationGroupEnum;
use App\Service\Filter\ArticleFilterService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\UserAccessor;

#[Route("/article")]
class SearchController
{
    #[Route("", methods: ["GET"])]
    #[DoesNotRequireHouseHours]
    public function handle(
        Request $request, ResponseService $responseService, ArticleFilterService $articleFilterService,
        EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUser();

        if($user && $userAccessor->getUserOrThrow()->getUnreadNews() > 0)
        {
            $user->setUnreadNews(0);
            $em->flush();
        }

        return $responseService->success(
            $articleFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::ARTICLE ]
        );
    }
}
