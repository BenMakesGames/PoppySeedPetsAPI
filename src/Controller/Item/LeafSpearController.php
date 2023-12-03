<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Functions\ItemRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route("/item")]
class LeafSpearController extends AbstractController
{
    #[Route("/leafSpear/{inventory}/unwrap", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function unwrapLeafSpear(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'leafSpear/#/unwrap');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $inventory
            ->changeItem(ItemRepository::findOneByName($em, 'Really Big Leaf'))
            ->addComment($user->getName() . ' untied a Leaf Spear, causing it to unroll into this.')
        ;

        $inventoryService->receiveItem('String', $user, $inventory->getCreatedBy(), $user->getName() . ' pulled this off of a Leaf Spear.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $em->flush();

        return $responseService->itemActionSuccess('You untie the String, and the leaf practically unrolls on its own.', [ 'itemDeleted' => true ]);
    }
}
