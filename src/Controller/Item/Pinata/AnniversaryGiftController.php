<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/anniversaryGift")
 */
class AnniversaryGiftController extends AbstractController
{
    /**
     * @Route("/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function open(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'anniversaryGift/#/open');
        ItemControllerHelpers::validateHouseSpace($inventory, $inventoryService);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $loot = [
            'Lengthy Scroll of Skill',
            'Lengthy Scroll of Skill',
            'Species Transmigration Serum',
            'Slice of Poppy Seed* Pie',
            'Everice'
        ];

        foreach($loot as $itemName)
            $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this in ' . $inventory->getItem()->getNameWithArticle() . '!', $location, $lockedToOwner);

        $em->remove($inventory);
        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess('You open the box, revealing the anniversarial goodies hidden inside: two Lengthy Scrolls of Skill, a Species Transmigration Serum, a slice of pie, and the cube of Everice that was keeping the pie refrigerated!', [ 'itemDeleted' => true ]);
    }
}
