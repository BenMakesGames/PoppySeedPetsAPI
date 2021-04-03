<?php
namespace App\Controller\Item\Book;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Service\ResponseService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/friedBook")
 */
class FriedBookController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/read", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(Inventory $inventory, ResponseService $responseService)
    {
        $this->validateInventory($inventory, 'friedBook/#/read');

        return $responseService->itemActionSuccess('# Fried

## Table of Contents

#### Basic Ingredients

* Oil
* Plain Bread

#### Recipes

* Battered, Fried Fish
* Chili Calamari
* Deep-fried Toad Legs
* Fried Donut
* Fried Egg
* Fried Tomato
* Hash Brown
* Laufabrauð
* Onion Rings

## Basic Ingredients

#### Oil

It should not surprise you to learn that frying involves _Oil_. And I\'m not talking about the black stuff found all over the Oil Zone! (That oil\'s poisonous to hedgehogs and humans alike! DO NOT EAT!)

To make edible Oil (not that you should eat it straight - gross), squeeze a Sunflower, and its seeds. That\'s all you need!

Need Sunflowers? They\'re available from the Trader on Sundays.

#### Plain Bread

In addition to Oil, you _may_ need bread (for crumbs!)

If you don\'t have any bread on-hand, you can make some:

* Yeast
* Butter
* Creamy Milk
* Sugar
* Wheat Flour

## Recipes

Alright! You\'ve got your Oil... you\'ve got your Plain Bread...

Let\'s fry some stuff!

#### Battered, Fried Fish

* Oil!
* Fish
* Wheat Flour
* Baking Soda
* Milk
* Egg (optional)

#### Chili Calamari

* Oil!
* Spicy Peps
* Onions
* Tentacle

#### Deep-fried Toad Legs

* Oil!
* Toad Legs
* Egg (or milk)
* Wheat Flour (no bread crumbs here!)

If you\'re feelin\' spicy, throw in some Spicy Peps to match!

#### Fried Donut

No baked donuts for us! We\'re goin\' FRIED!

You won\'t need breadcrumbs for this one, either; but you _will_ need:

* Oil!
* Yeast
* Butter
* Creamy Milk
* Egg
* Sugar
* Wheat Flour

#### Fried Egg

Simple, but delicious:

* Oil! (or Butter, but, c\'mon!)
* Egg

#### Fried Tomato

Finally the bread crumbs show themselves!

* Oil!
* Tomato
* Egg
* Wheat Flour
* Plain Bread (for that extra-crunchy fried crumb goodness!)

#### Hash Brown

* Oil! (or Butter, if you must)
* Potato
* Hash Table

#### Laufabrauð

After rolling the dough into thin circles, cut a pretty design into it. Then carefully place it into the hot oil to fry it!

Yep: a fried bread!

* Oil!
* Baking Powder
* Butter
* Creamy Milk
* Sugar
* Wheat Flour

#### Onion Rings

* Oil!
* Onion
* Wheat Flour
* Baking Soda (for that fast-food feel!)
');
    }
}
