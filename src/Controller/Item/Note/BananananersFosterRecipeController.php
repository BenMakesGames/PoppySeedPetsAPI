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

#[Route("/item/note/bananananersFoster")]
class BananananersFosterRecipeController extends AbstractController
{
    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'note/bananananersFoster/#/upload');

        $message = $cookingService->showRecipeNamesToCookingBuddy($user, [
            'Bananananers Foster',
        ]);

        return $responseService->itemActionSuccess($message);
    }

    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function readBananananersFosterRecipe(Inventory $inventory, ResponseService $responseService)
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'note/bananananersFoster/#/read');

        return $responseService->itemActionSuccess('Start with Naner Ice Cream.

Make "sauce" made from Sugar, Butter, Naner, and rum, and pour over the ice cream.

Serve warm (and quickly!)');
    }
}
