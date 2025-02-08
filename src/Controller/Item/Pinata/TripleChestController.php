<?php
declare(strict_types=1);

namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Exceptions\PSPNotFoundException;
use App\Repository\InventoryRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\TransactionService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item")]
class TripleChestController extends AbstractController
{
    #[Route("/tripleChest/{inventory}/openWithIronKey", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openWithIronKey(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        UserStatsService $userStatsRepository, EntityManagerInterface $em, InventoryRepository $inventoryRepository,
        TransactionService $transactionService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'tripleChest/#/openWithIronKey');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $key = $inventoryRepository->findOneToConsume($user, 'Iron Key');

        if(!$key)
            throw new PSPNotFoundException('You need an Iron Key to do that.');

        $comment = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.';

        $moneys = $squirrel3->rngNextInt(10, $squirrel3->rngNextInt(20, $squirrel3->rngNextInt(50, $squirrel3->rngNextInt(100, 200)))); // averages 35?

        $transactionService->getMoney($user, $moneys, 'Found inside ' . $inventory->getItem()->getNameWithArticle() . '.');

        $possibleItems = [
            'Silver Bar', 'Silver Bar',
            'Gold Bar',
            'Rusty Blunderbuss',
            'Rusty Rapier',
            'Blackberry Wine',
            'Fluff',
            'Glowing Six-sided Die',
        ];

        $newInventory = [];

        $location = $inventory->getLocation();

        for($i = 0; $i < 2; $i++)
            $newInventory[] = $inventoryService->receiveItem($squirrel3->rngNextFromArray($possibleItems), $user, $user, $comment, $location);

        $finalLoot = $squirrel3->rngNextFromArray([
            'Major Scroll of Riches',
            'Scroll of Dice',
            'Noetala Egg',
        ]);

        $newInventory[] = $inventoryService->receiveItem($finalLoot, $user, $user, $comment, $location, $inventory->getLockedToOwner());

        $em->remove($key);

        return BoxHelpers::countRemoveFlushAndRespond('Opening the box revealed ' . $moneys . '~~m~~,', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/tripleChest/{inventory}/openWithSilverKey", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openWithSilverKey(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        UserStatsService $userStatsRepository, EntityManagerInterface $em, InventoryRepository $inventoryRepository,
        TransactionService $transactionService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'tripleChest/#/openWithIronKey');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $key = $inventoryRepository->findOneToConsume($user, 'Silver Key');

        if(!$key)
            throw new PSPNotFoundException('You need a Silver Key to do that.');

        $comment = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.';

        $moneys = $squirrel3->rngNextInt(15, $squirrel3->rngNextInt(45, $squirrel3->rngNextInt(100, $squirrel3->rngNextInt(200, 300)))); // averages 50?

        $transactionService->getMoney($user, $moneys, 'Found inside ' . $inventory->getItem()->getNameWithArticle() . '.');

        $items = [
            'Silver Bar',
            'Gold Bar',
            'Gold Bar',
            'Glowing Six-sided Die',
        ];

        $items[] = $squirrel3->rngNextFromArray([
            'Rusty Blunderbuss',
            'Rusty Rapier',
            'Pepperbox',
        ]);

        $items[] = $squirrel3->rngNextFromArray([
            'Scroll of Fruit',
            'Magic Hourglass',
            'Forgetting Scroll',
        ]);

        $newInventory = [];
        $location = $inventory->getLocation();

        foreach($items as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $comment, $location, $inventory->getLockedToOwner());

        $em->remove($key);

        return BoxHelpers::countRemoveFlushAndRespond('Opening the box revealed ' . $moneys . '~~m~~,', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/tripleChest/{inventory}/openWithGoldKey", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openWithGoldKey(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        UserStatsService $userStatsRepository, EntityManagerInterface $em, InventoryRepository $inventoryRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'tripleChest/#/openWithIronKey');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $key = $inventoryRepository->findOneToConsume($user, 'Gold Key');

        if(!$key)
            throw new PSPNotFoundException('You need a Gold Key to do that.');

        $comment = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.';

        $items = [
            'Very Strongbox',
            'Major Scroll of Riches',
            'Liquid-hot Magma',
        ];

        $items[] = $squirrel3->rngNextFromArray([
            'Monster-summoning Scroll',
        ]);

        $newInventory = [];
        $location = $inventory->getLocation();

        foreach($items as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $comment, $location, $inventory->getLockedToOwner());

        $em->remove($key);

        return BoxHelpers::countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }
}
