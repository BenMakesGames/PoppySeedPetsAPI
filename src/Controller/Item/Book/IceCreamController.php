<?php
namespace App\Controller\Item\Book;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Service\CookingService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/iceCream")
 */
class IceCreamController extends AbstractController
{
    /**
     * @Route("/{inventory}/upload", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'iceCream/#/upload');

        $message = $cookingService->showRecipeNamesToCookingBuddy($user, [
            'Blackberry Ice Cream',
            'Chocolate Ice Cream',
            'Honeydont Ice Cream',
            'Naner Ice Cream',
            'Blackberry Ice Cream',

            'Blackberry Ice Cream Sammy',
            'Chocolate Ice Cream Sammy',
            'Honeydont Ice Cream Sammy',
            'Naner Ice Cream Sammies',
            'Blackberry Ice Cream Sammy',
        ]);

        return $responseService->itemActionSuccess($message);
    }

    /**
     * @Route("/{inventory}/read", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(Inventory $inventory, ResponseService $responseService)
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'iceCream/#/read');

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
