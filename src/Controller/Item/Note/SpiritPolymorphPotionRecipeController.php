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


namespace App\Controller\Item\Note;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Service\CookingService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/note/spiritPolymorphPotion")]
class SpiritPolymorphPotionRecipeController extends AbstractController
{
    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'note/spiritPolymorphPotion/#/upload');

        $message = $cookingService->showRecipeNamesToCookingBuddy($user, [
            'Spirit Polymorph Potion (A)',
            'Spirit Polymorph Potion (B)',
        ]);

        return $responseService->itemActionSuccess($message);
    }

    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function readSpiritPolymorphPotion(Inventory $inventory, ResponseService $responseService): JsonResponse
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'note/spiritPolymorphPotion/#/read');

        return $responseService->itemActionSuccess('* Striped Microcline
* Witch-hazel
* Carrot

Warning: if you don\'t have a Carrot handy, you can also use a Large Radish - just make sure not to use Spicy Peps, or you\'ll create a substance that\'s very toxic to spirits! 
');
    }
}
