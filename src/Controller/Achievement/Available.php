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


namespace App\Controller\Achievement;

use App\Entity\User;
use App\Entity\UserBadge;
use App\Enum\BadgeEnum;
use App\Enum\SerializationGroupEnum;
use App\Functions\InMemoryCache;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/achievement")]
final class Available
{
    #[Route("/available", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getAvailable(
        ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        $claimed = $em->getRepository(UserBadge::class)->createQueryBuilder('b')
            ->select('b.badge')
            ->andWhere('b.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleColumnResult()
        ;

        $unclaimed = array_diff(BadgeEnum::getValues(), $claimed);

        $info = [];
        $cache = new InMemoryCache();

        foreach($unclaimed as $badge)
            $info[] = BadgeHelpers::getBadgeProgress($badge, $user, $em, $cache);

        return $responseService->success($info, [ SerializationGroupEnum::TRADER_OFFER, SerializationGroupEnum::MARKET_ITEM ]);
    }
}