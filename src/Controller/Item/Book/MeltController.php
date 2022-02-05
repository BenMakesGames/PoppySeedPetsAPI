<?php
namespace App\Controller\Item\Book;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Entity\Recipe;
use App\Functions\ArrayFunctions;
use App\Model\ItemQuantity;
use App\Repository\RecipeRepository;
use App\Service\CookingService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/melt")
 */
class MeltController extends PoppySeedPetsItemController
{
    /**
     * @return Recipe[]
     */
    private function getRecipes(RecipeRepository $recipeRepository): array
    {
        return $recipeRepository->createQueryBuilder('r')
            ->andWhere('r.name LIKE :melt')
            ->setParameter('melt', 'Melt%')
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
        $this->validateInventory($inventory, 'melt/#/upload');

        $recipes = $this->getRecipes($recipeRepository);

        $message = $cookingService->showRecipesToCookingBuddy($this->getUser(), $recipes);

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
        $this->validateInventory($inventory, 'melt/#/read');

        $recipes = $this->getRecipes($recipeRepository);

        $recipeTexts = [
            '# Melt',
            'Many items can be melted down through the use of Liquid-hot Magma. If you have too many Pokers, or Silver Keys, then Liquid-hot Magma may become your best friend!',
            'Combine any of the following with Liquid-hot Magma to melt them down:'
        ];

        $items = [];

        foreach($recipes as $recipe)
        {
            $ingredients = $inventoryService->deserializeItemList($recipe->getIngredients());

            $ingredients = array_values(array_filter($ingredients, fn(ItemQuantity $q) => $q->item->getName() !== 'Liquid-hot Magma'));

            if(count($ingredients) > 1)
                continue;

            $items[] = '* ' . $ingredients[0]->item->getName();
        }

        sort($items);

        $recipeTexts[] = implode("\r\n", $items);

        return $responseService->itemActionSuccess(implode("\r\n\r\n", $recipeTexts));
    }
}
