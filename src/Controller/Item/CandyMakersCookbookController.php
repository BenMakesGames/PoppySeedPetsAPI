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
     * @Route("/{inventory}/read", methods={"POST"})
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

* Hand Candies
* Soft Gummies

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

#### Hard Candies

Combine the following:
* Fruit (berries, melon, orange, etc)
* Sugar

If you prefer to always think in terms of "2 Sugar", you can also double the small fruit recipes.

#### Soft Gummies

Combine the following:
* Fruit (berries, melon, orange, etc)
* Sugar
* Corn Syrup');
    }
}