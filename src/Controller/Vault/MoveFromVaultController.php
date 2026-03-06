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

namespace App\Controller\Vault;

use App\Entity\Inventory;
use App\Entity\User;
use App\Entity\VaultInventory;
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

#[Route("/vault")]
class MoveFromVaultController
{
    #[Route("/moveOut", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function moveFromVault(
        Request $request, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor, InventoryService $inventoryService
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::InfinityVault))
            throw new PSPNotUnlockedException('Infinity Vault');

        $vault = $user->getVault();
        if($vault === null || !$vault->isOpen())
            throw new PSPInvalidOperationException('The vault is closed.');

        $vaultItemId = $request->request->getString('vaultItemId');
        $quantity = $request->request->getInt('quantity', 1);
        $targetLocation = $request->request->getInt('location', LocationEnum::Home);

        // Validate vault record
        $vaultItem = $em->getRepository(VaultInventory::class)->find($vaultItemId);

        if(!$vaultItem || $vaultItem->getUser()->getId() !== $user->getId())
            throw new PSPNotFoundException('Vault item not found.');

        if($quantity < 1 || $quantity > $vaultItem->getQuantity())
            throw new PSPFormValidationException('Invalid quantity.');

        // Validate target location
        if(!LocationEnum::isAValue($targetLocation))
            throw new PSPFormValidationException('Invalid location.');

        $allowedLocations = [ LocationEnum::Home ];

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Basement))
            $allowedLocations[] = LocationEnum::Basement;

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Fireplace))
            $allowedLocations[] = LocationEnum::Mantle;

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Library))
            $allowedLocations[] = LocationEnum::Library;

        if(!in_array($targetLocation, $allowedLocations))
            throw new PSPFormValidationException('Invalid target location.');

        // Validate target location capacity
        $itemsInTarget = $inventoryService->countItemsInLocation($user, $targetLocation);

        if($targetLocation === LocationEnum::Home)
        {
            if($itemsInTarget + $quantity > User::MaxHouseInventory)
                throw new PSPInvalidOperationException('Not enough space at home.');
        }
        else if($targetLocation === LocationEnum::Basement)
        {
            if($itemsInTarget + $quantity > User::MaxBasementInventory)
                throw new PSPInvalidOperationException('Not enough space in the basement.');
        }
        else if($targetLocation === LocationEnum::Mantle)
        {
            $fireplace = $user->getFireplace()
                ?? throw new PSPNotUnlockedException('Fireplace');

            if($itemsInTarget + $quantity > $fireplace->getMantleSize())
                throw new PSPInvalidOperationException('Not enough space on the mantle.');
        }
        else
        {
            // Library constraints: must be Book, Note, or Pamphlet; must be unique
            $item = $vaultItem->getItem();

            if(!$item->hasItemGroup('Book') && !$item->hasItemGroup('Note') && !$item->hasItemGroup('Pamphlet'))
                throw new PSPInvalidOperationException('Only Books, Notes, and Pamphlets can be placed in the Library.');

            if($quantity > 1)
                throw new PSPInvalidOperationException('Library requires unique copies; you can only move 1 at a time.');

            $alreadyInLibrary = $em->createQueryBuilder()
                ->select('COUNT(i.id)')->from(Inventory::class, 'i')
                ->andWhere('i.owner = :user')
                ->andWhere('i.item = :item')
                ->andWhere('i.location = :library')
                ->setParameter('user', $user)
                ->setParameter('item', $item)
                ->setParameter('library', LocationEnum::Library)
                ->getQuery()
                ->getSingleScalarResult();

            if((int)$alreadyInLibrary > 0)
                throw new PSPInvalidOperationException('You already have a copy of ' . $item->getName() . ' in your Library.');
        }

        // Create inventory records WITHOUT using receiveItem()
        // (bypasses lucky transformation and seasonal spice application)
        for($i = 0; $i < $quantity; $i++)
        {
            $inv = (new Inventory(owner: $user, item: $vaultItem->getItem()))
                ->setCreatedBy($vaultItem->getMaker())
                ->addComment('Retrieved from the Infinity Vault.')
                ->setLocation($targetLocation)
            ;

            $em->persist($inv);
        }

        // Decrement vault quantity
        $vaultItem->setQuantity($vaultItem->getQuantity() - $quantity);

        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->success();
    }
}
