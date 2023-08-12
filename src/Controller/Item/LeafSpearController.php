<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Repository\ItemRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item")
 */
class LeafSpearController extends AbstractController
{
    /**
     * @Route("/leafSpear/{inventory}/unwrap", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function unwrapLeafSpear(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService, ItemRepository $itemRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'leafSpear/#/unwrap');

        $wasEquipped = $inventory->getHolder() !== null;

        $inventory->changeItem($itemRepository->findOneByName('Really Big Leaf'));

        $stringLocation = $inventory->getLocation() === LocationEnum::WARDROBE
            ? LocationEnum::HOME
            : $inventory->getLocation()
        ;

        $inventoryService->receiveItem('String', $user, $inventory->getCreatedBy(), $user->getName() . ' pulled this off of Leaf Spear.', $stringLocation, $inventory->getLockedToOwner());

        $em->flush();

        $responseService->setReloadPets($wasEquipped);

        return $responseService->itemActionSuccess('You untie the String, and the leaf practically unrolls on its own.', [ 'itemDeleted' => true ]);
    }
}
