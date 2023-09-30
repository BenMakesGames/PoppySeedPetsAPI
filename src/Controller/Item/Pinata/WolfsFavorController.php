<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Functions\ArrayFunctions;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/wolfsFavor")
 */
class WolfsFavorController extends AbstractController
{
    private const USER_STAT_NAME = 'Redeemed a Wolf\'s Favor';

    /**
     * @Route("/{inventory}/furAndClaw", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getFluffAndTalons(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng, UserStatsRepository $userStatsRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'wolfsFavor/#/furAndClaw');
        ItemControllerHelpers::validateHouseSpace($inventory, $inventoryService);

        $location = $inventory->getLocation();

        $loot = [
            'Fluff', 'Fluff', 'Fluff', 'Fluff', 'Fluff', 'Fluff', 'Fluff',
            'Talon', 'Talon', 'Talon',
            $rng->rngNextFromArray([
                'Rib', 'Stereotypical Bone',
            ]),
            $rng->rngNextFromArray([
                'Hot Dog', 'Bulbun Plushy'
            ])
        ];

        foreach($loot as $item)
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from a Wolf\'s Favor.', $location);

        $userStatsRepository->incrementStat($user, self::USER_STAT_NAME);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('A fluffy pupper drops ' . ArrayFunctions::list_nice($loot) . ' off just outside your door, and bounds off into the distance.', [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/theMoon", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getMoonStuff(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng, UserStatsRepository $userStatsRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'wolfsFavor/#/theMoon');

        $location = $inventory->getLocation();

        $loot = [
            'Moon Pearl', 'Moon Pearl', 'Moon Pearl', 'Moon Pearl',
            'Moon Dust', 'Moon Dust',
            'Moth',
            'Meteorite',
            $rng->rngNextFromArray([
                'Hot Dog', 'Bulbun Plushy'
            ])
        ];

        foreach($loot as $item)
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from a Wolf\'s Favor.', $location);

        $userStatsRepository->incrementStat($user, self::USER_STAT_NAME);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('A fluffy pupper drops ' . ArrayFunctions::list_nice($loot) . ' off just outside your door, and bounds off into the distance.', [ 'itemDeleted' => true ]);
    }
}
