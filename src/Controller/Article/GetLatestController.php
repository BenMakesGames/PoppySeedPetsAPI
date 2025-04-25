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
use App\Entity\Article;
use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/article")]
class GetLatestController extends AbstractController
{
    #[DoesNotRequireHouseHours]
    #[Route("/latest", methods: ["GET"])]
    public function getLatest(
        ResponseService $responseService, EntityManagerInterface $em
    ): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if($user && $user->getUnreadNews() === 1)
        {
            $user->setUnreadNews(0);
            $em->flush();
        }

        $latestArticle = $em->getRepository(Article::class)->findOneBy([], [ 'createdOn' => 'DESC' ]);

        return $responseService->success(
            $latestArticle,
            [ SerializationGroupEnum::ARTICLE ]
        );
    }
}
