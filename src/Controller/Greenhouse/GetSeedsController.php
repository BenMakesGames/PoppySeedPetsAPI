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


namespace App\Controller\Greenhouse;

use App\Entity\Inventory;
use App\Enum\PlantTypeEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/greenhouse")]
class GetSeedsController
{
    #[Route("/seeds/{type}", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getSeeds(
        ResponseService $responseService, EntityManagerInterface $em, UserAccessor $userAccessor,
        string $type = PlantTypeEnum::Earth,
    ): JsonResponse
    {
        if(!PlantTypeEnum::isAValue($type))
            throw new PSPFormValidationException('Must provide a valid seed type ("earth", "water", etc...)');

        $user = $userAccessor->getUserOrThrow();

        $seeds = $em->createQueryBuilder()
            ->select('i')->from(Inventory::class, 'i')
            ->andWhere('i.owner=:owner')
            ->andWhere('i.location IN (:consumableLocations)')
            ->leftJoin('i.item', 'item')
            ->leftJoin('item.plant', 'plant')
            ->andWhere('item.plant IS NOT NULL')
            ->andWhere('plant.type=:plantType')
            ->addOrderBy('item.name', 'ASC')
            ->setParameter('owner', $user->getId())
            ->setParameter('consumableLocations', Inventory::ConsumableLocations)
            ->setParameter('plantType', $type)
            ->getQuery()
            ->getResult()
        ;

        return $responseService->success($seeds, [ SerializationGroupEnum::MY_SEEDS ]);
    }
}
