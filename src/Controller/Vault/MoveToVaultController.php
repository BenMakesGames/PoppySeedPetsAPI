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
use App\Entity\VaultInventory;
use App\Enum\LocationEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/vault")]
class MoveToVaultController
{
    #[Route("/moveIn", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function moveToVault(
        Request $request, ResponseService $responseService, EntityManagerInterface $em, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::InfinityVault))
            throw new PSPNotUnlockedException('Infinity Vault');

        $vault = $user->getVault();
        if($vault === null || !$vault->isOpen())
            throw new PSPInvalidOperationException('The vault is closed.');

        $inventoryIds = $request->request->all('inventory');

        if(count($inventoryIds) === 0)
            throw new PSPFormValidationException('No items selected.');

        if(count($inventoryIds) > 200)
            throw new PSPFormValidationException('Please don\'t try to move more than 200 items at a time.');

        // Build allowed source locations (same as MoveController pattern)
        $allowedLocations = [ LocationEnum::Home ];

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Basement))
            $allowedLocations[] = LocationEnum::Basement;

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Fireplace))
            $allowedLocations[] = LocationEnum::Mantle;

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Library))
            $allowedLocations[] = LocationEnum::Library;

        /** @var Inventory[] $inventory */
        $inventory = $em->createQueryBuilder()
            ->select('i')->from(Inventory::class, 'i')
            ->join('i.item', 'item')
            ->andWhere('i.owner = :user')
            ->andWhere('i.id IN (:ids)')
            ->andWhere('i.location IN (:locations)')
            ->setParameter('user', $user->getId())
            ->setParameter('ids', $inventoryIds)
            ->setParameter('locations', $allowedLocations)
            ->getQuery()
            ->execute()
        ;

        if(count($inventory) !== count($inventoryIds))
            throw new PSPNotFoundException('Some items could not be found.');

        // Group by (item_id, creator_id) for upsert
        $groups = [];
        foreach($inventory as $inv)
        {
            $key = $inv->getItem()->getId() . ':' . ($inv->getCreatedBy()?->getId() ?? 'null');
            if(!isset($groups[$key]))
            {
                $groups[$key] = [
                    'item' => $inv->getItem(),
                    'maker' => $inv->getCreatedBy(),
                    'count' => 0,
                ];
            }
            $groups[$key]['count']++;
        }

        // Upsert vault records
        foreach($groups as $group)
        {
            $existing = $em->getRepository(VaultInventory::class)->findOneBy([
                'user' => $user,
                'item' => $group['item'],
                'maker' => $group['maker'],
            ]);

            if($existing)
            {
                $existing->increaseQuantity($group['count']);
            }
            else
            {
                $vaultItem = new VaultInventory($user, $group['item'], $group['maker'], $group['count']);
                $em->persist($vaultItem);
            }
        }

        // Delete inventory records
        foreach($inventory as $inv)
        {
            $em->remove($inv);
        }

        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->success();
    }
}
