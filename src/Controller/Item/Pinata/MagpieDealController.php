<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Functions\ArrayFunctions;
use App\Repository\InventoryRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/magpieDeal")
 */
class MagpieDealController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/quint", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getQuint(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'magpieDeal/#/quint');

        $location = $inventory->getLocation();

        for($i = 0; $i < 2; $i++)
            $inventoryService->receiveItem('Quintessence', $user, $user, $user->getName() . ' got this from a Magpie\'s Deal.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('A mail magpie delivers two Quintessence. "Thus concludes our deal!" it squawks, before flying away.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/feathersAndEggs", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getFeathersAndEggs(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'magpieDeal/#/feathersAndEggs');

        $location = $inventory->getLocation();

        $newInventory = [];

        for($i = 0; $i < 6; $i++)
            $newInventory[] = $inventoryService->receiveItem(ArrayFunctions::pick_one([ 'Feathers', 'Egg' ]), $user, $user, $user->getName() . ' got this from a Magpie\'s Deal.', $location);

        $itemList = array_map(function(Inventory $i) { return $i->getItem()->getName(); }, $newInventory);
        sort($itemList);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('A mail magpie delivers ' . ArrayFunctions::list_nice($itemList) . '. "Thus concludes our deal!" it squawks, before flying away.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/sticks", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getSticks(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'magpieDeal/#/sticks');

        $location = $inventory->getLocation();

        for($i = 0; $i < 5; $i++)
            $inventoryService->receiveItem('Crooked Stick', $user, $user, $user->getName() . ' got this from a Magpie\'s Deal.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('A mail magpie delivers five Crooked Sticks. "Thus concludes our deal!" it squawks, before flying away.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/shinyMetals", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getShinyMetals(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'magpieDeal/#/shinyMetals');

        $location = $inventory->getLocation();

        $inventoryService->receiveItem('Gold Bar', $user, $user, $user->getName() . ' got this from a Magpie\'s Deal.', $location);
        $inventoryService->receiveItem('Silver Bar', $user, $user, $user->getName() . ' got this from a Magpie\'s Deal.', $location);
        $inventoryService->receiveItem('Iron Bar', $user, $user, $user->getName() . ' got this from a Magpie\'s Deal.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('A mail magpie delivers an Iron, Silver, and Gold Bar. "Thus concludes our deal!" it squawks, before flying away.', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}