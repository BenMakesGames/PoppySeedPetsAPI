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
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/sandDollar")]
class SandDollarController
{
    #[Route("/{inventory}/loot", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function lootSandDollar(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        EntityManagerInterface $em, TransactionService $transactionService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'sandDollar/#/loot');

        $transactionService->getMoney($user, 1, 'Found inside a Sand Dollar.');

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $inventoryService->receiveItem('Silica Grounds', $user, $user, $user->getName() . ' found this inside a Sand Dollar.', $location, $locked)
            ->setSpice($inventory->getSpice())
        ;

        if($rng->rngNextInt(1, 10) === 1)
        {
            $inventoryService->receiveItem('String', $user, $user, $user->getName() . ' found this inside a Sand Dollar.', $location, $locked)
                ->setSpice($inventory->getSpice())
            ;

            $message = 'You rummage around inside the Sand Dollar. There was 1 dollar - er, I mean, moneys - and also a bit of sand - er, I mean, Silica Grounds - and oh! What\'s this? Oh, it\'s a soggy bit of String. Well, it\'ll dry out.';
        }
        else if($rng->rngNextInt(1, 10) === 1)
        {
            $inventoryService->receiveItem('Talon', $user, $user, $user->getName() . ' found this inside a Sand Dollar.', $location, $locked)
                ->setSpice($inventory->getSpice())
            ;

            $message = 'You rummage around inside the Sand Dollar. There was 1 dollar - er, I mean, moneys - and also a bit of sand - er, I mean, Silica Grounds - and hey: there\'s something else in here! It\'s a shark tooth, maybe? Or, like, a claw? Maybe a Talon? Let\'s go with Talon.';
        }
        else if($rng->rngNextInt(1, 20) === 1)
        {
            $inventoryService->receiveItem('Mermaid Egg', $user, $user, $user->getName() . ' found this inside a Sand Dollar.', $location, $locked)
                ->setSpice($inventory->getSpice())
            ;

            $message = 'You rummage around inside the Sand Dollar. There was 1 dollar - er, I mean, moneys - and also a bit of sand - er, I mean, Silica Grounds - and oh! There\'s something squishy! Ah! It\'s a Mermaid Egg!';
        }
        else if($rng->rngNextInt(1, 20) === 1)
        {
            $inventoryService->receiveItem('Glowing Six-sided Die', $user, $user, $user->getName() . ' found this inside a Sand Dollar.', $location, $locked)
                ->setSpice($inventory->getSpice())
            ;

            $message = 'You rummage around inside the Sand Dollar. There was 1 dollar - er, I mean, moneys - and also a bit of sand - er, I mean, Silica Grounds - and hm, something... geometric? Ah: it\'s a die. And it\'s... glowing...';
        }
        else if($rng->rngNextInt(1, 20) === 1)
        {
            $inventoryService->receiveItem('Plastic', $user, $user, $user->getName() . ' found this inside a Sand Dollar.', $location, $locked)
                ->setSpice($inventory->getSpice())
            ;

            $message = 'You rummage around inside the Sand Dollar. There was 1 dollar - er, I mean, moneys - and also a bit of sand - er, I mean, Silica Grounds - and what? There\'s some... Plastic in here?? That\'s kind of sad :| Well... one less piece in the ocean, I guess...';
        }
        else if($rng->rngNextInt(1, 30) === 1)
        {
            $inventoryService->receiveItem('Secret Seashell', $user, $user, $user->getName() . ' found this inside a Sand Dollar.', $location, $locked)
                ->setSpice($inventory->getSpice())
            ;

            $message = 'You rummage around inside the Sand Dollar. There was 1 dollar - er, I mean, moneys - and also a bit of sand - er, I mean, Silica Grounds - and oh! What\'s this? A Secret Seasheeeeeelllllll!';
        }
        else if($rng->rngNextInt(1, 40) === 1)
        {
            $inventoryService->receiveItem('Cyan Bow', $user, $user, $user->getName() . ' found this inside a Sand Dollar.', $location, $locked)
                ->setSpice($inventory->getSpice())
            ;

            $message = 'You rummage around inside the Sand Dollar. There was 1 dollar - er, I mean, moneys - and also a bit of sand - er, I mean, Silica Grounds - and oh! What\'s this? Some kind of bright blue hair bow!';
        }
        else
        {
            $message = 'You rummage around inside the Sand Dollar. There was 1 dollar - er, I mean, moneys - and also a bit of sand - er, I mean, Silica Grounds.';
        }

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
