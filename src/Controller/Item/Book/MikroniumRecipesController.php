<?php
namespace App\Controller\Item\Book;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Functions\RecipeRepository;
use App\Model\ItemQuantity;
use App\Service\CookingService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @Route("/item/mikroniumRecipes")
 */
class MikroniumRecipesController extends AbstractController
{
    private function getRecipes(): array
    {
        return RecipeRepository::findBy(fn($recipe) => preg_match('/(^|,)1129:/', $recipe['ingredients']));
    }

    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'mikroniumRecipes/#/upload');

        $recipeNames = array_map(fn(array $recipe) => $recipe['name'], $this->getRecipes());

        $message = $cookingService->showRecipeNamesToCookingBuddy($user, $recipeNames);

        return $responseService->itemActionSuccess($message);
    }

    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService
    )
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'mikroniumRecipes/#/read');

        $recipes = $this->getRecipes();

        $recipeTexts = [
            '# The Science of Ensmallening',
            'Many items can be ensmallened through the use of Mikronium. If you have too many Glowing Six-sided Die, or blocks of Limestone, then Mikronium may become your best friend!',
            'Introduce Mikronium to any of the following to ensmallen them:'
        ];

        $items = [];

        foreach($recipes as $recipe)
        {
            $ingredients = $inventoryService->deserializeItemList($recipe['ingredients']);

            $ingredients = array_values(array_filter($ingredients, fn(ItemQuantity $q) => $q->item->getName() !== 'Mikronium'));

            if(count($ingredients) > 1)
                continue;

            $items[] = '* ' . $ingredients[0]->item->getName();
        }

        sort($items);

        $recipeTexts[] = implode("\r\n", $items);

        return $responseService->itemActionSuccess(implode("\r\n\r\n", $recipeTexts));
    }
}
