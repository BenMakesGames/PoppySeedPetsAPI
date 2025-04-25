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
use App\Entity\TradesUnlocked;
use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Enum\StoryEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Model\StoryStep;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\StoryService;
use App\Service\TraderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/merchantFish")]
class MerchantFishController
{
    #[Route("/{inventory}/talk", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, IRandom $rng,
        StoryService $storyService, Request $request, TraderService $traderService, InventoryService $inventoryService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'merchantFish/#/talk');

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Trader))
        {
            $response = $storyService->doStory($user, StoryEnum::MERCHANT_FISH_MERCHANT, $request->request, $inventory);

            return $responseService->success($response, [ SerializationGroupEnum::STORY ]);
        }
        else
        {
            $em->remove($inventory);

            $responseService->setReloadInventory();

            $lockedTradeGroups = $traderService->getLockedTradeGroups($user);

            if(count($lockedTradeGroups) === 0)
            {
                $loot = $rng->rngNextFromArray([
                    $rng->rngNextFromArray([ 'Yellow Dye', 'Green Dye' ]),
                    'Spice Rack',
                    $rng->rngNextFromArray([ 'Silver Bar', 'Gold Bar' ]),
                    'Tentacle',
                    'White Cloth',
                    'Secret Seashell'
                ]);

                $message = 'You return the Merchant Fish to the Nation-state of Tell Samarzhoustia, who give you ' . $loot . ' as thanks. Also, they let you keep the fish bowl.';

                $inventoryService->receiveItem($loot, $user, $user, 'Received from Tell Samarzhoustia as thanks for a Merchant Fish.', $inventory->getLocation(), $inventory->getLockedToOwner());
                $inventoryService->receiveItem('Crystal Ball', $user, $user, 'This Crystal Ball was once acting as a fish bowl for a Merchant Fish.', $inventory->getLocation(), $inventory->getLockedToOwner());
            }
            else
            {
                $newTrades = new TradesUnlocked(
                    user: $user,
                    trades: $rng->rngNextFromArray($lockedTradeGroups)
                );

                $em->persist($newTrades);

                $message = 'You return the Merchant Fish to the Nation-state of Tell Samarzhoustia, who expand their trading offers as thanks. Also, they let you keep the fish bowl.';

                $inventoryService->receiveItem('Crystal Ball', $user, $user, 'This Crystal Ball was once acting as a fish bowl for a Merchant Fish.', $inventory->getLocation(), $inventory->getLockedToOwner());
            }

            $storyStep = new StoryStep();

            $storyStep->storyTitle = 'Merchant Fish Merchant';
            $storyStep->style = 'description';
            $storyStep->background = null;
            $storyStep->image = null;
            $storyStep->content = $message;
            $storyStep->choices = [];

            $em->flush();

            $responseService->setReloadInventory();

            return $responseService->success($storyStep, [ SerializationGroupEnum::STORY ]);
        }
    }
}
