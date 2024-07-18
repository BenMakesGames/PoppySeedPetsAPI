<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Functions\ItemRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/cheesyLump")]
class CheesyLumpController extends AbstractController
{
    #[Route("/{lump}/clean", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function clean(
        Inventory $lump, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        UserStatsService $userStatsRepository, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $lump, 'cheesyLump/#/clean');

        $location = $lump->getLocation();
        $lockedToOwner = $lump->getLockedToOwner();

        $item = $rng->rngNextFromArray([
            'Chocolate Sword',
            'French Fries',
            'Potato',
            'Noodles',
            'Toad Legs',

            'Potion of Arcana',
            'Scroll of Dice',
            'Plastic Idol',
            'Magic Crystal Ball',
            'Dirt-covered... Something',
        ]);

        $itemObject = ItemRepository::findOneByName($em, $item);

        $userStatsRepository->incrementStat($user, 'Cleaned a ' . $lump->getItem()->getName());

        $inventoryService->receiveItem('Cheese', $user, $lump->getCreatedBy(), $user->getName() . ' cleaned this off some cheese-covered ' . $itemObject->getName() . '.', $location, $lockedToOwner);
        $inventoryService->receiveItem($itemObject, $user, $lump->getCreatedBy(), $user->getName() . ' found this covered in cheese.', $location, $lockedToOwner);
        $message = 'You clean off the object, which reveals itself to be ' . $itemObject->getNameWithArticle() . '!';

        $em->remove($lump);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
