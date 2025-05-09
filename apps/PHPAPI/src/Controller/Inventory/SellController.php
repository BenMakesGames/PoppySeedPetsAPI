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
use App\Entity\Item;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\MarketService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/inventory")]
class SellController
{
    #[Route("/sell", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function setSellPrice(
        ResponseService $responseService, Request $request, EntityManagerInterface $em, MarketService $marketService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Market))
            throw new PSPNotUnlockedException('Market');

        $itemIds = $request->request->all('items');

        if(count($itemIds) === 0)
            throw new PSPFormValidationException('You must select at least one item!');

        $price = (int)($request->request->get('price') ?? 0);

        if($price > $user->getMaxSellPrice())
            throw new PSPInvalidOperationException('You cannot list items for more than ' . $user->getMaxSellPrice() . ' moneys. See the Market Manager to see if you can increase this limit!');

        /** @var Inventory[] $inventory */
        $inventory = $em->getRepository(Inventory::class)->createQueryBuilder('i')
            ->leftJoin('i.holder', 'holder')
            ->leftJoin('i.wearer', 'wearer')
            ->leftJoin('i.lunchboxItem', 'lunchboxItem')
            ->andWhere('i.owner=:user')
            ->andWhere('i.id IN (:itemIds)')
            ->andWhere('i.lockedToOwner = 0')
            ->andWhere('holder IS NULL')
            ->andWhere('wearer IS NULL')
            ->andWhere('lunchboxItem IS NULL')
            ->setParameter('user', $user->getId())
            ->setParameter('itemIds', $itemIds)
            ->getQuery()
            ->execute()
        ;

        if(count($inventory) !== count($itemIds))
            throw new PSPNotFoundException('One or more of the selected items do not exist! Maybe reload and try again??');

        /** @var Item[] $inventoryToUpdateMinimumPriceFor */
        $inventoryToUpdateMinimumPriceFor = [];

        // if you're UNlisting items... EASY: do that:
        if($price <= 0)
        {
            foreach($inventory as $i)
            {
                if($i->getForSale())
                {
                    $em->remove($i->getForSale());
                    $i->setForSale(null);

                    $inventoryToUpdateMinimumPriceFor[$i->getItem()->getId()] = $i->getItem();
                }
            }
        }
        else
        {
            $anySoldToBidder = false;

            foreach($inventory as $i)
            {
                $soldToBidder = $marketService->sell($i, $price);

                if($soldToBidder)
                {
                    $anySoldToBidder = true;
                    $em->flush();
                }
                else
                {
                    $inventoryToUpdateMinimumPriceFor[$i->getItem()->getId()] = $i->getItem();
                }
            }

            if($anySoldToBidder)
                $responseService->setReloadInventory();
        }

        $em->flush();

        foreach($inventoryToUpdateMinimumPriceFor as $i)
            $marketService->updateLowestPriceForItem($i);

        $em->flush();

        return $responseService->success();
    }
}
