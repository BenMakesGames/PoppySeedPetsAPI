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
use App\Functions\ItemRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/recoveredArchive")]
class RecoveredArchiveController
{
    #[Route("/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openRecoveredArchive(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'recoveredArchive/#/open');

        $threeDeePrinterId = ItemRepository::getIdByName($em, '3D Printer');

        if(InventoryService::countInventory($em, $user->getId(), $threeDeePrinterId, $inventory->getLocation()) < 1)
        {
            return $responseService->itemActionSuccess('You peek at the archive\'s contents. It appears to be a model file for a 3D Printer! If only you had a 3D Printer, you might be able to use it to print the object!');
        }

        $loot = $rng->rngNextFromArray([
            '4-function Calculator',
            'Bananananers Foster Recipe',
            'Bass Guitar',
            'Big Book of Baking',
            'Bird Bath Blueprint',
            'Blue Balloon',
            'Blue Plastic Egg',
            'Bulbun Plushy',
            'Canned Food',
            'Captain\'s Log',
            'Carrot Wine Recipe',
            'Chocolate Bar',
            'Cobbler Recipe',
            'Compass',
            'Compass (the Math Kind)',
            'Cool Sunglasses',
            'Dumbbell',
            'Egg Book Audiobook',
            'EP',
            'Feathers',
            'Fiberglass Flute',
            'Fish Bag',
            'Flowerbomb',
            'Fluff',
            'Flute',
            'Fried Egg',
            'Glowing Six-sided Die',
            'Glue',
            'Gold Bar',
            'Graham Cracker',
            'Grappling Hook',
            'Green Dye',
            'Green Scissors',
            'Hot Dog',
            'Hourglass',
            'Iron Bar',
            'L-Square',
            'Limestone',
            'Linens and Things',
            'Mango',
            'Maraca',
            'Mirror',
            'Minor Scroll of Riches',
            'Noodles',
            'Orange Chocolate Bar',
            'Painted Dumbbell',
            'Paper',
            'Pie Crust',
            'Pie Recipes',
            'Piece of Cetgueli\'s Map',
            'Plastic',
            'Plastic Bottle',
            'Poker',
            'Puddin\' Rec\'pes',
            'Purple Balloon',
            'Red',
            'Red Balloon',
            'Rib',
            'Rock',
            'Ruler',
            'Scroll of Flowers',
            'Scroll of Fruit',
            'Scythe',
            'Secret Seashell',
            'Silver Bar',
            'Simple Sushi',
            'Single',
            'Smashed Potatoes',
            'SOUP',
            'Spicy Chocolate Bar',
            'Spider',
            'Spirit Polymorph Potion Recipe',
            'Stereotypical Bone',
            'Stereotypical Torch',
            'Straw Broom',
            'String',
            'Sun Flag',
            'Sweet Roll',
            'Tentacle',
            'The Art of Tofu',
            'The Umbra',
            'Tinfoil Hat',
            'Toy Alien Gun',
            'Upside-down Saucepan',
            'Useless Fizz',
            'Welcome Note',
            'White Cloth',
            'White Flag',
            'Yellow Balloon',
            'Yellow Dye',
        ]);

        $item = ItemRepository::findOneByName($em, $loot);

        $message = "You peek at the archive's contents. It appears to be a model file for a 3D Printer!\n\nYou load the archive into your 3D Printer. Without any Plastic whatsoever, the device springs to life and begins printing at a furious rate! After the sparks stop and smoke clears, you see that it printed " . $item->getNameWithArticle() . "!\n\n";

        $message .= $rng->rngNextFromArray([
            'Weird.',
            'Okay, then...',
            'Is that how that\'s supposed to happen?',
        ]);

        $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' printed this in their 3D Printer, using ' . $inventory->getItem()->getNameWithArticle() . '.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
