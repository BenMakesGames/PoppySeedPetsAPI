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
use App\Functions\ArrayFunctions;
use App\Functions\SpiceRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/gnomesFavor")]
class GnomesFavorController extends AbstractController
{
    private const string USER_STAT_NAME = 'Redeemed a Gnome\'s Favor';

    private const array GNOMISH_MAGIC = [
        ' (Whoa! Magic!)',
        ' (Gnomish magic!)',
        ' #justgnomethings',
        ' (Smells... _gnomish_...)',
        ' (Ooh! Sparkly!)',
    ];

    #[Route("/{inventory}/quint", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getQuint(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng, UserStatsService $userStatsRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'gnomesFavor/#/quint');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        for($i = 0; $i < 2; $i++)
            $inventoryService->receiveItem('Quintessence', $user, $user, $user->getName() . ' got this from a Gnome\'s Favor.', $location);

        $userStatsRepository->incrementStat($user, self::USER_STAT_NAME);

        $em->remove($inventory);

        $em->flush();

        $extraSilliness = $rng->rngNextFromArray(self::GNOMISH_MAGIC);

        return $responseService->itemActionSuccess('Two Quintessence materialize in front of you with a flash! ' . $extraSilliness, [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/food", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getFood(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $squirrel3, UserStatsService $userStatsRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'gnomesFavor/#/food');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        $possibleSpices = [
            'Rain-scented',
            'Juniper',
            'with Rosemary',
            'with Toad Jelly',
            'Cheesy',
        ];

        shuffle($possibleSpices);

        $possibleFood = [
            'Cheese',
            'Fisherman\'s Pie',
            'Poutine',
            'Stargazy Pie',
            '15-bean Soup',
            'Brownie',
            'Pumpkin Custard',
        ];

        shuffle($possibleFood);

        for($i = 0; $i < 5; $i++)
        {
            $newInventory[] = $inventoryService->receiveItem($possibleFood[$i], $user, $user, $user->getName() . ' got this from a Gnome\'s Favor.', $location)
                ->setSpice(SpiceRepository::findOneByName($em, $possibleSpices[$i]))
            ;
        }

        $userStatsRepository->incrementStat($user, self::USER_STAT_NAME);

        $em->remove($inventory);

        $em->flush();

        $itemList = array_map(fn(Inventory $i) => $i->getFullItemName(), $newInventory);
        sort($itemList);

        $extraSilliness = $squirrel3->rngNextFromArray(self::GNOMISH_MAGIC);

        return $responseService->itemActionSuccess(ArrayFunctions::list_nice($itemList) . ' materialize in front of you with a flash! ' . $extraSilliness, [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/treasure", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getTreasure(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng, UserStatsService $userStatsRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'gnomesFavor/#/treasure');

        $location = $inventory->getLocation();

        $possibleItems = [
            'Silver Bar', 'Silver Bar', 'Silver Bar', 'Silver Bar', 'Silver Bar',
            'Gold Bar', 'Gold Bar', 'Gold Bar',
            'Blue Bow',
            'Key Ring',
            'Hourglass',
            'Spice Rack',
            'Sand Dollar',
            $rng->rngNextFromArray([ 'Password', 'Cryptocurrency Wallet' ]),
        ];

        shuffle($possibleItems);

        $newInventory = [];

        for($i = 0; $i < 3; $i++)
            $newInventory[] = $inventoryService->receiveItem($possibleItems[$i], $user, $user, $user->getName() . ' got this from a Gnome\'s Favor.', $location);

        $userStatsRepository->incrementStat($user, self::USER_STAT_NAME);

        $em->remove($inventory);

        $em->flush();

        $itemList = array_map(fn(Inventory $i) => $i->getFullItemName(), $newInventory);
        sort($itemList);

        $extraSilliness = $rng->rngNextFromArray(self::GNOMISH_MAGIC);

        return $responseService->itemActionSuccess(ArrayFunctions::list_nice($itemList) . ' materialize in front of you with a flash! ' . $extraSilliness, [ 'itemDeleted' => true ]);
    }
}
