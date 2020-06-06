<?php
namespace App\Controller\Item\Book;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Service\ResponseService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/cooking101")
 */
class Cooking101Controller extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/read", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(Inventory $inventory, ResponseService $responseService)
    {
        $this->validateInventory($inventory, 'cooking101/#/read');

        return $responseService->itemActionSuccess('# Cooking 101

### Cooking Basics

Your house comes with everything you need: stove, pots and pans, water, and basic spices.

To get started:
1. Click "Cook & Combine"
2. Select the item or items you want to use
3. Click "Cook & Combine" again!

### Recipes

#### Matzah Bread

Prepare Wheat Flour alone (or with Oil, if you have any).

If you have raw Wheat, it can be prepared alone to get Wheat Flour!

(No need to worry about how many cups of flour! If a recipe needs Wheat Flour, it only ever needs one!)

#### Fruit Juice (and Pectin)

Prepare a Red, Orange, Carrot (or other fruit or veggie high in Pectin) to create juice, and get a bit of Pectin on the side!

#### Candy

Combine Sugar and most fruits to make hard candy.

Sugar can be extracted from Sweet Beets by preparing Sweet Beet on its own.

#### Butter

Prepare Milk on its own.

### Learn More

Some item descriptions contain recipes. You may also find other books that contain more recipes.

Feel free to experiment! Nothing bad will happen if you try to prepare a "wrong" recipe; the items will simply not be used.
');
    }
}
