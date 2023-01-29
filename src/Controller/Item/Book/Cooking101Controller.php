<?php
namespace App\Controller\Item\Book;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Repository\RecipeRepository;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/cooking101")
 */
class Cooking101Controller extends AbstractController
{
    /**
     * @Route("/{inventory}/read", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(
        Inventory $inventory, ResponseService $responseService, RecipeRepository $recipeRepository
    )
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'cooking101/#/read');

        $recipeCount = $recipeRepository->count([]);

        return $responseService->itemActionSuccess('# Cooking 101

## Cooking Basics

Your house comes with everything you need: stove, pots and pans, water, and basic spices.

To get started:
1. Click "Cook or Combine"
2. Select the item or items you want to use
3. Click "Cook or Combine" again!

There are ' . $recipeCount . ' recipes to discover, so feel free to experiment! Nothing bad will happen if you try to prepare a "wrong" recipe; the items will simply not be used.

## Recipes

Here are a few simple recipes to get you started:

#### Matzah Bread

Prepare Wheat Flour alone (or with Oil, if you have any).

If you have raw Wheat, it can be prepared alone to get Wheat Flour!

(No need to worry about how many cups of flour! If a recipe needs Wheat Flour, it only ever needs one!)

#### Fruit Juice (and Pectin)

Prepare either a Red, Orange, Carrot, or other fruit or veggie high in Pectin, to create juice (and get a bit of Pectin on the side!)

#### Candy

Combine Sugar and most fruits to make hard candy.

Sugar can be extracted from Sweet Beets by preparing Sweet Beet on its own.

#### Butter

Prepare Milk on its own.

## Learn More

There are other cook books to find, but some item descriptions contain recipes, as well, and again: don\'t be afraid to experiment a little! 
');
    }
}
