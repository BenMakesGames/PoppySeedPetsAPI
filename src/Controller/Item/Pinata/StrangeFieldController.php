<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\RecyclingService;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/item/strangeField")]
class StrangeFieldController extends AbstractController
{
    #[Route("/{inventory}/collapse", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function open(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        EntityManagerInterface $em, UserStatsService $userStatsRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'strangeField/#/collapse');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $userStatsRepository->incrementStat($user, UserStatEnum::STRANGE_FIELDS_COLLAPSED);

        $possibleItems = [
            'Tachyon', 'Photon',
            'Mikronium', 'Mikronium',
            'Megalium', 'Megalium',
        ];

        $items = $squirrel3->rngNextSubsetFromArray($possibleItems, 2);

        foreach($items as $item)
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' collapsed a Strange Field, releasing this.', $location, $lockedToOwner);

        $message = 'The Strange Field collapses, releasing ' . ArrayFunctions::list_nice($items) . '!';

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
