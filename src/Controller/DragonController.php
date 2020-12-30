<?php
namespace App\Controller;

use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Functions\ArrayFunctions;
use App\Repository\DragonRepository;
use App\Repository\EnchantmentRepository;
use App\Repository\InventoryRepository;
use App\Repository\SpiceRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dragon")
 */
class DragonController extends PoppySeedPetsController
{
    // currently sums to 75
    private const SILVER_GOODIES = [
        [ 'weight' => 30, 'item' => 'Liquid-hot Magma' ], // 40%
        [ 'weight' => 15, 'item' => 'Quintessence' ], // 20%
        [ 'weight' => 15, 'item' => 'Charcoal' ], // 20%

        // 20% chance of one of these:
        [ 'weight' => 5, 'item' => 'Magpie Pouch' ],
        [ 'weight' => 5, 'item' => 'Fruits & Veggies Box', 'spice' => 'Well-done' ],
        [ 'weight' => 5, 'item' => 'Handicrafts Supply Box' ],
    ];

    // currently sums to 100 - handy!
    private const GOLD_GOODIES = [
        [ 'weight' => 20, 'item' => 'Liquid-hot Magma' ], // 20%
        [ 'weight' => 20, 'item' => 'Tiny Scroll of Resources' ],
        [ 'weight' => 10, 'item' => 'Dark Matter' ],
        [ 'weight' => 10, 'item' => 'Raccoon Pouch', 'spice' => 'Well-done' ],
        [ 'weight' => 10, 'item' => 'Rock' ],
        [ 'weight' => 10, 'item' => 'Burnt Log' ],

        // 20% chance of a burnt, iron tool:
        [ 'weight' => 10, 'item' => 'Iron Sword', 'bonus' => 'Burnt' ],
        [ 'weight' => 5, 'item' => 'Dumbbell', 'bonus' => 'Burnt' ],
        [ 'weight' => 5, 'item' => 'Flute', 'bonus' => 'Burnt' ],
    ];

    // currently sums to 75
    private const GEM_GOODIES = [
        [ 'weight' => 20, 'item' => 'Scroll of Resources' ], // 26.6%
        [ 'weight' => 10, 'item' => 'Liquid-hot Magma' ], // 13.3%
        [ 'weight' => 10, 'item' => 'Firestone' ],
        [ 'weight' => 10, 'item' => 'Lightning in a Bottle' ],
        // ^ 2/3 chance of being one of those

        // 1/3 chance of being one of these:
        [ 'weight' => 5, 'item' => 'Box of Ores' ],
        [ 'weight' => 5, 'item' => 'Secret Seashell' ],
        [ 'weight' => 5, 'item' => 'Scroll of Resources' ],
        [ 'weight' => 5, 'item' => 'Stereotypical Bone' ],
        [ 'weight' => 3, 'item' => 'Rib' ],
        [ 'weight' => 2, 'item' => 'Dino Skull' ],
    ];

    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getDragon(ResponseService $responseService, DragonRepository $dragonRepository)
    {
        $user = $this->getUser();

        $dragon = $dragonRepository->findAdult($user);

        if(!$dragon)
            throw new NotFoundHttpException('You don\'t have an adult dragon!');

        return $responseService->success($dragon, SerializationGroupEnum::MY_DRAGON);
    }

    /**
     * @Route("/offerings", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getOffer(
        ResponseService $responseService, DragonRepository $dragonRepository, InventoryRepository $inventoryRepository
    )
    {
        $user = $this->getUser();

        $dragon = $dragonRepository->findAdult($user);

        if(!$dragon)
            throw new NotFoundHttpException('You don\'t have an adult dragon!');

        $treasures = $inventoryRepository->findTreasures($user);

        return $responseService->success($treasures, SerializationGroupEnum::DRAGON_TREASURE);
    }

    /**
     * @Route("/giveTreasure", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function giveTreasure(
        ResponseService $responseService, DragonRepository $dragonRepository, InventoryRepository $inventoryRepository,
        Request $request, EntityManagerInterface $em, InventoryService $inventoryService,
        EnchantmentRepository $enchantmentRepository, SpiceRepository $spiceRepository
    )
    {
        $user = $this->getUser();

        $dragon = $dragonRepository->findAdult($user);

        if(!$dragon)
            throw new NotFoundHttpException('You don\'t have an adult dragon!');

        if(!$request->request->has('treasure'))
            throw new UnprocessableEntityHttpException('No items were selected to give???');

        $itemIds = $request->request->get('treasure');

        if(!is_array($itemIds)) $itemIds = [ $itemIds ];

        $items = $inventoryRepository->findTreasuresById($user, $itemIds);

        if(count($items) < count($itemIds))
            throw new UnprocessableEntityHttpException('Some of the treasures selected... maybe don\'t exist!? That shouldn\'t happen. Reload and try again.');

        $silver = ArrayFunctions::sum($items, function(Inventory $i) { return $i->getItem()->getTreasure()->getSilver(); });
        $gold = ArrayFunctions::sum($items, function(Inventory $i) { return $i->getItem()->getTreasure()->getGold(); });
        $gems = ArrayFunctions::sum($items, function(Inventory $i) { return $i->getItem()->getTreasure()->getGems(); });

        foreach($items as $item)
            $em->remove($item);

        $goodies = [];

        if($silver > 0)
        {
            $dragon->increaseSilver($silver);

            for($i = 0; $i < $silver; $i++)
                $goodies[] = ArrayFunctions::pick_one_weighted(self::SILVER_GOODIES, function($i) { return $i['weight']; });
        }

        if($gold > 0)
        {
            $dragon->increaseGold($gold);

            for($i = 0; $i < $gold; $i++)
                $goodies[] = ArrayFunctions::pick_one_weighted(self::GOLD_GOODIES, function($i) { return $i['weight']; });
        }

        if($gems > 0)
        {
            $dragon->increaseGems($gems);

            for($i = 0; $i < $gems; $i++)
                $goodies[] = ArrayFunctions::pick_one_weighted(self::GEM_GOODIES, function($i) { return $i['weight']; });
        }

        foreach($goodies as $goody)
        {
            $newItem = $inventoryService->receiveItem($goody['item'], $user, $user, $user->getName() . ' received this from their dragon, ' . $dragon->getName() . '.', LocationEnum::HOME);

            if(array_key_exists('bonus', $goody)) $newItem->setEnchantment($enchantmentRepository->findOneByName($goody['bonus']));
            if(array_key_exists('spice', $goody)) $newItem->setSpice($spiceRepository->findOneByName($goody['spice']));
        }

        $em->flush();

        $itemNames = array_map(function($goodie) { return $goodie['item']; }, $goodies);
        sort($itemNames);

        $responseService->addFlashMessage($dragon->getName() . ' thanks you for your gift, and gives you ' . ArrayFunctions::list_nice($itemNames) . ' in exchange.');

        return $responseService->success($dragon, SerializationGroupEnum::MY_DRAGON);
    }
}
