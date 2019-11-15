<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Service\ResponseService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/noodsBook")
 */
class NoodsBookController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/read", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(Inventory $inventory, ResponseService $responseService)
    {
        $this->validateInventory($inventory, 'noodsBook/#/read');

        return $responseService->itemActionSuccess('## Noods

* Wheat Flour
* Egg

OR

* Rice Flour
* Baking Soda (optional)

(There\'s a picture of what looks like some kind of squirming mass, but someone\'s scribbled over most of it with permanent marker.)

## Mackin Cheese

* Noods
* Cheese
* Creamy Milk
* Butter
* Flour

(There\'s a picture of what looks like someone\'s glistening knee, or elbow, poking from just off-frame, and there\'s steam everywhere... what kind of picture is this?) 

## Stroganoff

* Noods
* Fish
* Mushrooms
* Onions
* Oil (or Butter)
* Sour Cream
* Flour (any; acts as a thickener)

(There\'s a blurry picture of some kind of white goop, maybe? It\'s hard to tell.)

## Super-simple Spaghet

* Noods
* Tomato

(There\'s a picture of a shallow pool of some pale liquid that\'s clearly accumulated from dripping off of from something directly above it, but what that something is has been cut out of the book!)
');
    }
}