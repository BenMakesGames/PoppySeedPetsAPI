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
use App\Entity\User;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/baabble")]
class BaabbleController extends AbstractController
{
    private const LameShit = [
        'Crooked Stick', 'Scales', 'Tea Leaves', 'Aging Powder', 'Fluff', 'Pointer', 'Creamy Milk', 'Silica Grounds',
    ];

    private const OkayStuff = [
        'Crooked Stick',
        'Iron Ore',
        'Plastic', 'Plastic',
        'Glass', 'Paper', 'Talon', 'Feathers', 'Glue',
    ];

    private const GoodStuff = [
        'Quintessence', 'Quintessence', 'Wings',
        'Iron Ore', 'Iron Ore', 'Iron Bar',
        'Silver Ore', 'Silver Ore',
        'Gold Ore',
        'Dark Scales', 'Hash Table', 'Paper Bag', 'Finite State Machine', 'Fiberglass', 'Tiny Scroll of Resources'
    ];

    private const WeirdStuff = [
        'Really Big Leaf', 'Music Note', 'Bag of Beans', 'Crystal Ball', 'Linens and Things', 'Dark Matter',
        'Coriander Flower', 'Charcoal', 'Tentacle', 'XOR', 'Liquid-hot Magma', 'Quinacridone Magenta Dye', 'Gypsum',
        'Tiny Black Hole', 'Chocolate Bar',
    ];

    private const RareStuff = [
        'Blackonite', 'Everice', 'Striped Microcline', 'Firestone', 'Black Feathers',
        'Magic Smoke', 'Lightning in a Bottle',
    ];

    #[Route("/black/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openBlackBaabble(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, UserStatsService $userStatsRepository, IRandom $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'baabble/black/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();
        $createdBy = $inventory->getCreatedBy();

        $items = [];

        $lameThings = $squirrel3->rngNextInt(2, 8);
        $okayThings = $squirrel3->rngNextInt(7, 17);
        $goodThings = $squirrel3->rngNextInt(0, 9);

        for($i = 0; $i < $lameThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::LameShit);

        for($i = 0; $i < $okayThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::OkayStuff);

        for($i = 0; $i < $goodThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::GoodStuff);

        $weirdItem = $squirrel3->rngNextFromArray(self::WeirdStuff);

        $noteworthy = [ $squirrel3->rngNextFromArray($items), $weirdItem ];

        shuffle($noteworthy);

        $items[] = $weirdItem;

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $em->remove($inventory);

        foreach($items as $itemName)
            $inventoryService->receiveItem($itemName, $user, $createdBy, 'Found inside a ' . $inventory->getItem()->getName() . '.', $location, $lockedToOwner);

        $em->flush();

