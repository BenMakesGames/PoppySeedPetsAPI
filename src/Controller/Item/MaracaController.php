<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/maraca")
 */
class MaracaController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/takeApart", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        $this->validateInventory($inventory, 'maraca/#/takeApart');

        $user = $this->getUser();

        $location = $inventory->getLocation();

        $count = mt_rand(2, 3);

        // definitely Beans
        $inventoryService->receiveItem('Beans', $user, $user, $user->getName() . ' sacrificed a Maraca for these.', $location);

        // 50/50 chance of Magic Beans
        if(mt_rand(1, 2) === 1)
            $inventoryService->receiveItem('Magic Beans', $user, $user, $user->getName() . ' sacrificed a Maraca for these.', $location);
        else
            $inventoryService->receiveItem('Beans', $user, $user, $user->getName() . ' sacrificed a Maraca for these.', $location);

        // 50/50 chance of extra Beans
        if($count === 3)
            $inventoryService->receiveItem('Beans', $user, $user, $user->getName() . ' sacrificed a Maraca for these.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You take the Maraca apart, recuperating ' . $count . ' lots of Beans.' . "\n\nBecause that's totally how Beans are measured.\n\nIn \"lots\".", [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}