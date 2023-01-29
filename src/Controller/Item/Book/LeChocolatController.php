<?php
namespace App\Controller\Item\Book;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\Recipe;
use App\Functions\ArrayFunctions;
use App\Model\ItemQuantity;
use App\Repository\RecipeRepository;
use App\Service\CookingService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/leChocolat")
 */
class LeChocolatController extends AbstractController
{
    /**
     * @return Recipe[]
     */
    private function getRecipes(RecipeRepository $recipeRepository): array
    {
        return $recipeRepository->createQueryBuilder('r')
            ->andWhere('r.name LIKE :chocolate')
            ->setParameter('chocolate', '%chocolate%')
            ->addOrderBy('r.name', 'ASC')
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * @Route("/{inventory}/upload", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService,
        RecipeRepository $recipeRepository
    )
    {
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'leChocolat/#/upload');

        $recipes = $this->getRecipes($recipeRepository);

        $message = $cookingService->showRecipesToCookingBuddy($user, $recipes);

        return $responseService->itemActionSuccess($message);
    }

    /**
     * @Route("/{inventory}/read", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(
        Inventory $inventory, ResponseService $responseService, RecipeRepository $recipeRepository,
        InventoryService $inventoryService
    )
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'leChocolat/#/read');

        $recipes = $this->getRecipes($recipeRepository);

        $recipeTexts = [
            '# Chocolate',
            '### Table of Contents'
        ];

        $recipeTexts[] = implode("\r\n", array_map(function(Recipe $r) {
            return '* ' . $r->getName();
        }, $recipes));

        foreach($recipes as $recipe)
        {
            $ingredients = $inventoryService->deserializeItemList($recipe->getIngredients());

            usort($ingredients, fn($a, $b) => $a->item->getName() <=> $b->item->getName());

            $items = array_map(function(ItemQuantity $q) {
                if($q->quantity > 1)
                    return '* ' . $q->quantity . '× ' . $q->item->getName();
                else
                    return '* ' . $q->item->getName();
            }, $ingredients);

            $recipeTexts[] = '### ' . $recipe->getName() . "\r\n" . implode("\r\n", $items);
        }

        return $responseService->itemActionSuccess(implode("\r\n\r\n", $recipeTexts));
    }
}
