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


namespace App\Controller\Inventory;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Repository\InventoryRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/inventory")]
class MoveController extends AbstractController
{
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/moveTo/{location}", methods: ["POST"], requirements: ["location" => "\d+"])]
    public function moveInventory(
        int $location, Request $request, ResponseService $responseService, InventoryRepository $inventoryRepository,
        EntityManagerInterface $em
    ): JsonResponse
    {
        if(!LocationEnum::isAValue($location))
            throw new PSPFormValidationException('Invalid location given.');

        /** @var User $user */
        $user = $this->getUser();

        $allowedLocations = [ LocationEnum::HOME ];

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Fireplace))
            $allowedLocations[] = LocationEnum::MANTLE;

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Basement))
            $allowedLocations[] = LocationEnum::BASEMENT;

        if(!in_array($location, $allowedLocations))
            throw new PSPFormValidationException('Invalid location given.');

        $inventoryIds = $request->request->all('inventory');

        if(count($inventoryIds) >= 200)
            throw new PSPFormValidationException('Oh, goodness, please don\'t try to move more than 200 items at a time. Sorry.');

        /** @var Inventory[] $inventory */
        $inventory = $inventoryRepository->createQueryBuilder('i')
            ->andWhere('i.owner=:user')
            ->andWhere('i.id IN (:inventoryIds)')
            ->andWhere('i.location IN (:allowedLocations)')
            ->setParameter('user', $user->getId())
            ->setParameter('inventoryIds', $inventoryIds)
            ->setParameter('allowedLocations', $allowedLocations)
            ->getQuery()
            ->execute()
        ;

        if(count($inventory) !== count($inventoryIds))
            throw new PSPNotFoundException('Some of the items could not be found??');

        $itemsInTargetLocation = InventoryRepository::countItemsInLocation($em, $user, $location);

        if($location === LocationEnum::HOME)
        {
            if ($itemsInTargetLocation + count($inventory) > User::MAX_HOUSE_INVENTORY)
                throw new PSPInvalidOperationException('You do not have enough space in your house!');
        }

        if($location === LocationEnum::BASEMENT)
        {
            if ($itemsInTargetLocation + count($inventory) > User::MAX_BASEMENT_INVENTORY)
                throw new PSPInvalidOperationException('You do not have enough space in the basement!');
        }

        if($location === LocationEnum::MANTLE)
        {
            if ($itemsInTargetLocation + count($inventory) > $user->getFireplace()->getMantleSize())
                throw new PSPInvalidOperationException('The mantle only has space for ' . $user->getFireplace()->getMantleSize() . ' items.');
        }

        foreach($inventory as $i)
        {
            $i
                ->setLocation($location)
                ->setModifiedOn()
            ;
        }

        $em->flush();

        return $responseService->success();
    }
}
