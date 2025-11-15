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
use App\Exceptions\PSPNotUnlockedException;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/inventory")]
class MoveController
{
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/moveTo/{location}", methods: ["POST"], requirements: ["location" => "\d+"])]
    public function moveInventory(
        int $location, Request $request, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService, UserAccessor $userAccessor
    ): JsonResponse
    {
        if(!LocationEnum::isAValue($location))
            throw new PSPFormValidationException('Invalid location given.');

        $user = $userAccessor->getUserOrThrow();

        $allowedLocations = [ LocationEnum::Home ];

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Fireplace))
            $allowedLocations[] = LocationEnum::Mantle;

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Basement))
            $allowedLocations[] = LocationEnum::Basement;

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Library))
            $allowedLocations[] = LocationEnum::Library;

        if(!in_array($location, $allowedLocations))
            throw new PSPFormValidationException('Invalid location given.');

        $inventoryIds = $request->request->all('inventory');

        if(count($inventoryIds) >= 200)
            throw new PSPFormValidationException('Oh, goodness, please don\'t try to move more than 200 items at a time. Sorry.');

        /** @var Inventory[] $inventory */
        $inventory = $em->createQueryBuilder()
            ->select('i')->from(Inventory::class, 'i')
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

        $itemsInTargetLocation = $inventoryService->countItemsInLocation($user, $location);

        if($location === LocationEnum::Home)
        {
            if ($itemsInTargetLocation + count($inventory) > User::MaxHouseInventory)
                throw new PSPInvalidOperationException('You do not have enough space in your house!');
        }
        else if($location === LocationEnum::Basement)
        {
            if ($itemsInTargetLocation + count($inventory) > User::MaxBasementInventory)
                throw new PSPInvalidOperationException('You do not have enough space in the basement!');
        }
        else if($location === LocationEnum::Mantle)
        {
            $fireplace = $user->getFireplace()
                ?? throw new PSPNotUnlockedException('Fireplace');

            if ($itemsInTargetLocation + count($inventory) > $fireplace->getMantleSize())
                throw new PSPInvalidOperationException('The mantle only has space for ' . $fireplace->getMantleSize() . ' items.');
        }
        else if($location === LocationEnum::Library)
        {
            $uniqueItemIds = [];

            foreach($inventory as $i)
            {
                if(!$i->getItem()->hasItemGroup('Book') && !$i->getItem()->hasItemGroup('Note'))
                    throw new PSPInvalidOperationException('Only books and notes can be placed in the Library! (' . $i->getItem()->getName() . ' isn\'t a book or note, at least.)');

                if(in_array($i->getItem()->getId(), $uniqueItemIds))
                    throw new PSPInvalidOperationException('Every book in the Library must be unique. (You selected multiple copies of ' . $i->getItem()->getName() . ' to move just now, at least.)');

                $uniqueItemIds[] = $i->getItem()->getId();
            }

            $alreadyInLibrary = $em->createQueryBuilder()
                ->select('item.name AS itemName')->from(Inventory::class, 'i')
                ->join('i.item', 'item')
                ->andWhere('i.owner=:user')
                ->andWhere('i.location=:library')
                ->andWhere('item.id IN (:books)')
                ->setParameter('user', $user->getId())
                ->setParameter('library', LocationEnum::Library)
                ->setParameter('books', $uniqueItemIds)
                ->setMaxResults(1)
                ->getQuery()
                ->getScalarResult();

            if(count($alreadyInLibrary) > 0)
                throw new PSPInvalidOperationException('Every book in the Library must be unique. (You already have a copy of ' . $alreadyInLibrary[0]['itemName'] . ' in your Library, at least.)');
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
