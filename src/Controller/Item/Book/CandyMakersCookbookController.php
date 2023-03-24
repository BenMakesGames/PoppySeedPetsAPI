<?php
namespace App\Controller\Item\Book;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/candyMakerCookbook")
 */
class CandyMakersCookbookController extends AbstractController
{
    /**
     * @Route("/{inventory}/read", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(Inventory $inventory, ResponseService $responseService)
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'candyMakerCookbook/#/read');

        return $responseService->itemActionSuccess('# Candy Maker\'s Cookbook

## Table of Contents

#### Basic Ingredients

* Agar-agar
* Corn Syrup
* Pectin
* Sugar

#### Recipes

* Candied Lotus Petals
* Chocolates
* Everybeans
* Hard Fruit Candies
* Honeycomb
* Konpeitō
* Mixed Nut Brittle
* Rock Candy
* Soft Fruit Gummies

## Basic Ingredients

#### Agar-agar

Boiling Algae breaks up the cell walls. As it all cools, the fibrous chains we call Agar-agar are formed!

Let it dry, grind it up, and you\'ve got yourself some Agar-agar powder!

<small>(For those of you trying these recipes in real life, it\'s probably actually more complicated than that... sorry...)</small>

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

1. Slice beets into 1/4" thick slices, and rinse.
2. Microwave until steaming.
3. Shred in food processor, then transfer to a large pot.
4. Cover with water, and simmer for about 45 minutes.
5. Strain liquid, and place on a shallow baking dish.
6. Bake at 250 until a sugar sludge remains.
7. Take out of oven, transfer to cool baking sheet, and allow remaining water to evaporate (be prepared to wait a day).

Quick method:

1. Click "Cook or Combine"
2. Select one Sweet Beet
3. Click "Cook or Combine" again
4. Dries instantly, because reasons; don\'t worry about it

## Recipes

#### Candied Lotus Petals

A floral treat for your pets!

Combine:
* Egg
* Sugar
* Lotus Flower

#### Chocolates

Combine:
* Cocoa Beans (*not* powder!)
* Sugar
* Optionally, add Orange, or Spicy Peps.

#### Everybeans

Somehow, all you have to do is dye and sweeten some Beans, and they\'ll gain unexpected flavors!

Even mundane Beans are a little magic, it seems...

Combine:
* Beans
* Sugar
* Any dye (yellow, green, magenta...)

#### Hard Fruit Candies

Combine the following:
* Fruit (berries, melon, orange, etc)
* Sugar

#### Honeycomb

The candy, of course. You will need non-candy Honeycomb to make it, though!

* Sugar
* Corn Syrup
* Honeycomb
* Baking Soda

Optionally cover in chocolate for a delicious & crunchy candy bar!

#### Konpeitō

These colorful candies are painstaking to make by hand! But the ingredients are simple:
* Sugar
* A variety of dyes (at least three!)

#### Mixed Nut Brittle

* Sugar
* Corn Syrup
* Baking Soda
* Butter
* Mixed Nuts

#### Rock Candy

Dangle a String in a solution of Sugar-water.

It\'s the easiest thing!

#### Soft Fruit Gummies

Combine the following:
* Fruit (berries, melon, orange, etc)
* Sugar
* Corn Syrup, or Agar-agar
');
    }
}
