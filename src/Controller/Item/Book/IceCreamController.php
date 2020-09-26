<?php
namespace App\Controller\Item\Book;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Service\ResponseService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/iceCream")
 */
class IceCreamController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/read", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(Inventory $inventory, ResponseService $responseService)
    {
        $this->validateInventory($inventory, 'iceCream/#/read');

        return $responseService->itemActionSuccess('# We All Scream

#### Simple Ice Cream

* Sugar
* Creamy Milk
* Egg
* Something to act as the flavor! (Fruits or Cocoa Powder are solid choices!)

#### Ice Cream Sammies

Homemade ice cream sammies are fun to make: put some ice cream on some cookies, and you\'re good to go! Here are my favorite combinations:

* Blackberry Ice Cream + World\'s Second-best Sugar Cookie
* Chocolate Ice Cream + World\'s Best Sugar Cookie
* Honeydont Ice Cream + Browser Cookie
* Naner Ice Cream + Mini Chocolate Chip Cookies
* Blueberry Ice Cream + Shortbread Cookies
');
    }
}
