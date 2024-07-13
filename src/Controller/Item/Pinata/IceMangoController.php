<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Functions\ItemRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/iceMango")]
class IceMangoController extends AbstractController
{
    #[Route("/{inventory}/shatter", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function shatterIceMango(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        EntityManagerInterface $em, UserStatsService $userStatsRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'iceMango/#/shatter');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        switch($rng->rngNextInt(1, 4))
        {
            case 1:
                $inventoryService->receiveItem('Mango', $user, $user, $user->getName() . ' shattered an Ice Mango... it was actually just this ice-encrusted Mango. This is that Mango.', $location, $lockedToOwner);
                $inventoryService->receiveItem('Everice', $user, $user, $user->getName() . ' shattered an Ice Mango... it was actually just an ice-encrusted Mango. This is that ice.', $location, $lockedToOwner);
                $message = 'You smash the Ice Mango, but rather than shattering to bits, a layer of ice breaks off, revealing a Mango, inside. It _was_ merely some ice-encrusted Mango after all!';
                break;

            case 2:

            default:
                throw new \Exception('This should never happen. But it did. Ben has been notified.');
        }

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
