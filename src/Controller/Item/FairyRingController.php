<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Functions\ItemRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @Route("/item/fairyRing")
 */
class FairyRingController extends AbstractController
{
    #[Route("/{inventory}/takeApart", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function takeApart(
        Inventory $inventory, ResponseService $responseService, IRandom $squirrel3,
        EntityManagerInterface $em, InventoryService $inventoryService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'fairyRing/#/takeApart');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $inventory->changeItem(ItemRepository::findOneByName($em, 'Gold Ring'));

        $inventoryService->receiveItem('Wings', $user, $inventory->getCreatedBy(), $user->getName() . ' pulled these off a Fairy Ring.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $em->flush();

        $message = 'You pull the Wings off the Fairy Ring. Now it\'s just a regular Gold Ring.';

        if($squirrel3->rngNextInt(1, 70) === 1)
            $message .= $squirrel3->rngNextFromArray([ ' (I hope you\'re happy.)', ' (See what thy hand hath wrought!)', ' (All according to plan...)' ]);

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);

    }
}
