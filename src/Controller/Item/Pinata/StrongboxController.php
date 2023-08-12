<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Exceptions\PSPNotFoundException;
use App\Repository\InventoryRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/box")
 */
class StrongboxController extends AbstractController
{
    /**
     * @Route("/little-strongbox/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openLittleStrongbox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, Squirrel3 $squirrel3,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, InventoryRepository $inventoryRepository,
        TransactionService $transactionService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/little-strongbox/#/open');
        ItemControllerHelpers::validateHouseSpace($inventory, $inventoryService);

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

        $numItems = $squirrel3->rngNextInt(2, $squirrel3->rngNextInt(3, 4));
        $newInventory = [];

        $location = $inventory->getLocation();

        for($i = 0; $i < $numItems; $i++)
            $newInventory[] = $inventoryService->receiveItem($squirrel3->rngNextFromArray($possibleItems), $user, $user, $comment, $location);

        $newInventory[] = $inventoryService->receiveItem('Piece of Cetgueli\'s Map', $user, $user, $comment, $location, $inventory->getLockedToOwner());

        $em->remove($key);

        return BoxHelpers::countRemoveFlushAndRespond('Opening the box revealed ' . $moneys . '~~m~~,', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    /**
     * @Route("/very-strongbox/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openVeryStrongbox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, Squirrel3 $squirrel3,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, InventoryRepository $inventoryRepository,
        TransactionService $transactionService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/very-strongbox/#/open');
        ItemControllerHelpers::validateHouseSpace($inventory, $inventoryService);

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
            'Minor Scroll of Riches',
            'Magic Hourglass',
        ]);

        $items[] = $squirrel3->rngNextFromArray([
            'Scroll of Fruit',
            'Scroll of the Sea',
            'Forgetting Scroll',
        ]);

        $newInventory = [];
        $location = $inventory->getLocation();

        foreach($items as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $comment, $location, $inventory->getLockedToOwner());

        $em->remove($key);

        return BoxHelpers::countRemoveFlushAndRespond('Opening the box revealed ' . $moneys . '~~m~~,', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    /**
     * @Route("/outrageously-strongbox/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openOutrageouslyStrongbox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, Squirrel3 $squirrel3,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, InventoryRepository $inventoryRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/outrageously-strongbox/#/open');
        ItemControllerHelpers::validateHouseSpace($inventory, $inventoryService);

        $key = $inventoryRepository->findOneToConsume($user, 'Gold Key');

        if(!$key)
            throw new PSPNotFoundException('You need a Gold Key to do that.');

        $comment = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.';

        $items = [
            'Very Strongbox',
            'Major Scroll of Riches',
            'Major Scroll of Riches',
            'Dumbbell',
        ];

        $items[] = $squirrel3->rngNextFromArray([
            'Weird, Blue Egg',
            'Unexpectedly-familiar Metal Box',
        ]);

        $newInventory = [];
        $location = $inventory->getLocation();

        foreach($items as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $comment, $location, $inventory->getLockedToOwner());

        $em->remove($key);

        return BoxHelpers::countRemoveFlushAndRespond('Opening the box revealed', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }
}
