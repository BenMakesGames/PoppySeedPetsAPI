<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Service\ResponseService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/bakingBook")
 */
class BakingBookController extends PsyPetsItemController
{
    /**
     * @Route("/{inventory}/read", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(Inventory $inventory, ResponseService $responseService)
    {
        $this->validateInventory($inventory, 'bakingBook/#/read');

        return $responseService->itemActionSuccess('# Big Book of Baking

## Table of Contents

#### Basic Ingredients

* Baking Powder
* Baking Soda
* Butter
* Cream of Tartar
* Wheat Flour

#### Recipes

* Flour Tortilla
* Melon Pan
* Meringues
* Pancakes
* Plain Bread
* Sugar Cookies (World\'s Best!)

## Basic Ingredients

#### Baking Powder

Combine an equal measure of Baking Soda and Cream of Tartar.

#### Baking Soda

Dissolve a block of Limestone. You will get about 10 batches of Baking Soda.

#### Butter

Churn a bottle of Creamy Milk to create Butter.

#### Cream of Tartar

Can be found as a byproduct of wine-making. (As an example, you can age some Blueberries or Blackberries to make wine, and some Cream of Tartar.) 

#### Wheat Flour

Simply grind some Wheat into a fine powder.

## Recipes

#### Flour Tortilla

A versatile bread! (If you make quesadillas, don\'t forget to Butter, or Oil, the pan, as you would with a Grilled Cheese!)

* Wheat Flour
* Baking Powder (or Baking Soda + Cream of Tartar)
* Butter

#### Melon Pan

A simple bread with a cracked, cookie crust. It\'s melon-like appearance gives it its name.

* Wheat Flour
* Sugar
* Yeast
* Egg
* Milk
* Butter
* Baking Powder

#### Meringues

* Sugar
* Egg
* Cream of Tartar

Optionally, add Cocoa Powder for Chocolate Meringues!

#### Pancakes

* Baking Soda
* Butter
* Creamy Milk
* Egg
* Sugar
* Wheat Flour

The milk provides enough acidity for this recipe, so Cream of Tartar (or Baking Powder) is not required; Baking Soda on its own will do!

#### Plain Bread

* Baker\'s Yeast
* Butter
* Creamy Milk
* Sugar
* Wheat Flour

#### Sugar Cookies (World\'s Best!)

* Baking Powder
* Butter
* Egg
* Sugar
* Wheat Flour

If you have Baking Soda and Cream of Tartar lying around, but no Baking Powder, feel free to substitute the Baking Powder here with Baking Soda and Cream of Tartar directly.
');
    }
}