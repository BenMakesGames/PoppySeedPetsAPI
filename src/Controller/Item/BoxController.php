<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Functions\ArrayFunctions;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
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
    public function openBakers(Inventory $inventory, ResponseService $responseService)
    {
        $this->validateInventory($inventory, 'box/bakers/#/open');

        return $responseService->itemActionSuccess('');
    }

    /**
     * @Route("/fruits-n-veggies/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openFruitsNVeggies(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        UserStatsRepository $userStatsRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'box/fruits-n-veggies/#/open');

        for($i = 0; $i < 3; $i++)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one(['Carrot', 'Onion', 'Celery', 'Carrot', 'Sweet Beet']), $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.');

        for($i = 0; $i < 3; $i++)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one(['Orange', 'Red', 'Blackberries', 'Blueberries']), $user, $user, $user->getName() . ' got this from a ' . $inventory->getItem()->getName() . '.');

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        return $responseService->itemActionSuccess('');
    }
}