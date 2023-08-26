<?php

namespace App\Controller\Item\Book;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Repository\InventoryRepository;
use App\Service\CookingService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/yellowyLime")
 */
class YellowyLimeController extends AbstractController
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

        ItemControllerHelpers::validateInventory($user, $inventory, 'yellowyLime/#/upload');

        $message = $cookingService->showRecipeNamesToCookingBuddy($user, [
            'Yellowy Key-y Lime Pie',
            'Essence d\'Assortiment (from Blackberry Wine)',
            'Essence d\'Assortiment (from Blueberry Wine)',
            'Essence d\'Assortiment (from Dandelion Wine)',
            'Essence d\'Assortiment (from Fig Wine)',
            'Essence d\'Assortiment (from Red Wine)',
        ]);

        return $responseService->itemActionSuccess($message);
    }

    /**
     * @Route("/{inventory}/read", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(
        Inventory $inventory, ResponseService $responseService,
        InventoryRepository $inventoryRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'yellowyLime/#/read');

        $magnifyingGlass = $inventoryRepository->findAnyOneFromItemGroup($user, 'Magnifying Glass', [
            LocationEnum::HOME,
            LocationEnum::BASEMENT,
            LocationEnum::MANTLE,
            LocationEnum::WARDROBE,
        ]);

        if(!$magnifyingGlass)
        {
            throw new PSPInvalidOperationException('Goodness! It\'s so small! You\'ll need a magnifying glass of some kind...');
        }

        return $responseService->itemActionSuccess(<<<EOBOOK
<em>(You know how on the back of a bag of chocolate chips, there's a recipe for Chocolate Chip Cookies? This is like that, but on the sticker on this Yellowy Lime. Oh, and also the recipe isn't for Chocolate Chip Cookies, because that would be weird. Oh, and ALSO-also, the print is just, like, absolutely and absurdly tiny. Thankfully your {$magnifyingGlass->getFullItemName()} is a good magnifying glass, and lets you make it all out.)</em>

**Yellowy Key-y Lime Pie**
* Yellowy Lime
* Egg
* Creamy Milk
* Sugar
* Butter
* Graham Cracker

**Essence d'Assortiment**
* Yellowy Lime
* (Almost) any wine
* Vinegar
* Chanterelle
* Onion
EOBOOK
);
    }

}