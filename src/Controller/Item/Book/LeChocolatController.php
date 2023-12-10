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

#[Route("/item/leChocolat")]
class LeChocolatController extends AbstractController
{
    private function getRecipes(): array
    {
        return RecipeRepository::findBy(fn($recipe) => mb_strpos(mb_strtolower($recipe['name']), 'chocolat') !== false);
    }

    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'leChocolat/#/upload');

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
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'leChocolat/#/read');

        $recipes = $this->getRecipes();

        $recipeTexts = [
            '# Chocolate',
            '### Table of Contents'
        ];

        $recipeTexts[] = implode("\r\n", array_map(function($recipe) {
            return '* ' . $recipe['name'];
        }, $recipes));

        foreach($recipes as $recipe)
        {
            $ingredients = $inventoryService->deserializeItemList($recipe['ingredients']);

            usort($ingredients, fn($a, $b) => $a->item->getName() <=> $b->item->getName());

            $items = array_map(function(ItemQuantity $q) {
                if($q->quantity > 1)
                    return '* ' . $q->quantity . '× ' . $q->item->getName();
                else
                    return '* ' . $q->item->getName();
            }, $ingredients);

            $recipeTexts[] = '### ' . $recipe['name'] . "\r\n" . implode("\r\n", $items);
        }

        return $responseService->itemActionSuccess(implode("\r\n\r\n", $recipeTexts));
    }
}
