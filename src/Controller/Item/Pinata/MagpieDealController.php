<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/magpieDeal")
 */
class MagpieDealController extends AbstractController
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
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'magpieDeal/#/quint');
        ItemControllerHelpers::validateHouseSpace($inventory, $inventoryService);

        $location = $inventory->getLocation();

        for($i = 0; $i < 2; $i++)
            $inventoryService->receiveItem('Quintessence', $user, $user, $user->getName() . ' got this from a Magpie\'s Deal.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('A mail magpie delivers two Quintessence. "Thus concludes our deal!" it squawks, before flying away.', [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/feathersAndEggs", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getFeathersAndEggs(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, Squirrel3 $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'magpieDeal/#/feathersAndEggs');
        ItemControllerHelpers::validateHouseSpace($inventory, $inventoryService);

        $location = $inventory->getLocation();

        $newInventory = [
            $inventoryService->receiveItem('Feathers', $user, $user, $user->getName() . ' got this from a Magpie\'s Deal.', $location),
            $inventoryService->receiveItem('Feathers', $user, $user, $user->getName() . ' got this from a Magpie\'s Deal.', $location),
            $inventoryService->receiveItem('Egg', $user, $user, $user->getName() . ' got this from a Magpie\'s Deal.', $location),
        ];

        for($i = 0; $i < 3; $i++)
            $newInventory[] = $inventoryService->receiveItem($squirrel3->rngNextFromArray([ 'Feathers', 'Egg' ]), $user, $user, $user->getName() . ' got this from a Magpie\'s Deal.', $location);

        $itemList = array_map(fn(Inventory $i) => $i->getItem()->getName(), $newInventory);
        sort($itemList);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('A mail magpie delivers ' . ArrayFunctions::list_nice($itemList) . '. "Thus concludes our deal!" it squawks, before flying away.', [ 'itemDeleted' => true ]);
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
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'magpieDeal/#/sticks');
        ItemControllerHelpers::validateHouseSpace($inventory, $inventoryService);

        $location = $inventory->getLocation();

        for($i = 0; $i < 5; $i++)
            $inventoryService->receiveItem('Crooked Stick', $user, $user, $user->getName() . ' got this from a Magpie\'s Deal.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('A mail magpie delivers five Crooked Sticks. "Thus concludes our deal!" it squawks, before flying away.', [ 'itemDeleted' => true ]);
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
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'magpieDeal/#/shinyMetals');
        ItemControllerHelpers::validateHouseSpace($inventory, $inventoryService);

        $location = $inventory->getLocation();

        $inventoryService->receiveItem('Gold Bar', $user, $user, $user->getName() . ' got this from a Magpie\'s Deal.', $location);
        $inventoryService->receiveItem('Silver Bar', $user, $user, $user->getName() . ' got this from a Magpie\'s Deal.', $location);
        $inventoryService->receiveItem('Iron Bar', $user, $user, $user->getName() . ' got this from a Magpie\'s Deal.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('A mail magpie delivers an Iron, Silver, and Gold Bar. "Thus concludes our deal!" it squawks, before flying away.', [ 'itemDeleted' => true ]);
    }
}
