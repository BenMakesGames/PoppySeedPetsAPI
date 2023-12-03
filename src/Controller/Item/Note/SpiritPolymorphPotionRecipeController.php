<?php
namespace App\Controller\Item\Note;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Service\CookingService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/note/spiritPolymorphPotion")
 */
class SpiritPolymorphPotionRecipeController extends AbstractController
{
    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'note/spiritPolymorphPotion/#/upload');

        $message = $cookingService->showRecipeNamesToCookingBuddy($user, [
            'Spirit Polymorph Potion (A)',
            'Spirit Polymorph Potion (B)',
        ]);

        return $responseService->itemActionSuccess($message);
    }

    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function readSpiritPolymorphPotion(Inventory $inventory, ResponseService $responseService)
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'note/spiritPolymorphPotion/#/read');

        return $responseService->itemActionSuccess('* Striped Microcline
* Witch-hazel
* Carrot

Warning: if you don\'t have a Carrot handy, you can also use a Large Radish - just make sure not to use Spicy Peps, or you\'ll create a substance that\'s very toxic to spirits! 
');
    }
}
