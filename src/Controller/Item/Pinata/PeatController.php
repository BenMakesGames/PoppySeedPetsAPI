<?php
declare(strict_types=1);

namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/peat")]
class PeatController extends AbstractController
{
    #[Route("/{inventory}/sort", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function raidPeat(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'peat/#/sort');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $loot = array_merge(
            [ 'Large Bag of Fertilizer', 'Dark Matter', 'Fruit Fly' ],
            $rng->rngNextSubsetFromArray(
                [
                    'Small Bag of Fertilizer',
                    'Small Bag of Fertilizer',
                    'Fruit Fly',
                    'Worms',
                    'Crooked Stick',
                    'Charcoal',
                    'Rock',
                    'Really Big Leaf',
                    'Grandparoot',
                ],
                3
            )
        );

        sort($loot);

        foreach($loot as $itemName)
        {
            $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this in a clump of Barnacles.', $location, $lockedToOwner)
                ->setSpice($inventory->getSpice());
        }

        $em->remove($inventory);
        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess('You sort through the peat, separating it into ' . ArrayFunctions::list_nice($loot) . '.', [ 'itemDeleted' => true ]);
    }
}
