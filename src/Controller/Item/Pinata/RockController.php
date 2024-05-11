<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item")]
class RockController extends AbstractController
{
    #[Route("/rock/{rock}/smash", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function smash(
        Inventory $rock, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        UserStatsService $userStatsRepository, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $rock, 'rock/#/smash');
        ItemControllerHelpers::validateLocationSpace($rock, $em);

        $location = $rock->getLocation();
        $lockedToOwner = $rock->getLockedToOwner();

        $ores = [
            'Iron Ore', 'Iron Ore', 'Iron Ore', 'Iron Ore', 'Iron Ore', 'Iron Ore', 'Iron Ore', // 7
            'Silver Ore', 'Silver Ore', 'Silver Ore', 'Silver Ore', // 4
            'Gold Ore', 'Gold Ore', 'Gold Ore', // 3
        ];

        $extraItems = array_merge($ores, [
            'Liquid-hot Magma', 'Liquid-hot Magma',

            'Baking Soda',
            'Silica Grounds',
            'Rock',
            'Rock Candy',
            'Secret Seashell',
            'Striped Microcline',
        ]);

        $userStatsRepository->incrementStat($user, 'Smashed Open a ' . $rock->getItem()->getName());

        $ore = $squirrel3->rngNextFromArray($ores);
        $extraItem = $squirrel3->rngNextFromArray($extraItems);

        $inventoryService->receiveItem($ore, $user, $rock->getCreatedBy(), 'Found inside a Rock.', $location, $lockedToOwner);
        $inventoryService->receiveItem($extraItem, $user, $rock->getCreatedBy(), 'Found inside a Rock.', $location, $lockedToOwner);

        $em->remove($rock);

        $em->flush();

        $message = 'Smashing open the rock revealed ' . $ore . ', and ' . $extraItem;

        if($extraItem === 'Liquid-hot Magma')
            $message .= '! (Whoa! Dangerous!)';
        else
            $message .= '.';

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }

    #[Route("/pommegranite/{rock}/smash", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openPommegranite(
        Inventory $rock, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        UserStatsService $userStatsRepository, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $rock, 'pommegranite/#/smash');
        ItemControllerHelpers::validateLocationSpace($rock, $em);

        $location = $rock->getLocation();
        $lockedToOwner = $rock->getLockedToOwner();

        $userStatsRepository->incrementStat($user, 'Smashed Open a ' . $rock->getItem()->getName());

        if($rng->rngNextInt(1, 3) === 1)
        {
            $inventoryService->receiveItem('Rock', $user, $rock->getCreatedBy(), 'Found inside a Pommegranite.', $location, $lockedToOwner);
            $message = 'Smashing open the Pommegranite revealed a Rock, and ';
        }
        else
        {
            $inventoryService->receiveItem('Silica Grounds', $user, $rock->getCreatedBy(), 'Found inside a Pommegranite.', $location, $lockedToOwner);
            $message = 'Smashing open the Pommegranite revealed some Silica Grounds, and ';
        }

        if($rng->rngNextInt(1, 3) === 1)
        {
            $inventoryService->receiveItem('Potato', $user, $rock->getCreatedBy(), 'Found inside a Pommegranite.', $location, $lockedToOwner);
            $message .= 'a Pomme de Ter-- er, I mean, Potato!';
        }
        else
        {
            $inventoryService->receiveItem('Red', $user, $rock->getCreatedBy(), 'Found inside a Pommegranite.', $location, $lockedToOwner);
            $inventoryService->receiveItem('Red', $user, $rock->getCreatedBy(), 'Found inside a Pommegranite.', $location, $lockedToOwner);
            $message .= 'a couple Pommes-- er, I mean, App-- ER! I MEAN: REDS! A couple Reds! (Why does this game have to be so confusing all the time!)';
        }

        $em->remove($rock);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
