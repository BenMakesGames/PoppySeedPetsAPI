<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Service\ResponseService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/bakingBook")
 */
class BakingBookController extends PoppySeedPetsItemController
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

* Donut (Cake)
* Donut (Yeast)
* Flour Tortilla
* Melon Pan
* Meringues
* Naner Bread
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

#### Donut (Cake)

This donut isn\'t fried, so it\'s easier to pretend that it\'s healthy.

* Baking Powder
* Butter
* Creamy Milk
* Egg
* Sugar
* Wheat Flour

#### Donut (Yeast)

* Baker\'s Yeast
* Butter
* Creamy Milk
* Egg
* Oil (for fryin\'!)
* Sugar
* Wheat Flour

#### Flour Tortilla

A versatile bread! (If you make quesadillas, don\'t forget to Butter, or Oil, the pan, as you would with a Grilled Cheese!)

* Baking Powder (or Baking Soda + Cream of Tartar)
* Butter
* Wheat Flour

#### Melon Pan

A simple bread with a cracked, cookie crust. It\'s melon-like appearance gives it its name.

* Baking Powder
* Butter
* Egg
* Milk
* Sugar
* Wheat Flour
* Yeast

#### Meringues

* Egg
* Cream of Tartar
* Sugar

Optionally, add Cocoa Powder for Chocolate Meringues!

To make a Bizet Cake, use citrus instead of Cream of Tartar, and add Butter (for the buttercream!)

#### Naner Bread

* Baking Soda
* Butter
* Egg
* Naner
* Sugar
* Wheat Flour

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