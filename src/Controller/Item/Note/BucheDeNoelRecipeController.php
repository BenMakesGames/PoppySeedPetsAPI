<?php
namespace App\Controller\Item\Note;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Service\CookingService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/note/bucheDeNoel")]
class BucheDeNoelRecipeController extends AbstractController
{
    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'note/bucheDeNoel/#/upload');

        $message = $cookingService->showRecipeNamesToCookingBuddy($user, [
            'Bûche De Noël',
        ]);

        return $responseService->itemActionSuccess($message);
    }

    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(Inventory $inventory, ResponseService $responseService)
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'note/bucheDeNoel/#/read');

        return $responseService->itemActionSuccess('**Bûche De Noël**
* Egg
* Wheat Flour
* Cocoa Powder
* Sugar
* Pectin
* Goodberries');
    }
}
