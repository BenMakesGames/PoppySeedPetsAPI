<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Service\ResponseService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/candyMakerCookbook")
 */
class CandyMakersCookbookController extends PsyPetsItemController
{
    /**
     * @Route("/{inventory}/read", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(Inventory $inventory, ResponseService $responseService)
    {
        $this->validateInventory($inventory, 'candyMakerCookbook/#/read');

        return $responseService->itemActionSuccess('# Candy Maker\'s Cookbook

## Table of Contents

#### Basic Ingredients

* Corn Syrup
* Pectin
* Sugar

#### Recipes

* Fruit Candies
* Fruit Gummies

## Basic Ingredients

#### Corn Syrup

Combine Sugar, Corn, and Cream of Tartar in a saucepan and bring to a simmer; strain out solids, then continue simmering until thick.

#### Pectin

Many fruits (and even some vegetables) contain Pectin. Fruits with piths and rinds (such as Oranges and Pamplemousses) are especially rich with the stuff.

Cook any of the following, on its own, to receive Pectin:
* Red
* Orange
* Carrot
* Pamplemousse

#### Sugar

Sweet Beets are _brimming_ with Sugar, just waiting to be extracted!

1. Slice beats into 1/4" thick slices, and rinse.
2. Microwave until steaming.
3. Shred in food processor, then transfer to a large pot.
4. Cover with water, and simmer for about 45 minutes.
5. Strain liquid, and place on a shallow baking dish.
6. Bake at 250 until a sugar sludge remains.
7. Take out of oven, transfer to cool baking sheet, and allow remaining water evaporate (be prepared to wait a day).

Quick method:

1. Click "Cook & Combine"
2. Select one Sweet Beet
3. Click "Cook & Combine" again
4. Dries instantly, because reasons; don\'t worry about it

## Recipes

#### Fruit Candies

Combine the following:
* A small fruit (ex: Blueberries, or Blackberries)
* Sugar

Or:
* A larger fruit (ex: Red, Orange, or Pamplemousse)
* 2 Sugar

If you prefer to always think in terms of "2 Sugar", you can also double the small fruit recipes.

#### Fruit Gummies

Combine the following:
* A small fruit (ex: Blueberries, or Blackberries)
* Sugar
* Corn Syrup

Or:
* A large fruit (ex: Red, Orange, or Pamplemousse)
* 2 Sugar
* Corn Syrup

When making small fruit gummies, you can be a little more efficient with your Corn Syrup, as follows:
* 2 of same small fruit
* 2 Sugar
* Corn Syrup
');
    }
}