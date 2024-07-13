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
                $inventoryService->receiveItem('Everice', $user, $user, $user->getName() . ' shattered an Ice "Mango"... it was actually just an ice-encrusted Mango. This is that ice.', $location, $lockedToOwner);
                $inventoryService->receiveItem('Mango', $user, $user, $user->getName() . ' shattered an Ice "Mango"... it was actually just an ice-encrusted Mango. This is that Mango.', $location, $lockedToOwner);
                $message = 'You smash the "mango", but rather than shattering to bits, a layer of ice breaks off, revealing _an actual Mango_, inside! It was actually just some ice-encrusted Mango after all!';
                break;

            case 2:
                $inventoryService->receiveItem('Everice', $user, $user, $user->getName() . ' shattered an Ice "Mango". This is a chunk of the icy remains from that violent event.', $location, $lockedToOwner);
                $inventoryService->receiveItem('Rock', $user, $user, $user->getName() . ' shattered an Ice "Mango"... there was just this rock inside :|', $location, $lockedToOwner);
                $message = 'You smash the "mango", sending shards of Everice everywhere, and revealing...!!! ... oh, it was just a Rock, inside. Dangit.';
                break;

            case 3:
                $item = ItemRepository::findOneByName($em, $rng->rngNextFromArray([
                    '"Alien" Camera',
                    '"Chicken" Noodle Soup',
                    '"Gold" Idol',
                    '"Roy" Plushy',
                    '"Rustic" Magnifying Glass',
                    '"Wolf" Balloon',
                    'Cooking "Alien"',
                    'Tomato "Sushi"',
                    'Zebra "Horsey" Hat',
                ]));

                $inventoryService->receiveItem('Everice', $user, $user, $user->getName() . ' shattered an Ice "Mango". This is a chunk of the icy remains from that violent event.', $location, $lockedToOwner);
                $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' shattered an Ice "Mango"... there was just this rock inside :|', $location, $lockedToOwner);
                $message = 'You smash the "mango", sending shards of Everice everywhere, and revealing...!!! ... ' . $item->getNameWithArticle() . '?!? How the heck did _that_ get in there!';
                break;

            case 4:
                $inventoryService->receiveItem('Everice', $user, $user, $user->getName() . ' shattered an Ice "Mango". This is a chunk of the icy remains from that violent event.', $location, $lockedToOwner);
                $inventoryService->receiveItem('Noetala Egg', $user, $user, $user->getName() . ' shattered an Ice "Mango"... there was just this rock inside :|', $location, $lockedToOwner);
                $message = 'You smash the "mango", sending shards of Everice everywhere, and revealing...!!! ... a Noetala Egg?! Worrying... (was she here??)';
                break;

            default:
                throw new \Exception('This should never happen. But it did. Ben has been notified.');
        }

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
