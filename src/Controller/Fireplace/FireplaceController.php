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


namespace App\Controller\Fireplace;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\DragonRepository;
use App\Repository\InventoryRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use App\Service\UserAccessor;

#[Route("/fireplace")]
class FireplaceController
{
    #[Route("", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getFireplace(
        ResponseService $responseService, EntityManagerInterface $em,
        NormalizerInterface $normalizer,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Fireplace) || !$user->getFireplace())
            throw new PSPNotUnlockedException('Fireplace');

        $mantle = $em->getRepository(Inventory::class)->findBy([
            'owner' => $user,
            'location' => LocationEnum::MANTLE
        ]);

        $dragon = DragonRepository::findWhelp($em, $user);

        return $responseService->success(
            [
                'mantle' => $normalizer->normalize($mantle, null, [ 'groups' => [ SerializationGroupEnum::MY_INVENTORY ] ]),
                'fireplace' => $normalizer->normalize($user->getFireplace(), null, [ 'groups' => [ SerializationGroupEnum::MY_FIREPLACE, SerializationGroupEnum::HELPER_PET ] ]),
                'whelp' => $normalizer->normalize($dragon, null, [ 'groups' => [ SerializationGroupEnum::MY_FIREPLACE ] ]),
            ]
        );
    }

    #[Route("/fuel", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getFireplaceFuel(
        InventoryRepository $inventoryRepository, ResponseService $responseService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Fireplace) || !$user->getFireplace())
            throw new PSPNotUnlockedException('Fireplace');

        $fuel = $inventoryRepository->findFuel($user);

        return $responseService->success($fuel, [ SerializationGroupEnum::FIREPLACE_FUEL ]);
    }

    #[Route("/whelpFood", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getWhelpFood(
        ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        $whelp = DragonRepository::findWhelp($em, $user);

        if(!$whelp)
            throw new PSPNotUnlockedException('Dragon Whelp');

        $food = $em->getRepository(Inventory::class)->createQueryBuilder('i')
            ->andWhere('i.owner=:user')->setParameter('user', $user->getId())
            ->andWhere('i.location=:home')->setParameter('home', LocationEnum::HOME)
            ->join('i.item', 'item')
            ->join('item.food', 'food')
            ->andWhere('(food.spicy > 0 OR food.meaty > 0 OR food.fishy > 0)')
            ->addOrderBy('item.name', 'ASC')
            ->getQuery()
            ->execute()
        ;

        return $responseService->success($food, [ SerializationGroupEnum::MY_INVENTORY ]);
    }

    #[Route("/mantle/{user}", methods: ["GET"], requirements: [ "user" => "\d+" ])]
    public function getMantle(
        User $user, InventoryRepository $inventoryRepository, ResponseService $responseService
    ): JsonResponse
    {
        $inventory = $inventoryRepository->findBy([
            'owner' => $user,
            'location' => LocationEnum::MANTLE
        ]);

        return $responseService->success($inventory, [ SerializationGroupEnum::FIREPLACE_MANTLE ]);
    }
}
