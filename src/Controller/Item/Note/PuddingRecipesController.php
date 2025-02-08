<?php
declare(strict_types=1);

namespace App\Controller\Item\Note;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Service\CookingService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/note/puddin")]
class PuddingRecipesController extends AbstractController
{
    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'note/puddin/#/upload');

        $message = $cookingService->showRecipeNamesToCookingBuddy($user, [
            'Naner Puddin\'',
            'Rice Puddin\'',
            'Mango Pudding',
        ]);

        return $responseService->itemActionSuccess($message);
    }

    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function readPuddingRecipes(Inventory $inventory, ResponseService $responseService)
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'note/puddin/#/read');

        return $responseService->itemActionSuccess('**Naner Puddin\'**

* Naner
* Creamy Milk
* Sugar
* Egg
* Wheat Flour

Sub the naners with mangoes for a mango puddin\'!

**Rice Puddin\'**

* Rice
* Creamy Milk
* Coconut Milk
* Sugar
');
    }
}
