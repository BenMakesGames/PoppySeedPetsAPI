<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Functions\ArrayFunctions;
use App\Repository\SpiceRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/gnomesFavor")
 */
class GnomesFavorController extends PoppySeedPetsItemController
{
    private const USER_STAT_NAME = 'Redeemed a Gnome\'s Favor';

    private const GNOMISH_MAGIC = [
        ' (Whoa! Magic!)',
        ' (Gnomish magic!)',
        ' #justgnomethings',
        ' (Smells... _gnomish_...)',
        ' (Ooh! Sparkly!)',
    ];

    /**
     * @Route("/{inventory}/quint", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getQuint(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, Squirrel3 $rng, UserStatsRepository $userStatsRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'gnomesFavor/#/quint');
        $this->validateHouseSpace($inventory, $inventoryService);

        $location = $inventory->getLocation();

        for($i = 0; $i < 2; $i++)
            $inventoryService->receiveItem('Quintessence', $user, $user, $user->getName() . ' got this from a Gnome\'s Favor.', $location);

        $userStatsRepository->incrementStat($user, self::USER_STAT_NAME);

        $em->remove($inventory);

        $em->flush();

        $extraSilliness = $rng->rngNextFromArray(self::GNOMISH_MAGIC);

        return $responseService->itemActionSuccess('Two Quintessence materialize in front of you with a flash! ' . $extraSilliness, [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/food", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getFood(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, Squirrel3 $squirrel3, SpiceRepository $spiceRepository,
        UserStatsRepository $userStatsRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'gnomesFavor/#/food');
        $this->validateHouseSpace($inventory, $inventoryService);

        $location = $inventory->getLocation();

        $possibleSpices = [
            'Rain-scented',
            'Juniper',
            'with Rosemary',
            'with Toad Jelly',
            'Cheesy',
        ];

        shuffle($possibleSpices);

        $possibleFood = [
            'Cheese',
            'Fisherman\'s Pie',
            'Poutine',
            'Stargazy Pie',
            '15-bean Soup',
            'Brownie',
            'Pumpkin Custard',
        ];

        shuffle($possibleFood);

        for($i = 0; $i < 5; $i++)
        {
            $newInventory[] = $inventoryService->receiveItem($possibleFood[$i], $user, $user, $user->getName() . ' got this from a Gnome\'s Favor.', $location)
                ->setSpice($spiceRepository->findOneByName($possibleSpices[$i]))
            ;
        }

        $userStatsRepository->incrementStat($user, self::USER_STAT_NAME);

        $em->remove($inventory);

        $em->flush();

        $itemList = array_map(fn(Inventory $i) => $i->getFullItemName(), $newInventory);
        sort($itemList);

        $extraSilliness = $squirrel3->rngNextFromArray(self::GNOMISH_MAGIC);

        return $responseService->itemActionSuccess(ArrayFunctions::list_nice($itemList) . ' materialize in front of you with a flash! ' . $extraSilliness, [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/treasure", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getTreasure(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, Squirrel3 $rng, UserStatsRepository $userStatsRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'gnomesFavor/#/treasure');

        $location = $inventory->getLocation();

        $possibleItems = [
            'Silver Bar', 'Silver Bar', 'Silver Bar', 'Silver Bar', 'Silver Bar',
            'Gold Bar', 'Gold Bar', 'Gold Bar',
            'Blue Bow',
            'Key Ring',
            'Hourglass',
            'Spice Rack',
            'Sand Dollar',
            $rng->rngNextFromArray([ 'Password', 'Cryptocurrency Wallet' ]),
        ];

        shuffle($possibleItems);

        $newInventory = [];

        for($i = 0; $i < 3; $i++)
            $newInventory[] = $inventoryService->receiveItem($possibleItems[$i], $user, $user, $user->getName() . ' got this from a Gnome\'s Favor.', $location);

        $userStatsRepository->incrementStat($user, self::USER_STAT_NAME);

        $em->remove($inventory);

        $em->flush();

        $itemList = array_map(fn(Inventory $i) => $i->getFullItemName(), $newInventory);
        sort($itemList);

        $extraSilliness = $rng->rngNextFromArray(self::GNOMISH_MAGIC);

        return $responseService->itemActionSuccess(ArrayFunctions::list_nice($itemList) . ' materialize in front of you with a flash! ' . $extraSilliness, [ 'itemDeleted' => true ]);
    }
}
