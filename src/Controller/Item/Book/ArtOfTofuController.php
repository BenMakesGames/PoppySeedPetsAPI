<?php
declare(strict_types=1);

namespace App\Controller\Item\Book;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Service\CookingService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/artOfTofu")]
class ArtOfTofuController extends AbstractController
{
    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'artOfTofu/#/upload');

        $message = $cookingService->showRecipeNamesToCookingBuddy($user, [
            'Tofu',
            '"Chicken" Noodle Soup (with Tofu)',
            'Miso Soup',
            'Pan-fried Tofu (using Butter)',
            'Pan-fried Tofu (using Oil)',
            'Tofu Fried Rice (A)',
            'Tofu Fried Rice (B)',
        ]);

        return $responseService->itemActionSuccess($message);
    }

    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(Inventory $inventory, ResponseService $responseService)
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'artOfTofu/#/read');

        return $responseService->itemActionSuccess('# The Art of Tofu

#### Making Tofu

1. Press Beans into Bean Milk
2. Combine Bean Milk with Gypsum

#### "Chicken" Noodle Soup

* Tofu
* Mirepoix
* Noodles

#### Miso Soup

* Tofu
* Dashi

#### Pan-seared Tofu

* Tofu
* Oil (or Butter)
* Soy Sauce

#### Tofu Fried Rice

* Tofu
* Rice
* Oil
* Soy Sauce
* Onion (or Mirepoix!)
* Egg
');
    }
}
