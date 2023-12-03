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

/**
 * @Route("/item/note/stroganoff")
 */
class StroganoffRecipeController extends AbstractController
{
    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'note/stroganoff/#/upload');

        $message = $cookingService->showRecipeNamesToCookingBuddy($user, [
            'Fish Stroganoff (A)',
            'Fish Stroganoff (B)',
            'Fish Stroganoff (C)',
            'Fish Stroganoff (D)',
            'Fish Stroganoff (E)',
            'Fish Stroganoff (F)',
        ]);

        return $responseService->itemActionSuccess($message);
    }

    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function readStroganoffRecipe(Inventory $inventory, ResponseService $responseService)
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'note/stroganoff/#/read');

        return $responseService->itemActionSuccess('* mushrooms, onions, oil (or butter)
* fish
* sour cream, flour (any - thickener)
* noodles');
    }
}
