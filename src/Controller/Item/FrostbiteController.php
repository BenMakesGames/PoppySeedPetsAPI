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
class FrostbiteController extends AbstractController
{
    #[Route("/frostbite/{inventory}/break", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function breakFrostbite(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'frostbite/#/break');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $icicle = ItemRepository::findOneByName($em, 'Icicle');

        $originalName = $inventory->getItem()->getNameWithArticle();

        $inventory
            ->changeItem($icicle)
            ->addComment($user->getName() . ' intentionally snapped ' . $originalName . ' in two; this is one of those halves.')
        ;

        $inventoryService->receiveItem($icicle, $user, $inventory->getCreatedBy(), $user->getName() . ' broke this off of ' . $originalName . '.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $em->flush();

        return $responseService->itemActionSuccess('You break the thing in two, yielding two Icicles!', [ 'itemDeleted' => true ]);
    }
}
