<?php
namespace App\Controller\Item\Scroll;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Functions\ItemRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/scroll")]
class StarMonkeyController extends AbstractController
{
    #[Route("/starMonkey/{inventory}/items", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getItems(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'scroll/starMonkey/#/items');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $em->remove($inventory);

        $items = [
            $rng->rngNextFromArray([ 'Tile: Naner-eater', 'Naner-picker' ]),
            $rng->rngNextFromArray([ 'Chocolate-covered Naner', 'Chocolate-covered Naner with Nuts' ]),
            $rng->rngNextFromArray([ 'Naner Ketchup', 'Naner Preserves' ]),
            $rng->rngNextFromArray([ 'Naner Dog', 'Naner Pancakes' ]),
        ];

        foreach($items as $item)
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' summoned this by reading a Scroll of the Star Monkey.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess(
            'The scroll vanishes, replaced by several Naner-themed items...',
            [ 'itemDeleted' => true ]
        );
    }

    #[Route("/starMonkey/{inventory}/summoningScroll", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getSummoningScroll(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'scroll/starMonkey/#/summoningScroll');

        $naner = ItemRepository::getIdByName($em, 'Naner');

        if($inventoryService->loseItem($user, $naner, $inventory->getLocation(), 2) < 2)
        {
            return $responseService->itemActionSuccess('It seems you need two Naners to do this...');
        }

        $em->remove($inventory);

        $inventoryService->receiveItem('Monster-summoning Scroll', $user, $user, $user->getName() . ' summoned this by offering two Naners to a Scroll of the Star Monkey.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess(
            'You offer two Naners to the Scroll of the Star Monkey; they vanish, replaced by a Monster-summoning Scroll!',
            [ 'itemDeleted' => true ]
        );
    }
}
