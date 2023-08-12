<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\RecyclingService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/item/strangeField")
 */
class StrangeFieldController extends AbstractController
{
    /**
     * @Route("/{inventory}/collapse", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function open(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, Squirrel3 $squirrel3,
        EntityManagerInterface $em, RecyclingService $recyclingService, UserStatsRepository $userStatsRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'strangeField/#/collapse');

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
