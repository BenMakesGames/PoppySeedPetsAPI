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


namespace App\Controller\Item\Book;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Service\CookingService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/fruitJuice")]
class FruitJuiceController extends AbstractController
{
    private const array RECIPES = [
        'Red Juice, and Pectin',
        'Orange Juice, and Pectin',
        'Carrot Juice, and Pectin',
        'Pamplemousse Juice, and Pectin',
        'Tall Glass of Yellownade',
        'Short Glass of Greenade',
        'Chicha Morada',
    ];

    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'fruitJuice/#/upload');

        $message = $cookingService->showRecipeNamesToCookingBuddy($user, self::RECIPES);

        return $responseService->itemActionSuccess($message);
    }

    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(
        Inventory $inventory, ResponseService $responseService
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'fruitJuice/#/read');

        return $responseService->itemActionSuccess('### Fresh-squeezed Fruit Juice

Squeeze a Red, Orange, Pamplemousse, or (even though it\'s not a fruit) Carrot, or (even though it\'s not a plant) Jellyfish, for some fresh-squeezed, um, fruit juice? Vegetable juice? Somethin\' juice.

### Yellownade & Greenade

Just add Yellow or Green Dye to some Sugar water! It\'s called "0% juice", and that has juice in the same, so... it\'s juice!

### Chicha Morada

Finally, a normal one!

* Purple Corn (stay with me!)
* Pineapple
* Red
* Sugar

If you grow yellow Corn, you may be lucky enough to find some Purple Corn in the mix. (Or you can cheat and dye yellow Corn purple, but don\'t tell anyone I told you!)

Pineapples aren\'t native to Poppy Seed Pets Island, so can be hard to find, but they seem to be closely associated with magic, so you might start with scrolls and spirits. I also heard someone dreamed one into existence, once, so... you know... maybe try that??? (Couldn\'t hurt to try!)');
    }
}