        return $responseService->itemActionSuccess('You open the Baabble, and, like, ' . count($items) . ' items come flying out! ' . $noteworthy[0] . ', and ' . $noteworthy[1] . ', among them!', [ 'itemDeleted' => true ]);
    }

    #[Route("/white/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openWhiteBaabble(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, UserStatsService $userStatsRepository, IRandom $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'baabble/white/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();
        $createdBy = $inventory->getCreatedBy();

        $items = [];

        $lameThings = $squirrel3->rngNextInt(4, 14);
        $okayThings = $squirrel3->rngNextInt(10, 18);
        $goodThings = $squirrel3->rngNextInt(0, 9);
        $rareThings = 1;

        for($i = 0; $i < $lameThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::LameShit);

        for($i = 0; $i < $okayThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::OkayStuff);

        for($i = 0; $i < $goodThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::GoodStuff);

        for($i = 0; $i < $rareThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::RareStuff);

        $weirdItem = $squirrel3->rngNextFromArray(self::WeirdStuff);

        $noteworthy = [ $squirrel3->rngNextFromArray($items), $weirdItem ];

        shuffle($noteworthy);

        $items[] = $weirdItem;

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $em->remove($inventory);

        foreach($items as $itemName)
            $inventoryService->receiveItem($itemName, $user, $createdBy, 'Found inside a ' . $inventory->getItem()->getName() . '.', $location, $lockedToOwner);

        $em->flush();

        return $responseService->itemActionSuccess('You open the Baabble, and, like, ' . count($items) . ' items come flying out! ' . $noteworthy[0] . ', and ' . $noteworthy[1] . ', among them!', [ 'itemDeleted' => true ]);
    }

    #[Route("/gold/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openGoldBaabble(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, UserStatsService $userStatsRepository, IRandom $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'baabble/gold/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();
        $createdBy = $inventory->getCreatedBy();

        $items = [];

        $lameThings = $squirrel3->rngNextInt(4, 14);
        $okayThings = $squirrel3->rngNextInt(6, 16);
        $goodThings = $squirrel3->rngNextInt(0, 12);
        $weirdThings = $squirrel3->rngNextInt(0, 10);
        $rareThings = $squirrel3->rngNextInt(1, 5);

        for($i = 0; $i < $lameThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::LameShit);

        for($i = 0; $i < $okayThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::OkayStuff);

        for($i = 0; $i < $goodThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::GoodStuff);

        for($i = 0; $i < $weirdThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::WeirdStuff);

        for($i = 0; $i < $rareThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::RareStuff);

        $noteworthy = [ $squirrel3->rngNextFromArray($items), $squirrel3->rngNextFromArray($items) ];

        shuffle($noteworthy);

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $em->remove($inventory);

        foreach($items as $itemName)
            $inventoryService->receiveItem($itemName, $user, $createdBy, 'Found inside a ' . $inventory->getItem()->getName() . '.', $location, $lockedToOwner);

        $em->flush();

        if($noteworthy[0] === $noteworthy[1])
            return $responseService->itemActionSuccess('You open the Baabble, and, like, ' . count($items) . ' items come flying out! ' . $noteworthy[0] . ' among them!', [ 'itemDeleted' => true ]);
        else
            return $responseService->itemActionSuccess('You open the Baabble, and, like, ' . count($items) . ' items come flying out! ' . $noteworthy[0] . ', and ' . $noteworthy[1] . ', among them!', [ 'itemDeleted' => true ]);
    }

    #[Route("/shiny/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openShinyBaabble(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, UserStatsService $userStatsRepository, IRandom $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'baabble/shiny/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();
        $createdBy = $inventory->getCreatedBy();

        $items = [];

        $lameThings = $squirrel3->rngNextInt(0, 12);
        $okayThings = $squirrel3->rngNextInt(4, 16);
        $goodThings = $squirrel3->rngNextInt(4, 16);
        $weirdThings = $squirrel3->rngNextInt(4, 14);
        $rareThings = $squirrel3->rngNextInt(3, 7);

        for($i = 0; $i < $lameThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::LameShit);

        for($i = 0; $i < $okayThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::OkayStuff);

        for($i = 0; $i < $goodThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::GoodStuff);

        for($i = 0; $i < $weirdThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::WeirdStuff);

        for($i = 0; $i < $rareThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::RareStuff);

        $noteworthy = [ $squirrel3->rngNextFromArray($items), $squirrel3->rngNextFromArray($items) ];

        shuffle($noteworthy);

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $em->remove($inventory);

        foreach($items as $itemName)
            $inventoryService->receiveItem($itemName, $user, $createdBy, 'Found inside a ' . $inventory->getItem()->getName() . '.', $location, $lockedToOwner);

        $em->flush();

        if($noteworthy[0] === $noteworthy[1])
            return $responseService->itemActionSuccess('You open the Baabble, and, like, ' . count($items) . ' items come flying out! ' . $noteworthy[0] . ' among them!', [ 'itemDeleted' => true ]);
        else
            return $responseService->itemActionSuccess('You open the Baabble, and, like, ' . count($items) . ' items come flying out! ' . $noteworthy[0] . ', and ' . $noteworthy[1] . ', among them!', [ 'itemDeleted' => true ]);
    }
}
