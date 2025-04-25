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


namespace App\Controller\Dragon;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPNotFoundException;
use App\Functions\DragonHelpers;
use App\Repository\InventoryRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\UserAccessor;

#[Route("/dragon")]
class GetPotentialOfferingsController
{
    #[Route("/offerings", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getOfferings(
        ResponseService $responseService, InventoryRepository $inventoryRepository,
        EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        $dragon = DragonHelpers::getAdultDragon($em, $user);

        if(!$dragon)
            throw new PSPNotFoundException('You don\'t have an adult dragon!');

        $treasures = $inventoryRepository->createQueryBuilder('i')
            ->leftJoin('i.item', 'item')
            ->leftJoin('item.treasure', 'treasure')
            ->andWhere('i.owner=:user')
            ->andWhere('i.location=:home')
            ->andWhere('item.treasure IS NOT NULL')
            ->setParameter('user', $user->getId())
            ->setParameter('home', LocationEnum::HOME)
            ->getQuery()
            ->execute()
        ;

        return $responseService->success($treasures, [ SerializationGroupEnum::DRAGON_TREASURE ]);
    }
}
