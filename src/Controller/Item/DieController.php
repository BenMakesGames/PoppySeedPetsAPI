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


namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Service\HollowEarthService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/die")]
class DieController
{
    #[Route("/{inventory}/roll", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function roll(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, IRandom $rng,
        HollowEarthService $hollowEarthService, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'die/#/roll');

        $itemName = $inventory->getItem()->getName();

        if(!array_key_exists($itemName, HollowEarthService::DICE_ITEMS))
            throw new PSPInvalidOperationException('The selected item is not a die!? (Weird! Reload and try again??)');

        if($itemName === 'Dreidel')
        {
            $roll = $rng->rngNextFromArray([
                'נ', 'ג', 'ה', 'ש'
            ]);
        }
        else
        {
            $sides = HollowEarthService::DICE_ITEMS[$itemName];
            $roll = $rng->rngNextInt(1, $sides);
        }

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::HollowEarth))
            return $responseService->itemActionSuccess('You got a ' . $roll . '.', []);

        $hollowEarthService->unlockHollowEarth($user);

        $em->flush();

        if($inventory->getLocation() === LocationEnum::BASEMENT)
            $location = 'under the basement stairs';
        else
            $location = 'on one of the walls';

        return $responseService->itemActionSuccess("You rolled a $roll.\n\nYou notice a door $location that you're _quite_ certain did not exist before now...\n\nThat's... more than a little weird.\n\n(A new location has been made available - check the menu...)");
    }
    #[Route("/{inventory}/changeYourFate", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function changeYourFate(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'die/#/changeYourFate');

        $user->setFate();

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess("You throw the die, and it vanishes the instant before it hits the ground!\n\n_You_ feel the same, but the world around you seems... different.\n\n(Your daily trades & pet shelter offerings have been changed!)");
    }
}
