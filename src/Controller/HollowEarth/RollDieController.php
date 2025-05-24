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


namespace App\Controller\HollowEarth;

use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\CalendarFunctions;
use App\Functions\InventoryHelpers;
use App\Service\HollowEarthService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/hollowEarth")]
class RollDieController
{
    #[Route("/roll", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function rollDie(
        ResponseService $responseService, EntityManagerInterface $em, HollowEarthService $hollowEarthService,
        Request $request, InventoryService $inventoryService, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();
        $player = $user->getHollowEarthPlayer();
        $now = new \DateTimeImmutable();

        if($player === null)
            throw new PSPInvalidOperationException('You gotta\' visit the Hollow Earth page at least once before taking this kind of action.');

        if($player->getChosenPet() === null)
            throw new PSPInvalidOperationException('You must choose a pet to lead the group.');

        if($player->getCurrentAction() !== null || $player->getMovesRemaining() > 0)
            throw new PSPInvalidOperationException('Cannot roll a die at this time...');

        $itemName = $request->request->getString('die');

        if(!array_key_exists($itemName, HollowEarthService::DICE_ITEMS))
            throw new PSPFormValidationException('You must specify a die to roll.');

        $inventory = InventoryHelpers::findOneToConsume($em, $user, $itemName);

        if(!$inventory)
            throw new PSPNotFoundException('You do not have a ' . $itemName . '!');

        $sides = HollowEarthService::DICE_ITEMS[$itemName];
        $moves = $rng->rngNextInt(1, $sides);

        $responseService->addFlashMessage('You rolled a ' . $moves . '!');

        $em->remove($inventory);

        $player->setMovesRemaining($moves);

        $hollowEarthService->advancePlayer($player);

        if(CalendarFunctions::isEaster($now) && $rng->rngNextInt(1, 4) === 1)
        {
            if($rng->rngNextInt(1, 6) === 6)
            {
                if($rng->rngNextInt(1, 12) === 12)
                    $loot = 'Pink Plastic Egg';
                else
                    $loot = 'Yellow Plastic Egg';
            }
            else
                $loot = 'Blue Plastic Egg';

            $inventoryService->receiveItem($loot, $user, $user, $user->getName() . ' spotted this while traveling with ' . $player->getChosenPet()->getName() . ' through the Hollow Earth!', LocationEnum::Home)
                ->setLockedToOwner($loot !== 'Blue Plastic Egg')
            ;

            if($rng->rngNextInt(1, 10) === 1)
                $responseService->addFlashMessage('(While moving through the Hollow Earth, you spot a ' . $loot . '! But you decide to leave it there... ... nah, I\'m just kidding, of course you scoop the thing up immediately!)');
            else
                $responseService->addFlashMessage('(While moving through the Hollow Earth, you spot a ' . $loot . '!)');
        }

        $em->flush();

        return $responseService->success($hollowEarthService->getResponseData($user), [ SerializationGroupEnum::HOLLOW_EARTH ]);
    }
}
