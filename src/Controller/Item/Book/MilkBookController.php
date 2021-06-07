<?php
namespace App\Controller\Item\Book;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Service\ResponseService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/milkBook")
 */
class MilkBookController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/read", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(Inventory $inventory, ResponseService $responseService)
    {
        $this->validateInventory($inventory, 'milkBook/#/read');

        return $responseService->itemActionSuccess('Have you ever found yourself in a situation where you just have way too much dang milk? Maybe your goat\'s just been overflowing with the stuff; maybe you have a reasonable amount, but just don\'t like the taste of it; maybe your friends\' goat was overflowing with the stuff, so they brought you some as a "gift". (I\'m on to you, Greg and Rachel!)

Whatever the circumstances that lead to your overabundance of milk, fret not! There are a lot of good uses for the stuff!

## Table o\' Contents

* Chocolate Cream Pie
* Chocomilk
* Creamed Corn
* Eggnog
* Hakuna Frittata
* Lassi
* Mahalabia
* Melonpan (Melonbun)
* Naner Puddin\'
* Pancakes
* Plain Bread
* Teas
* Yogurt

## Recipes

#### Chocolate Cream Pie

* Creamy Milk!
* Sugar
* Egg
* Wheat Flour
* Butter
* Cocoa Powder
* Pie Crust

#### Chocomilk

* Creamy Milk!
* Sugar
* Cocoa Powder

#### Creamed Corn

* Creamy Milk!
* Corn

#### Eggnog

* Creamy Milk!
* Egg
* Sugar

#### Hakuna Frittata

* Creamy Milk!
* Onion
* Egg
* Chanterelle

#### Lassi

* Creamy Milk!
* Plain Yogurt
* Fruit (Mango, Blackberry, or Blueberry)

Or, if you have Blackberry or Blueberry Yogurt, just add... Creamy Milk!

#### Mahalabia

A simple, sweet pudding! Try it with cardamom, clove, or Nutmeg!

* Creamy Milk!
* Rice Flour
* Sugar 

#### Melonpan (or is it Melonbun?)

* Creamy Milk!
* Sugar
* Egg
* Wheat Flour
* Butter
* Baking Powder
* Yeast

#### Naner Puddin\'

* Creamy Milk!
* Naner
* Sugar
* Egg
* Wheat Flour

#### Pancakes

* Creamy Milk!
* Sugar
* Egg
* Wheat Flour
* Baking Soda
* Butter (Don\'t have any? It\'s made from nothing more than Creamy Milk!)

Optionally, add fruit, such as berries, Reds, or Naners.

#### Plain Bread

* Creamy Milk!
* Sugar
* Wheat Flour
* Butter
* Yeast

#### Teas

Add Creamy Milk to any tea for a richer, creamier tea!

#### Yogurt Begets Yogurt

* Creamy Milk!
* Plain Yogurt
* Aging Powder
');
    }
}
