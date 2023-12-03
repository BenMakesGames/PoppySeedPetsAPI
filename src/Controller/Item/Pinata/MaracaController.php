<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/maraca")
 */
class MaracaController extends AbstractController
{
    #[Route("/{inventory}/takeApart", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'maraca/#/takeApart');

        $location = $inventory->getLocation();

        $count = $squirrel3->rngNextInt(2, 3);

        // definitely Beans
        $inventoryService->receiveItem('Beans', $user, $user, $user->getName() . ' sacrificed a Maraca for these.', $location);

        // 50/50 chance of Magic Beans
        if($squirrel3->rngNextInt(1, 2) === 1)
            $inventoryService->receiveItem('Magic Beans', $user, $user, $user->getName() . ' sacrificed a Maraca for these.', $location);
        else
            $inventoryService->receiveItem('Beans', $user, $user, $user->getName() . ' sacrificed a Maraca for these.', $location);

        // 50/50 chance of extra Beans
        if($count === 3)
            $inventoryService->receiveItem('Beans', $user, $user, $user->getName() . ' sacrificed a Maraca for these.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You take the Maraca apart, recuperating ' . $count . ' lots of Beans.' . "\n\nBecause that's totally how Beans are measured.\n\nIn \"lots\".", [ 'itemDeleted' => true ]);
    }
}
