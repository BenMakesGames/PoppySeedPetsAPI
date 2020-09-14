<?php
namespace App\Controller\Item\Book;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Service\ResponseService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/riceBook")
 */
class RiceBookController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/read", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(Inventory $inventory, ResponseService $responseService)
    {
        $this->validateInventory($inventory, 'riceBook/#/read');

        return $responseService->itemActionSuccess('# Of Rice

## Table of Contents

* Nigiri
* Onigiri
* Rice Flour
* Rice Vinegar
* Simple Sushi
* Tapsilog (with Fish)
* Tomato "Sushi"
* Yaki Onigiri
* Zongzi

## Recipes

#### Nigiri
* Rice
* Fish

#### Onigiri
A rice ball with a seaweed wrap; hardly anything could be simpler!

Try adding a filling, such as Fish, or Melowatern.

* Rice
* Seaweed

#### Rice Vinegar
An important ingredient when making sushi!

Be sure to include the Aging Powder, or you\'ll just end up with plain ol\' Rice Flour!

* Rice
* Aging Powder

#### Simple Sushi
* Rice
* Sugar
* Vinegar
* Seaweed
* Fish

#### Tapsilog (with Fish)
Fried rice, well-cooked fish, and a fried egg. A complete breakfast!

* Onion
* Rice
* Oil
* Fish
* Fried Egg

#### Tomato "Sushi"
* Rice
* Tomato
* Oil
* Soy Sauce

#### Yaki Onigiri
* Onigiri
* Charcoal
* Soy Sauce

#### Zongzi
* Rice
* Fish
* Chanterelle
* Beans
* Mixed Nuts
* Really Big Leaf
* String (for tying it all together!)
');
    }
}
