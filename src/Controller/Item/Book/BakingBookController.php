<?php
namespace App\Controller\Item\Book;

use App\Controller\Item\PoppySeedPetsItemController;
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

* Brownie
* Chocolate Chip Cookies
* Donut (Cake)
* Donut (Yeast)
* Flour Tortilla
* Graham Crackers
* Melon Pan
* Meringues
* Muffins
* Naner Bread
* Pancakes
* Plain Bread
* Shortbread Cookies
* Sugar Cookies (World\'s Best!)
* Thicc Mints

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

#### Brownie

* Baking Powder
* Butter
* Cocoa Powder
* Egg
* Sugar
* Wheat Flour

#### Chocolate Chip Cookies

* Baking Soda
* Butter
* Egg
* Sugar
* Wheat Flour
* Chocolate Bar

Makes 12!

#### Donut (Cake)

This donut isn\'t fried, so it\'s easier to pretend that it\'s healthy.

* Baking Powder
* Butter
* Creamy Milk
* Egg
* Sugar
* Wheat Flour

#### Donut (Yeast)

* Yeast
* Butter
* Creamy Milk
* Egg
* Oil (for fryin\'!)
* Sugar
* Wheat Flour

Optionally, glaze with frosting (Sugar + the tiniest amount of water you can get away with).

#### Flour Tortilla

A versatile bread! (If you make quesadillas, don\'t forget to Butter, or Oil, the pan, as you would with a Grilled Cheese!)

* Baking Powder (or Baking Soda + Cream of Tartar)
* Butter
* Wheat Flour

#### Graham Crackers

* Wheat Flour
* Baking Soda
* Butter (or Oil)
* Sugar

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

#### Muffins

* Wheat Flour
* Baking Powder
* Butter
* Sugar
* Egg
* Plain Yogurt
* Optionally: Berries, Red, pieces of Chocolate Bar

In some cases, using Creamy Milk or a flavored Yogurt, instead of Plain Yogurt, may work! 

#### Naner Bread

* Baking Soda
* Butter
* Egg
* Naner
* Sugar
* Wheat Flour

Bored of Naner Bread? Replacing the Naner with a Smallish Pumpkin and make some Pumpkin Bread!

#### Pancakes

* Baking Soda
* Butter
* Creamy Milk
* Egg
* Sugar
* Wheat Flour

The milk provides enough acidity for this recipe, so Cream of Tartar (or Baking Powder) is not required; Baking Soda on its own will do!

#### Plain Bread

* Yeast
* Butter
* Creamy Milk
* Sugar
* Wheat Flour

Or, for a simpler recipe (has a smaller yield):

* Wheat Flour
* Yeast

#### Shortbread Cookies

* Butter
* Sugar
* Wheat Flour

(Don\'t forget the Sugar, or you\'ll just end up with Pie Crust!)  

#### Sugar Cookies (World\'s Best!)

* Baking Powder
* Butter
* Egg
* Sugar
* Wheat Flour

If you have Baking Soda and Cream of Tartar lying around, but no Baking Powder, feel free to substitute the Baking Powder here with Baking Soda and Cream of Tartar directly.

#### Thicc Mints

* Wheat Flour
* Baking Soda
* Sugar
* Cocoa Powder
* Butter, or Oil
* Mint
');
    }
}
