<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Functions\ArrayFunctions;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/box")
 */
class BoxController extends PsyPetsItemController
{
    /**
     * @Route("/bakers/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openBakers(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/bakers/#/open');

        $newInventory = [];

        for($i = 0; $i < 5; $i++)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one([ 'Egg', 'Wheat Flour', 'Sugar', 'Milk' ]), $user, $user, $user->getName() . ' got this from a weekly Care Package.');

        for($i = 0; $i < 4; $i++)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one([ 'Corn Syrup', 'Aging Powder', 'Cocoa Beans', 'Baking Soda', 'Cream of Tartar' ]), $user, $user, $user->getName() . ' got this from a weekly Care Package.');

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $itemList = array_map(function(Inventory $i) { return $i->getItem()->getName(); }, $newInventory);
        sort($itemList);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('Opening the box revealed ' . ArrayFunctions::list_nice($itemList) . '.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/fruits-n-veggies/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openFruitsNVeggies(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/fruits-n-veggies/#/open');

        $newInventory = [];

        for($i = 0; $i < 5; $i++)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one(['Orange', 'Red', 'Blackberries', 'Blueberries']), $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.');

        for($i = 0; $i < 4; $i++)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one(['Carrot', 'Onion', 'Celery', 'Carrot', 'Sweet Beet']), $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.');

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $itemList = array_map(function(Inventory $i) { return $i->getItem()->getName(); }, $newInventory);
        sort($itemList);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('Opening the box revealed ' . ArrayFunctions::list_nice($itemList) . '.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/july4/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function open4thOfJulyBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/july4/#/open');

        $comment = $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.';

        $newInventory = [
            $inventoryService->receiveItem('Hot Dog', $user, $user, $comment),
            $inventoryService->receiveItem('Hot Dog', $user, $user, $comment),
            $inventoryService->receiveItem('Sunscreen', $user, $user, $comment),
            $inventoryService->receiveItem('Red Firework', $user, $user, $comment),
            $inventoryService->receiveItem('White Firework', $user, $user, $comment),
            $inventoryService->receiveItem('Blue Firework', $user, $user, $comment),
        ];

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $itemList = array_map(function(Inventory $i) { return $i->getItem()->getName(); }, $newInventory);
        sort($itemList);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('Opening the box revealed ' . ArrayFunctions::list_nice($itemList) . '.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}