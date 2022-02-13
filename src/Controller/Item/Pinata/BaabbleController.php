<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/baabble")
 */
class BaabbleController extends PoppySeedPetsItemController
{
    private const LAME_SHIT = [
        'Crooked Stick', 'Scales', 'Tea Leaves', 'Aging Powder', 'Fluff', 'Pointer', 'Creamy Milk', 'Silica Grounds',
    ];

    private const OKAY_STUFF = [
        'Crooked Stick',
        'Iron Ore',
        'Plastic', 'Plastic',
        'Glass', 'Paper', 'Talon', 'Feathers', 'Glue',
    ];

    private const GOOD_STUFF = [
        'Quintessence', 'Quintessence', 'Wings',
        'Iron Ore', 'Iron Ore', 'Iron Bar',
        'Silver Ore', 'Silver Ore',
        'Gold Ore',
        'Dark Scales', 'Hash Table', 'Paper Bag', 'Finite State Machine', 'Fiberglass', 'Tiny Scroll of Resources'
    ];

    private const WEIRD_STUFF = [
        'Really Big Leaf', 'Music Note', 'Bag of Beans', 'Crystal Ball', 'Linens and Things', 'Dark Matter',
        'Coriander Flower', 'Charcoal', 'Tentacle', 'XOR', 'Liquid-hot Magma', 'Quinacridone Magenta Dye', 'Gypsum',
        'Tiny Black Hole', 'Chocolate Bar',
    ];

    private const RARE_STUFF = [
        'Blackonite', 'Everice', 'Striped Microcline', 'Firestone', 'Black Feathers',
        'Magic Smoke', 'Lightning in a Bottle',
    ];

