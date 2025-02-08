<?php
declare(strict_types=1);

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
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/ohGames")]
class OhGamesController extends AbstractController
{
    #[Route("/{inventory}/rockPaintingKit", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function rockPaintingKit(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'ohGames/#/rockPaintingKit');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $dyes = [
            'Green Dye', 'Yellow Dye', 'Quinacridone Magenta Dye',
        ];

        $lucky = $rng->rngNextInt(1, 100) === 1;

        $loot = [
            'Rock', 'Rock', $lucky ? 'Meteorite' : 'Rock',
            $rng->rngNextFromArray($dyes),
            $rng->rngNextFromArray($dyes),
            $rng->rngNextFromArray($dyes)
        ];

        foreach($loot as $itemName)
            $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '!', $location, $lockedToOwner);

        $em->remove($inventory);
        $em->flush();

        $responseService->setReloadInventory();

        $message = 'You open the box, revealing three rocks, and three dyes!';

        if($lucky)
            $message .= ' (One of the rocks seems a little different, though...)';

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/sneqosAndLadders", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function sneqosAndLadders(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'ohGames/#/sneqosAndLadders');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $lucky = $rng->rngNextInt(1, 50) === 1;

        $loot = [
            'Scales', 'Talon', 'Talon',
            'Crooked Stick', 'Crooked Stick', 'Crooked Stick', $lucky ? 'Stick Insect' : 'Crooked Stick',
            $rng->rngNextFromArray([ 'Glowing Four-sided Die', 'Glowing Six-sided Die', 'Glowing Eight-sided Die' ])
        ];

        foreach($loot as $itemName)
            $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '!', $location, $lockedToOwner);

        $em->remove($inventory);
        $em->flush();

        $responseService->setReloadInventory();

        $message = 'You open the box, revealing scales, a couple fangs, some sticks, and a die! (They weren\'t lying when they said "some assembly required"!)';

        if($lucky)
            $message .= "\n\n" . $rng->rngNextFromArray([ 'Whoa, hey!', 'Whoamygoodness!', 'Hey, whoa!', 'Holy poop!', 'Eek!' ]) . ' Did one of the sticks just move?!';

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
