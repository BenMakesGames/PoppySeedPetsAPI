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


namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ItemRepository;
use App\Repository\InventoryRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/wrinkledCloth")]
class WrinkledClothController
{
    #[Route("/{inventory}/iron", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function ironWrinkledCloth(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        UserStatsService $userStatsRepository, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'wrinkledCloth/#/iron');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $ironBar = InventoryRepository::findOneToConsume($em, $user, 'Iron Bar');

        if(!$ironBar)
            throw new PSPNotFoundException('You\'ll need an Iron (... Bar) to do that!');

        $comment = $user->getName() . ' found this inside ' . $inventory->getItem()->getNameWithArticle() . '.';

        $lootInfo = $rng->rngNextFromArray([
            [ 'item' => 'Fluff', 'newCloth' => 'White Cloth' ],
            [ 'item' => 'Plastic', 'newCloth' => 'White Cloth' ],
            [ 'item' => 'Glowing Six-sided Die', 'newCloth' => 'White Cloth' ],
            [ 'item' => 'Super-wrinkled Cloth', 'newCloth' => 'White Cloth' ],
            [ 'item' => 'Feathers', 'newCloth' => 'White Cloth' ],
            [ 'item' => 'Gold Ring', 'newCloth' => 'White Cloth' ],
            [ 'item' => 'Talon', 'newCloth' => 'White Cloth' ],
            [ 'item' => 'Tiny Scroll of Resources', 'newCloth' => 'White Cloth' ],
            [ 'item' => 'Password', 'newCloth' => 'White Cloth' ],
            [ 'item' => 'Quintessence', 'newCloth' => 'White Cloth' ],
            [ 'item' => 'Secret Seashell', 'newCloth' => 'White Cloth' ],

            [ 'item' => 'Sand-covered... Something', 'newCloth' => 'Filthy Cloth' ],
            [ 'item' => 'Butter', 'newCloth' => 'Filthy Cloth' ],
            [ 'item' => 'Cheese Ravioli', 'newCloth' => 'Filthy Cloth' ],
            [ 'item' => 'Mixed Nuts', 'newCloth' => 'Filthy Cloth' ],
            [ 'item' => 'Rice Flour', 'newCloth' => 'Filthy Cloth' ],
            [ 'item' => 'Grilled Fish', 'newCloth' => 'Filthy Cloth' ],
            [ 'item' => 'Glue', 'newCloth' => 'Filthy Cloth' ],
            [ 'item' => 'Shoyu Tamago', 'newCloth' => 'Filthy Cloth' ],

            [ 'item' => 'Brownie', 'newCloth' => 'Chocolate-stained Cloth' ],
        ]);

        $location = $inventory->getLocation();

        $loot = $inventoryService->receiveItem($lootInfo['item'], $user, $user, $comment, $location);

        $stat = $userStatsRepository->incrementStat($user, 'Ironed ' . $inventory->getItem()->getNameWithArticle());

        $inventory
            ->addComment('You straighted out the ' . $inventory->getItem()->getName() . ' into ' . $loot->getItem()->getNameWithArticle() . '...')
            ->changeItem(ItemRepository::findOneByName($em, $lootInfo['newCloth']));

        if($lootInfo['item'] === 'Super-wrinkled Cloth')
            $message = 'Ironing out the cloth, you found _another_ ' . $loot->getItem()->getNameWithArticle() . ' tangled up inside! (Whoa! Meta!)';
        else
            $message = 'Ironing out the cloth, you found ' . $loot->getItem()->getNameWithArticle() . ' tangled up inside!';

        if($lootInfo['newCloth'] !== 'White Cloth')
            $message .= ' Unfortunately, what with all the ironing, you unintentionally filthified the cloth... (Why is house cleaning so harrrrrd!)';

        if($stat->getValue() == 4 || ($stat->getValue() >= 8 && $rng->rngNextInt(1, $stat->getValue()) <= 2))
        {
            $message .= ' Also, the Iron (Bar) broke while you were ironing! Agh! Why do these things happen?!?';

            if($stat->getValue() == 4)
                $message .= ' (And _how??_ Like, what about an Iron _Bar_ can-- \*sigh\* you know, never mind. It\'s... it\'s fine. Whatever.)';

            $em->remove($ironBar);
        }

        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