    /**
     * @Route("/black/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openBlackBaabble(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, UserStatsRepository $userStatsRepository, Squirrel3 $squirrel3
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'baabble/black/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();
        $createdBy = $inventory->getCreatedBy();

        $items = [];

        $lameThings = $squirrel3->rngNextInt(2, 8);
        $okayThings = $squirrel3->rngNextInt(7, 17);
        $goodThings = $squirrel3->rngNextInt(0, 9);

        for($i = 0; $i < $lameThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::LAME_SHIT);

        for($i = 0; $i < $okayThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::OKAY_STUFF);

        for($i = 0; $i < $goodThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::GOOD_STUFF);

        $weirdItem = $squirrel3->rngNextFromArray(self::WEIRD_STUFF);

        $noteworthy = [ $squirrel3->rngNextFromArray($items), $weirdItem ];

        shuffle($noteworthy);

        $items[] = $weirdItem;

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $em->remove($inventory);

        foreach($items as $itemName)
            $inventoryService->receiveItem($itemName, $user, $createdBy, 'Found inside a ' . $inventory->getItem()->getName() . '.', $location, $lockedToOwner);

        $em->flush();

        return $responseService->itemActionSuccess('You open the Baabble, and, like, ' . count($items) . ' items come flying out! ' . $noteworthy[0] . ', and ' . $noteworthy[1] . ', among them!', [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/white/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openWhiteBaabble(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, UserStatsRepository $userStatsRepository, Squirrel3 $squirrel3
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'baabble/white/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();
        $createdBy = $inventory->getCreatedBy();

        $items = [];

        $lameThings = $squirrel3->rngNextInt(4, 14);
        $okayThings = $squirrel3->rngNextInt(10, 18);
        $goodThings = $squirrel3->rngNextInt(0, 9);
        $rareThings = 1;

        for($i = 0; $i < $lameThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::LAME_SHIT);

        for($i = 0; $i < $okayThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::OKAY_STUFF);

        for($i = 0; $i < $goodThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::GOOD_STUFF);

        for($i = 0; $i < $rareThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::RARE_STUFF);

        $weirdItem = $squirrel3->rngNextFromArray(self::WEIRD_STUFF);

        $noteworthy = [ $squirrel3->rngNextFromArray($items), $weirdItem ];

        shuffle($noteworthy);

        $items[] = $weirdItem;

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $em->remove($inventory);

        foreach($items as $itemName)
            $inventoryService->receiveItem($itemName, $user, $createdBy, 'Found inside a ' . $inventory->getItem()->getName() . '.', $location, $lockedToOwner);

        $em->flush();

        return $responseService->itemActionSuccess('You open the Baabble, and, like, ' . count($items) . ' items come flying out! ' . $noteworthy[0] . ', and ' . $noteworthy[1] . ', among them!', [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/gold/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openGoldBaabble(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, UserStatsRepository $userStatsRepository, Squirrel3 $squirrel3
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'baabble/gold/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();
        $createdBy = $inventory->getCreatedBy();

        $items = [];

        $lameThings = $squirrel3->rngNextInt(4, 14);
        $okayThings = $squirrel3->rngNextInt(6, 16);
        $goodThings = $squirrel3->rngNextInt(0, 12);
        $weirdThings = $squirrel3->rngNextInt(0, 10);
        $rareThings = $squirrel3->rngNextInt(1, 5);

        for($i = 0; $i < $lameThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::LAME_SHIT);

        for($i = 0; $i < $okayThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::OKAY_STUFF);

        for($i = 0; $i < $goodThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::GOOD_STUFF);

        for($i = 0; $i < $weirdThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::WEIRD_STUFF);

        for($i = 0; $i < $rareThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::RARE_STUFF);

        $noteworthy = [ $squirrel3->rngNextFromArray($items), $squirrel3->rngNextFromArray($items) ];

        shuffle($noteworthy);

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $em->remove($inventory);

        foreach($items as $itemName)
            $inventoryService->receiveItem($itemName, $user, $createdBy, 'Found inside a ' . $inventory->getItem()->getName() . '.', $location, $lockedToOwner);

        $em->flush();

        if($noteworthy[0] === $noteworthy[1])
            return $responseService->itemActionSuccess('You open the Baabble, and, like, ' . count($items) . ' items come flying out! ' . $noteworthy[0] . ' among them!', [ 'itemDeleted' => true ]);
        else
            return $responseService->itemActionSuccess('You open the Baabble, and, like, ' . count($items) . ' items come flying out! ' . $noteworthy[0] . ', and ' . $noteworthy[1] . ', among them!', [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/shiny/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openShinyBaabble(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, UserStatsRepository $userStatsRepository, Squirrel3 $squirrel3
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'baabble/shiny/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();
        $createdBy = $inventory->getCreatedBy();

        $items = [];

        $lameThings = $squirrel3->rngNextInt(0, 12);
        $okayThings = $squirrel3->rngNextInt(4, 16);
        $goodThings = $squirrel3->rngNextInt(4, 16);
        $weirdThings = $squirrel3->rngNextInt(4, 14);
        $rareThings = $squirrel3->rngNextInt(3, 7);

        for($i = 0; $i < $lameThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::LAME_SHIT);

        for($i = 0; $i < $okayThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::OKAY_STUFF);

        for($i = 0; $i < $goodThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::GOOD_STUFF);

        for($i = 0; $i < $weirdThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::WEIRD_STUFF);

        for($i = 0; $i < $rareThings; $i++)
            $items[] = $squirrel3->rngNextFromArray(self::RARE_STUFF);

        $noteworthy = [ $squirrel3->rngNextFromArray($items), $squirrel3->rngNextFromArray($items) ];

        shuffle($noteworthy);

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $em->remove($inventory);

        foreach($items as $itemName)
            $inventoryService->receiveItem($itemName, $user, $createdBy, 'Found inside a ' . $inventory->getItem()->getName() . '.', $location, $lockedToOwner);

        $em->flush();

        if($noteworthy[0] === $noteworthy[1])
            return $responseService->itemActionSuccess('You open the Baabble, and, like, ' . count($items) . ' items come flying out! ' . $noteworthy[0] . ' among them!', [ 'itemDeleted' => true ]);
        else
            return $responseService->itemActionSuccess('You open the Baabble, and, like, ' . count($items) . ' items come flying out! ' . $noteworthy[0] . ', and ' . $noteworthy[1] . ', among them!', [ 'itemDeleted' => true ]);
    }
}
