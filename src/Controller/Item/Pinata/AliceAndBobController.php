<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Repository\EnchantmentRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @Route("/item/box")
 */
class AliceAndBobController extends AbstractController
{
    #[Route("/alicesSecret/{inventory}/teaTime", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function alicesSecretTeaTime(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em, IRandom $squirrel3,
        UserStatsService $userStatsRepository, ResponseService $responseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/alicesSecret/#/teaTime');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $loot = [
            'Toadstool', 'Shortbread Cookies'
        ];

        for($i = 0; $i < 3; $i++)
        {
            $loot[] = $squirrel3->rngNextFromArray([
                'Coffee Bean Tea with Mammal Extract',
                'Ginger Tea',
                'Black Tea',
                'Sweet Tea with Mammal Extract',
            ]);
        }

        for($i = 0; $i < 2; $i++)
        {
            if($squirrel3->rngNextInt(1, 5) === 1)
            {
                $loot[] = $squirrel3->rngNextFromArray([
                    'Dreamwalker\'s Tea', 'Yogurt Muffin',
                ]);
            }
            else
            {
                $loot[] = $squirrel3->rngNextFromArray([
                    'Toadstool', 'Mini Chocolate Chip Cookies', 'Pumpkin Bread',
                ]);
            }
        }

        $newInventory = [];

        foreach($loot as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from Alice\'s Secret.', $inventory->getLocation(), $inventory->getLockedToOwner());

        return BoxHelpers::countRemoveFlushAndRespond('Inside Alice\'s Secret, you find', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/alicesSecret/{inventory}/hourglass", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function alicesSecretHourglass(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        UserStatsService $userStatsRepository, ResponseService $responseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/alicesSecret/#/hourglass');

        $item = $inventoryService->receiveItem('Hourglass', $user, $user, $user->getName() . ' got this from Alice\'s Secret.', $inventory->getLocation(), $inventory->getLockedToOwner());

        return BoxHelpers::countRemoveFlushAndRespond('Inside Alice\'s Secret, you find', $userStatsRepository, $user, $inventory, [ $item ], $responseService, $em);
    }

    #[Route("/alicesSecret/{inventory}/cards", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function alicesSecretCards(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em, IRandom $squirrel3,
        UserStatsService $userStatsRepository, ResponseService $responseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/alicesSecret/#/cards');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $loot = [
            'Paper', 'Paper', 'Paper', 'Paper', $squirrel3->rngNextFromArray([ 'Paper', 'Quinacridone Magenta Dye' ])
        ];

        $newInventory = [];

        foreach($loot as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from Alice\'s Secret.', $inventory->getLocation(), $inventory->getLockedToOwner());

        return BoxHelpers::countRemoveFlushAndRespond('Inside Alice\'s Secret, you find some cards? Oh, wait, no: it\'s just', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/bobsSecret/{inventory}/fish", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function bobsSecretFish(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em, IRandom $squirrel3,
        UserStatsService $userStatsRepository, ResponseService $responseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/bobsSecret/#/fish');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $loot = [
            'Fish',
            'Fish',
            'Scales'
        ];

        for($i = 0; $i < 3; $i++)
        {
            if($squirrel3->rngNextInt(1, 5) === 1)
            {
                $loot[] = $squirrel3->rngNextFromArray([
                    'Sand Dollar', 'Tentacle',
                ]);
            }
            else
            {
                $loot[] = $squirrel3->rngNextFromArray([
                    'Fish', 'Scales',
                ]);
            }
        }

        $newInventory = [];

        foreach($loot as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from Bob\'s Secret.', $inventory->getLocation(), $inventory->getLockedToOwner());

        return BoxHelpers::countRemoveFlushAndRespond('Inside Bob\'s Secret, you find', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

    #[Route("/bobsSecret/{inventory}/tool", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function bobsTool(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em, IRandom $squirrel3,
        UserStatsService $userStatsRepository, ResponseService $responseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/bobsSecret/#/tool');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        // apply "Bob's" bonus
        $tool = $squirrel3->rngNextFromArray([
            'Iron Tongs',
            'Garden Shovel',
            'Crooked Fishing Rod',
            'Yellow Scissors',
            'Small Plastic Bucket',
            'Straw Broom',
        ]);

        $item = $inventoryService->receiveItem($tool, $user, $user, $user->getName() . ' got this from Bob\'s Secret.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $item->setEnchantment(
            EnchantmentRepository::findOneByName($em, 'Bob\'s')
        );

        return BoxHelpers::countRemoveFlushAndRespond('Inside Bob\'s Secret, you find', $userStatsRepository, $user, $inventory, [ $item ], $responseService, $em);
    }

    #[Route("/bobsSecret/{inventory}/bbq", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function bobsBBQ(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        UserStatsService $userStatsRepository, ResponseService $responseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/bobsSecret/#/bbq');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $loot = [
            'Charcoal',
            'Hot Dog',
            'Grilled Fish',
            'Tomato Ketchup',
            'Hot Potato'
        ];

        $newInventory = [];

        foreach($loot as $item)
            $newInventory[] = $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from Bob\'s Secret.', $inventory->getLocation(), $inventory->getLockedToOwner());

        return BoxHelpers::countRemoveFlushAndRespond('Inside Bob\'s Secret, you find', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }
}
