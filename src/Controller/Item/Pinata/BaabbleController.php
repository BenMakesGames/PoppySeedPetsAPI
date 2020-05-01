<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Model\PetChanges;
use App\Repository\InventoryRepository;
use App\Repository\PetRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/baabble")
 */
class BaabbleController extends PoppySeedPetsItemController
{
    private const LAME_SHIT = [
        'Crooked Stick',
        'Scales', 'Tea Leaves', 'Aging Powder', 'Fluff', 'Pointer', 'Creamy Milk', 'Silica Grounds',
    ];

    private const OKAY_STUFF = [
        'Crooked Stick',
        'Iron Ore',
        'Plastic', 'Plastic',
        'Carrot', 'Paper', 'Talon', 'Feathers', 'Glue',
    ];

    private const GOOD_STUFF = [
        'Quintessence', 'Quintessence',
        'Iron Ore', 'Iron Ore',
        'Silver Ore', 'Silver Ore',
        'Gold Ore',
        'Dark Scales', 'Hash Table', 'Paper Bag', 'Finite State Machine',
    ];

    private const WEIRD_STUFF = [
        'Really Big Leaf',
        'Music Note', 'Music Note',
        'White Cloth',
        'Dark Matter', 'Coriander Flower', 'Charcoal', 'Moon Pearl',
        'XOR', 'Liquid-hot Magma', 'Quinacridone Magenta Dye', 'Gypsum', 'Tiny Black Hole',
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
        EntityManagerInterface $em, UserStatsRepository $userStatsRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'baabble/black/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();
        $createdBy = $inventory->getCreatedBy();

        $items = [];

        $lameThings = mt_rand(2, 8);
        $okayThings = mt_rand(7, 17);
        $goodThings = mt_rand(0, 9);

        for($i = 0; $i < $lameThings; $i++)
            $items[] = ArrayFunctions::pick_one(self::LAME_SHIT);

        for($i = 0; $i < $okayThings; $i++)
            $items[] = ArrayFunctions::pick_one(self::OKAY_STUFF);

        for($i = 0; $i < $goodThings; $i++)
            $items[] = ArrayFunctions::pick_one(self::GOOD_STUFF);

        $weirdItem = ArrayFunctions::pick_one(self::WEIRD_STUFF);

        $items[] = $weirdItem;

        $noteworthy = [ ArrayFunctions::pick_one($items), $weirdItem ];

        shuffle($noteworthy);

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $em->remove($inventory);

        foreach($items as $itemName)
            $inventoryService->receiveItem($itemName, $user, $createdBy, 'Found inside a ' . $inventory->getItem()->getName() . '.', $location, $lockedToOwner);

        $em->flush();

        return $responseService->itemActionSuccess('You open the Baabble, and, like, ' . count($items) . ' items come flying out! ' . $noteworthy[0] . ', and ' . $noteworthy[1] . ', among them!', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/white/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openWhiteBaabble(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, UserStatsRepository $userStatsRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'baabble/white/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();
        $createdBy = $inventory->getCreatedBy();

        $items = [];

        $lameThings = mt_rand(3, 11);
        $okayThings = mt_rand(10, 18);
        $goodThings = mt_rand(0, 9);
        $rareThings = mt_rand(1, 3);

        for($i = 0; $i < $lameThings; $i++)
            $items[] = ArrayFunctions::pick_one(self::LAME_SHIT);

        for($i = 0; $i < $okayThings; $i++)
            $items[] = ArrayFunctions::pick_one(self::OKAY_STUFF);

        for($i = 0; $i < $goodThings; $i++)
            $items[] = ArrayFunctions::pick_one(self::GOOD_STUFF);

        for($i = 0; $i < $rareThings; $i++)
            $items[] = ArrayFunctions::pick_one(self::RARE_STUFF);

        $weirdItem = ArrayFunctions::pick_one(self::WEIRD_STUFF);

        $items[] = $weirdItem;

        $noteworthy = [ ArrayFunctions::pick_one($items), $weirdItem ];

        shuffle($noteworthy);

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $em->remove($inventory);

        foreach($items as $itemName)
            $inventoryService->receiveItem($itemName, $user, $createdBy, 'Found inside a ' . $inventory->getItem()->getName() . '.', $location, $lockedToOwner);

        $em->flush();

        return $responseService->itemActionSuccess('You open the Baabble, and, like, ' . count($items) . ' items come flying out! ' . $noteworthy[0] . ', and ' . $noteworthy[1] . ', among them!', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/gold/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openGoldBaabble(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, UserStatsRepository $userStatsRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'baabble/gold/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();
        $createdBy = $inventory->getCreatedBy();

        $items = [];

        $lameThings = mt_rand(3, 13);
        $okayThings = mt_rand(6, 16);
        $goodThings = mt_rand(0, 10);
        $weirdThings = mt_rand(0, 10);
        $rareThings = mt_rand(2, 8);

        for($i = 0; $i < $lameThings; $i++)
            $items[] = ArrayFunctions::pick_one(self::LAME_SHIT);

        for($i = 0; $i < $okayThings; $i++)
            $items[] = ArrayFunctions::pick_one(self::OKAY_STUFF);

        for($i = 0; $i < $goodThings; $i++)
            $items[] = ArrayFunctions::pick_one(self::GOOD_STUFF);

        for($i = 0; $i < $weirdThings; $i++)
            $items[] = ArrayFunctions::pick_one(self::WEIRD_STUFF);

        for($i = 0; $i < $rareThings; $i++)
            $items[] = ArrayFunctions::pick_one(self::RARE_STUFF);

        $noteworthy = [ ArrayFunctions::pick_one($items), ArrayFunctions::pick_one($items) ];

        shuffle($noteworthy);

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $em->remove($inventory);

        foreach($items as $itemName)
            $inventoryService->receiveItem($itemName, $user, $createdBy, 'Found inside a ' . $inventory->getItem()->getName() . '.', $location, $lockedToOwner);

        $em->flush();

        if($noteworthy[0] === $noteworthy[1])
            return $responseService->itemActionSuccess('You open the Baabble, and, like, ' . count($items) . ' items come flying out! ' . $noteworthy[0] . ', and ' . $noteworthy[1] . ', among them! (Wait: was that two different ones, or the same one?)', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
        else
            return $responseService->itemActionSuccess('You open the Baabble, and, like, ' . count($items) . ' items come flying out! ' . $noteworthy[0] . ', and ' . $noteworthy[1] . ', among them!', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/shiny/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openShinyBaabble(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, UserStatsRepository $userStatsRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'baabble/shiny/#/open');
        $this->validateHouseSpace($inventory, $inventoryService);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();
        $createdBy = $inventory->getCreatedBy();

        $items = [];

        $lameThings = mt_rand(0, 8);
        $okayThings = mt_rand(4, 14);
        $goodThings = mt_rand(4, 14);
        $weirdThings = mt_rand(4, 14);
        $rareThings = mt_rand(4, 14);

        for($i = 0; $i < $lameThings; $i++)
            $items[] = ArrayFunctions::pick_one(self::LAME_SHIT);

        for($i = 0; $i < $okayThings; $i++)
            $items[] = ArrayFunctions::pick_one(self::OKAY_STUFF);

        for($i = 0; $i < $goodThings; $i++)
            $items[] = ArrayFunctions::pick_one(self::GOOD_STUFF);

        for($i = 0; $i < $weirdThings; $i++)
            $items[] = ArrayFunctions::pick_one(self::WEIRD_STUFF);

        for($i = 0; $i < $rareThings; $i++)
            $items[] = ArrayFunctions::pick_one(self::RARE_STUFF);

        $noteworthy = [ ArrayFunctions::pick_one($items), ArrayFunctions::pick_one($items) ];

        shuffle($noteworthy);

        $userStatsRepository->incrementStat($user, 'Opened a ' . $inventory->getItem()->getName());

        $em->remove($inventory);

        foreach($items as $itemName)
            $inventoryService->receiveItem($itemName, $user, $createdBy, 'Found inside a ' . $inventory->getItem()->getName() . '.', $location, $lockedToOwner);

        $em->flush();

        if($noteworthy[0] === $noteworthy[1])
            return $responseService->itemActionSuccess('You open the Baabble, and, like, ' . count($items) . ' items come flying out! ' . $noteworthy[0] . ', and ' . $noteworthy[1] . ', among them! (Wait: was that two different ones, or the same one?)', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
        else
            return $responseService->itemActionSuccess('You open the Baabble, and, like, ' . count($items) . ' items come flying out! ' . $noteworthy[0] . ', and ' . $noteworthy[1] . ', among them!', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}
