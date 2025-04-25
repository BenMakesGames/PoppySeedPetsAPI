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
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetLocationEnum;
use App\Enum\UserStatEnum;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogFactory;
use App\Model\ActivityCallback;
use App\Model\PetChanges;
use App\Service\HotPotatoService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\TransactionService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/hornOfPlenty")]
class HornOfPlentyController
{
    #[Route("/{inventory}/use", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function use(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService, IRandom $rng, HotPotatoService $hotPotatoService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();
        $numberOfTosses = HotPotatoService::countTeleports($inventory);

        $petsAtHome = $em->getRepository(Pet::class)->findBy([
            'owner' => $user,
            'location' => PetLocationEnum::HOME
        ]);

        if(count($petsAtHome) == 0)
            return $responseService->itemActionSuccess('You need to have a pet at home to help!');

        /** @var Pet $helperPet */
        $helperPet = $rng->rngNextFromArray($petsAtHome);

        ItemControllerHelpers::validateInventory($user, $inventory, 'hornOfPlenty/#/use');

        // get an item:
        $spice = $inventory->getSpice();

        $loot = $rng->rngNextFromArray([
            [ 'Smashed Potatoes', 1 ],
            [ 'Potato-mushroom Stuffed Onion', 1 ],
            [ 'Berry Cobbler', 1 ],
            [ 'Slice of Red Pie', 1 ],
            [ 'Beans', 1 ],
            [ 'Carrot Preserves', 1 ],
            [ 'Caramel-covered Popcorn', 1 ],
            [ 'Pumpkin Bread', 1 ],
            [ 'Small Bag of Fertilizer', 1 ],
            [ 'Purple Hard Candy', 3 ],
            [ 'Yellow Hard Candy', 3 ],
        ]);

        for($i = 0; $i < $loot[1]; $i++)
        {
            $inventoryService->receiveItem($loot[0], $user, $inventory->getCreatedBy(), $helperPet->getName() . ' found this inside a Horn of Plenty.', $inventory->getLocation())
                ->setSpice($spice);
        }

        $lootDescription = $loot[1] == 1 ? $loot[0] : "$loot[1]x $loot[0]";

        $description = $helperPet->getName() . ' rummaged around in the Horn of Plenty, and found ' . $lootDescription . '!';

        $changes = new PetChanges($helperPet);

        $helperPet
            ->increaseSafety(4)
            ->increaseLove(4)
            ->increaseEsteem(4);

        PetActivityLogFactory::createReadLog($em, $helperPet, ActivityHelpers::PetName($helperPet) . ' found ' . $lootDescription . ' in a Horn of Plenty.')
            ->setChanges($changes->compare($helperPet))
            ->addInterestingness(PetActivityLogInterestingnessEnum::PLAYER_ACTION_RESPONSE);

        if($rng->rngNextInt(2, 6) <= $numberOfTosses)
        {
            $inventory
                ->changeItem(ItemRepository::findOneByName($em, 'Empty Horn of Plenty'))
                ->setLockedToOwner(false);

            $em->flush();

            return $responseService->itemActionSuccess($description . ' Apparently drained of all its magic, the horn became an Empty Horn of Plenty.', [ 'itemDeleted' => true ]);
        }
        else
        {
            return $hotPotatoService->teleportItem($inventory, $description);
        }
    }
}
