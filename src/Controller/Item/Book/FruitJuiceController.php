<?php
namespace App\Controller\Item\Book;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Repository\RecipeRepository;
use App\Service\CookingService;
use App\Service\ResponseService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/fruitJuice")
 */
class FruitJuiceController extends PoppySeedPetsItemController
{
    private const RECIPES = [
        'Red Juice, and Pectin',
        'Orange Juice, and Pectin',
        'Carrot Juice, and Pectin',
        'Pamplemousse Juice, and Pectin',
        'Tall Glass of Yellownade',
        'Short Glass of Greenade',
        'Chicha Morada',
    ];

    /**
     * @Route("/{inventory}/upload", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService
    )
    {
        $this->validateInventory($inventory, 'fruitJuice/#/upload');

        $message = $cookingService->showRecipeNamesToCookingBuddy($this->getUser(), self::RECIPES);

        return $responseService->itemActionSuccess($message);
    }

    /**
     * @Route("/{inventory}/read", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(
        Inventory $inventory, ResponseService $responseService, RecipeRepository $recipeRepository
    )
    {
        $this->validateInventory($inventory, 'fruitJuice/#/read');

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

Pineapples aren\'t native to Poppy Seed Pets island, so can be hard to find, but they seem to be closely associated with magic, so you might start with scrolls and spirits. I also heard someone dreamed one into existence, once, so... you know... maybe try that??? (Couldn\'t hurt to try!)');
    }
}
