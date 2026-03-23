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

namespace App\Controller\HollowEarth;

use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/hollowEarth")]
class MyTilesController
{
    #[Route("/myTiles", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getMyTiles(
        EntityManagerInterface $em, ResponseService $responseService, Request $request,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();
        $player = $user->getHollowEarthPlayer();

        if($player === null)
            throw new PSPNotUnlockedException('Hollow Earth');

        $types = $request->query->all('types');

        if(count($types) === 0)
            throw new PSPFormValidationException('The types of tiles to look for were not specified.');

        $tiles = $em->createQueryBuilder()
            ->select('i')->from(Inventory::class, 'i')
            ->leftJoin('i.item', 'item')
            ->leftJoin('item.hollowEarthTileCard', 'tileCard')
            ->leftJoin('tileCard.type', 'type')
            ->andWhere('i.owner=:user')
            ->andWhere('i.location=:home')
            ->andWhere('item.hollowEarthTileCard IS NOT NULL')
            ->andWhere('type.name IN (:allowedTypes)')
            ->setParameter('user', $user->getId())
            ->setParameter('home', LocationEnum::Home)
            ->setParameter('allowedTypes', $types)
            ->getQuery()
            ->execute()
        ;

        return $responseService->success($tiles, [ SerializationGroupEnum::MY_HOLLOW_EARTH_TILES ]);
    }
}
