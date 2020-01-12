<?php
namespace App\Controller\Item\Book;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Service\ResponseService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/fishBook")
 */
class FishBookController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/read", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(Inventory $inventory, ResponseService $responseService)
    {
        $this->validateInventory($inventory, 'fishBook/#/read');

        return $responseService->itemActionSuccess('# Fish Book

## Table of Contents

#### Crafts

* Fishing Rod
* Painted Fishing Rod
* Plastic Fishing Rod

#### Recipes

* Baked Fish Fingers
* Basic Fish Taco
* Fermented Fish
* Gefilte Fish
* Grilled Fish
* Nigiri
* Orange Fish
* Sushi

## Crafts

Your pets can craft these things, if the materials are available.

For some reason, only pets can make these?

Life as a human truly isn\'t fair.

#### Fishing Rod

1. Attach a bit of String to a Crooked Stick
1. Somehow, that\'s all it takes! (Where does the hook come from?)

#### Painted Fishing Rod

1. Prepare a palette of Yellow and Green Dye
1. Alternate painting splotchy patches (technical term!) of green and yellow on the rod

#### Plastic Fishing Rod

1. Get a 3D Printer
1. Print it!

## Recipes

Your can cook these things, if the ingredients are available.

For some reason, only humans can make these?

Life as a pet truly isn\'t fair.

#### Baked Fish Fingers

1. Cut up some Fish into finger sizes
1. Brush Fish with Egg
1. Roll Fish in Wheat Flour and crumbs from a Slice of Bread
1. Bake!

#### Basic Fish Taco

* Tortilla
* Fish
* Salsa

#### Fermented Fish

Just age some Fish with Aging Powder!

An acquired taste!

#### Gefilte Fish

* Carrot
* Onion
* Egg
* Fish
* Matzah Bread
* Celery (optional)

#### Grilled Fish

Just grill some Fish with Charcoal!

#### Nigiri

* Rice
* Fish

(Psst, hey! I know this is supposed to be a book about Fish, BUT: if you marinade a slice of Tomato in Soy Sauce and Oil, and use that instead of Fish, you could make yourself a nice Tomato "Sushi"! Just sayin\'! It\'s an option!)

#### Orange Fish

* Orange
* Fish
* Sugar (not optional!)

#### Sushi

1. Lay out a sheet of Seaweed
1. Place a bed of sushi rice on the seaweed (Rice, Sugar, Vinegar)
1. Place pieces of Fish on the sushi rice, in a line about 1-2 inches away from one edge 
1. Roll!
');
    }
}