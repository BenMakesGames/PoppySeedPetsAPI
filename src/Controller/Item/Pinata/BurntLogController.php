<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Functions\ArrayFunctions;
use App\Functions\GrammarFunctions;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/burntLog")
 */
class BurntLogController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/break", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openBurntLog(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        $this->validateInventory($inventory, 'burntLog/#/break');

        $user = $this->getUser();
        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $extraItem = ArrayFunctions::pick_one([
            'Crooked Stick',
            'Iron Ore',
            'Glass',
            'Glowing Six-sided Die',
            'Fried Egg',
        ]);

        if(mt_rand(1, 4) === 1)
        {
            $charcoalReceived = 'Charcoal, Liquid-hot Magma';
            $inventoryService->receiveItem('Liquid-hot Magma', $user, $user, $user->getName() . ' pulled this out of a Burnt Log.', $location, $lockedToOwner);
        }
        else
        {
            $charcoalReceived = 'three Charcoal';
            $inventoryService->receiveItem('Charcoal', $user, $user, $user->getName() . ' pulled this out of a Burnt Log.', $location, $lockedToOwner);
            $inventoryService->receiveItem('Charcoal', $user, $user, $user->getName() . ' pulled this out of a Burnt Log.', $location, $lockedToOwner);
        }

        $inventoryService->receiveItem('Charcoal', $user, $user, $user->getName() . ' pulled this out of a Burnt Log.', $location, $lockedToOwner);

        $inventoryService->receiveItem($extraItem, $user, $user, $user->getName() . ' pulled this out of a Burnt Log.', $location, $lockedToOwner);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You break the Burnt Log apart, receiving ' . $charcoalReceived . ', and ' . GrammarFunctions::indefiniteArticle($extraItem) . ' ' . $extraItem . '!', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}