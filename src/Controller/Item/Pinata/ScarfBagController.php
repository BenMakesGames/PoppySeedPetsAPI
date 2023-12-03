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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/scarfBag")
 */
class ScarfBagController extends AbstractController
{
    #[Route("/{bag}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openScarfBag(
        Inventory $bag, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        EntityManagerInterface $em
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $bag, 'scarfBag/#/open');
        ItemControllerHelpers::validateLocationSpace($bag, $em);

        $location = $bag->getLocation();
        $lockedToOwner = $bag->getLockedToOwner();



        $scarf = $rng->rngNextFromArray([
            'North Star Scarf',
            'Pine Green Scarf',
            'Rainbow Scarf',
            'Betelgeuse Scarf',
            'Freddy Scarf',
            'Cheshire Scarf',
            'Toothpaste Scarf',
            'Starry Night Scarf',
            'Black Scarf',
            'Memories of Summer',
        ]);

        $inventoryService->receiveItem($scarf, $user, $bag->getCreatedBy(), 'Found inside a Scarf Bag.', $location, $lockedToOwner);

        $em->remove($bag);

        $em->flush();

        $message = 'You open the bag, and find a ' . $scarf . ' inside!';

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
