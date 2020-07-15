<?php
namespace App\Controller\Item\Book;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Service\ResponseService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/artOfTofu")
 */
class ArtOfTofuController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/read", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(Inventory $inventory, ResponseService $responseService)
    {
        $this->validateInventory($inventory, 'artOfTofu/#/read');

        return $responseService->itemActionSuccess('# The Art of Tofu

#### Making Tofu

1. Press Beans into Bean Milk
2. Combine Bean Milk with Gypsum

#### "Chicken" Noodle Soup

* Tofu
* Mirepoix
* Noodles

#### Pan-seared Tofu

* Tofu
* Oil (or Butter)
* Soy Sauce
');
    }
}
