<?php
namespace App\Controller\Item\Book;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Service\CookingService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @Route("/item/milkBook")
 */
class MilkBookController extends AbstractController
{
    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'milkBook/#/upload');

        $message = $cookingService->showRecipeNamesToCookingBuddy($user, [
            'Chocolate Cream Pie',
            'Chocomilk',
            'Creamed Corn',
            'Eggnog',
            'Blackberry Eton Mess',
            'Blueberry Eton Mess',
            'Goodberry Eton Mess',
            'Hakuna Frittata',
            'Blackberry Lassi (from scratch)',
            'Blackberry Lassi',
            'Blueberry Lassi (from scratch)',
            'Blueberry Lassi',
            'Mango Lassi (from scratch)',
            'Mango Lassi',
            'Mahalabia',
            'Melonpanbun',
            'Naner Puddin\'',
            'Pancakes',
            'Berry Pancakes (Blue)',
            'Red Pancakes',
            'Naner Pancakes',
            'Berry Pancakes (Black)',
            'Loaf of Plain Bread',
            'Qatayef',
            'Plain Yogurt',
            'Tea with Mammal Extract',
            'Tea with Mammal Extract (B)',
            'Sweet Tea with Mammal Extract',
            'Coffee Bean Tea with Mammal Extract',
            'Coffee Bean Tea with Mammal Extract (B)',
            'Sweet Coffee Bean Tea with Mammal Extract',
            'Vichyssoise (with Butter)',
            'Vichyssoise (with Milk)',
            'Vichyssoise (with both)',
        ]);

        return $responseService->itemActionSuccess($message);
    }

    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(Inventory $inventory, ResponseService $responseService)
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'milkBook/#/read');

        return $responseService->itemActionSuccess('Have you ever found yourself in a situation where you just have way too much dang milk? Maybe your goat\'s just been overflowing with the stuff; maybe you have a reasonable amount, but just don\'t like the taste of it; maybe your friends\' goat was overflowing with the stuff, so they brought you some as a "gift". (I\'m on to you, Greg and Rachel!)

Whatever the circumstances that lead to your overabundance of milk, fret not! There are a lot of good uses for the stuff!

## Table o\' Contents

* Chocolate Cream Pie
* Chocomilk
* Creamed Corn
* Eggnog
* Eton Mess
* Hakuna Frittata
* Lassi
* Mahalabia
* Melonpan (Melonbun)
* Naner Puddin\'
* Pancakes
* Plain Bread
* Qatayef
* Teas
* Vichyssoise
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

#### Eton Mess

* Creamy Milk!
* Sugar
* Meringue
* Berries

A layered dessert: chunks of meringue, a layer of whipped cream, and berries on top.  

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

#### Qatayef

A tasty Arabic dessert filled with nuts and cream! (It\'s a little involved, but worth the effort!)

* Creamy Milk!
* Mixed Nuts
* Sugar
* Yeast
* Baking Powder
* Wheat Flour
* Wheat

#### Teas

Add Creamy Milk to any tea for a richer, creamier tea!

#### Vichyssoise

* Creamy Milk and/or Butter
* Onion
* Potato

Served cold - that\'s how you know it\'s fancy! Well: that and the French name...

#### Yogurt Begets Yogurt

* Creamy Milk!
* Plain Yogurt
* Aging Powder
');
    }
}
