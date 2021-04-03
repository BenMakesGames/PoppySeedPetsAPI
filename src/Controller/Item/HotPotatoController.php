<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Service\HotPotatoService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/hotPotato")
 */
class HotPotatoController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/toss", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function toss(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService, Squirrel3 $squirrel3, HotPotatoService $hotPotatoService
    )
    {
        $this->validateInventory($inventory, 'hotPotato/#/toss');

        $user = $this->getUser();

        if($squirrel3->rngNextInt(1, 5) === 1)
        {
            $inventoryService->receiveItem('Smashed Potatoes', $user, $inventory->getCreatedBy(), 'The remains of an exploded Hot Potato.', $inventory->getLocation());
            $inventoryService->receiveItem('Liquid-hot Magma', $user, $inventory->getCreatedBy(), 'The remains of an exploded Hot Potato.', $inventory->getLocation());

            $thirdItem = $squirrel3->rngNextFromArray([
                'Charcoal',
                'Glowing Six-sided Die',
                $squirrel3->rngNextFromArray([ 'Oil', 'Butter' ]),
                $squirrel3->rngNextFromArray([ 'Sour Cream', 'Cheese' ]),
            ]);

            $inventoryService->receiveItem($thirdItem, $user, $inventory->getCreatedBy(), 'This exploded out of a Hot Potato.', $inventory->getLocation());

            $em->remove($inventory);
            $em->flush();

            return $responseService->itemActionSuccess('You get ready to toss the Hot Potato, but it explodes in your hands! It\'s a bit hot, but hey: you got Smashed Potatoes, Liquid-hot Magma, and ' . $thirdItem . '!', [ 'itemDeleted' => true ]);
        }
        else
        {
            return $hotPotatoService->tossItem($inventory);
        }
    }

    /**
     * @Route("/{inventory}/tossChocolateBomb", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function tossChocolateBomb(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService, Squirrel3 $squirrel3, HotPotatoService $hotPotatoService
    )
    {
        $this->validateInventory($inventory, 'hotPotato/#/tossChocolateBomb');

        $user = $this->getUser();

        $numberOfTosses = $hotPotatoService->countTosses($inventory);

        if($squirrel3->rngNextInt(1, 100) <= 10 + $numberOfTosses * 10)
        {
            $numberOfItems = 5 + $numberOfTosses;

            $loot = $squirrel3->rngNextSubsetFromArray([
                'Chocolate Bar',
                'Chocolate Bomb',
                'Chocolate Cake Pops',
                'Chocolate Chip Meringue',
                'Chocolate Chip Muffin',
                'Chocolate Ice Cream',
                'Chocolate Key',
                'Chocolate Meringue',
                'Chocolate Syrup',
                'Chocolate Toffee Matzah',
                'Chocolate-covered Honeycomb',
                'Chocolate-covered Naner',
                'Chocolate-frosted Donut',
                'Mini Chocolate Chip Cookies',
                'Orange Chocolate Bar',
                'Slice of Chocolate Cream Pie',
                'Spicy Chocolate Bar'
            ], $numberOfItems);

            foreach($loot as $itemName)
                $inventoryService->receiveItem($itemName, $user, $inventory->getCreatedBy(), 'This exploded out of a Chocolate Bomb.', $inventory->getLocation(), $itemName === 'Chocolate Bomb');

            $em->remove($inventory);
            $em->flush();

            return $responseService->itemActionSuccess('You get ready to toss the Chocolate Bomb, but it explodes in your hands; ' . $numberOfItems . ' chocolately items fly out!', [ 'itemDeleted' => true ]);
        }
        else
        {
            return $hotPotatoService->tossItem($inventory);
        }
    }
}
