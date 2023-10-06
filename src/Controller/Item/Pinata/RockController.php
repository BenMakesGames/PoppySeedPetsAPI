<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/rock")
 */
class RockController extends AbstractController
{
    /**
     * @Route("/{rock}/smash", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function smash(
        Inventory $rock, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $rock, 'rock/#/smash');
        ItemControllerHelpers::validateHouseSpace($rock, $inventoryService);

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

        UserStatsRepository::incrementStat($em, $user, 'Smashed Open a ' . $rock->getItem()->getName());

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
}
