<?php
namespace App\Controller\Item\Scroll;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/scroll")
 */
class SeaController extends AbstractController
{
    #[Route("/sea/{inventory}/invoke", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function invokeSeaScroll(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        UserStatsService $userStatsRepository, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'scroll/sea/#/invoke');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $items = [
            'Fish',
            'Seaweed',
            'Silica Grounds',
            $squirrel3->rngNextFromArray([ 'Fish', 'Tentacle' ]),
            $squirrel3->rngNextFromArray([ 'Seaweed', 'Fish' ]),
            $squirrel3->rngNextFromArray([ 'Seaweed', 'Silica Grounds' ]),
            $squirrel3->rngNextFromArray([ 'Seaweed', 'Crooked Stick' ]),
        ];

        if($squirrel3->rngNextInt(1, 4) === 1) $items[] = 'Glass';
        if($squirrel3->rngNextInt(1, 5) === 1) $items[] = 'Music Note';
        if($squirrel3->rngNextInt(1, 8) === 1) $items[] = 'Mermaid Egg';
        if($squirrel3->rngNextInt(1, 10) === 1) $items[] = 'Secret Seashell';
        if($squirrel3->rngNextInt(1, 15) === 1) $items[] = 'Iron Ore';
        if($squirrel3->rngNextInt(1, 20) === 1) $items[] = 'Little Strongbox';
        if($squirrel3->rngNextInt(1, 45) === 1) $items[] = 'Ceremony of Sand and Sea';

        $location = $inventory->getLocation();

        sort($items);

        foreach($items as $item)
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location);

        $em->flush();

        $responseService->addFlashMessage('You read the scroll, summoning ' . ArrayFunctions::list_nice($items) . '.');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
