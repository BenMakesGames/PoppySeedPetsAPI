<?php
namespace App\Controller;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Repository\DragonRepository;
use App\Repository\EnchantmentRepository;
use App\Repository\InventoryRepository;
use App\Repository\SpiceRepository;
use App\Repository\UserStatsRepository;
use App\Service\CalendarService;
use App\Service\InventoryService;
use App\Service\PetActivity\TreasureMapService;
use App\Service\PetAssistantService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
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

        return $responseService->success($dragon, [
            SerializationGroupEnum::MY_DRAGON,
            SerializationGroupEnum::HELPER_PET,
        ]);
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

        return $responseService->success($treasures, [ SerializationGroupEnum::DRAGON_TREASURE ]);
    }

    /**
     * @Route("/giveTreasure", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function giveTreasure(
        ResponseService $responseService, DragonRepository $dragonRepository, InventoryRepository $inventoryRepository,
        Request $request, EntityManagerInterface $em, InventoryService $inventoryService,
        EnchantmentRepository $enchantmentRepository, SpiceRepository $spiceRepository,
        UserStatsRepository $userStatsRepository, CalendarService $calendarService, Squirrel3 $rng,
        TreasureMapService $treasureMapService
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

        $userStatsRepository->incrementStat($user, UserStatEnum::TREASURES_GIVEN_TO_DRAGON_HOARD, count($items));

        $silverGoodies = self::SILVER_GOODIES;
        $goldGoodies = self::GOLD_GOODIES;
        $gemGoodies = self::GEM_GOODIES;

        if($calendarService->isValentinesOrAdjacent())
        {
            $silverGoodies[] = [ 'weight' => 10, 'item' => 'Cocoa Beans' ];
            $goldGoodies[] = [ 'weight' => 10, 'item' => 'Chocolate Bar' ];
            $gemGoodies[] = [ 'weight' => 10, 'item' => 'Chocolate Key' ];
        }

        $chineseCalendarInfo = $calendarService->getChineseCalendarInfo();

        if($chineseCalendarInfo->month === 1 && $chineseCalendarInfo->day <= 6)
        {
            $silverGoodies[] = [ 'weight' => 10, 'item' => 'Mooncake' ];
            $goldGoodies[] = [ 'weight' => 10, 'item' => 'Mooncake' ];
            $gemGoodies[] = [ 'weight' => 10, 'item' => 'Mooncake' ];
        }

        $goodies = [];

        if($silver > 0)
        {
            $dragon->increaseSilver($silver);

            for($i = 0; $i < $silver; $i++)
                $goodies[] = ArrayFunctions::pick_one_weighted($silverGoodies, function($i) { return $i['weight']; });
        }

        if($gold > 0)
        {
            $dragon->increaseGold($gold);

            for($i = 0; $i < $gold; $i++)
                $goodies[] = ArrayFunctions::pick_one_weighted($goldGoodies, function($i) { return $i['weight']; });
        }

        if($gems > 0)
        {
            $dragon->increaseGems($gems);

            for($i = 0; $i < $gems; $i++)
                $goodies[] = ArrayFunctions::pick_one_weighted($gemGoodies, function($i) { return $i['weight']; });
        }

        foreach($goodies as $goody)
        {
            $newItem = $inventoryService->receiveItem($goody['item'], $user, $user, $user->getName() . ' received this from their dragon, ' . $dragon->getName() . '.', LocationEnum::HOME);

            if(array_key_exists('bonus', $goody)) $newItem->setEnchantment($enchantmentRepository->findOneByName($goody['bonus']));
            if(array_key_exists('spice', $goody)) $newItem->setSpice($spiceRepository->findOneByName($goody['spice']));
        }

        $totalMoneys = 0;
        $extraItem = null;

        if($dragon->getHelper())
        {
            $helper = $dragon->getHelper();
            $helperSkills = $helper->getComputedSkills();

            $businessSkill = $helperSkills->getIntelligence()->getTotal() +
                ($helper->hasMerit(MeritEnum::EIDETIC_MEMORY) ? 3 : 0) +
                ($helper->hasMerit(MeritEnum::GREGARIOUS) ? 1 : 0) +
                ($helper->hasMerit(MeritEnum::LUCKY) ? 1 : 0)
            ;

            $moneysMultiplier = $gems * 0.5 + $gold * 0.35 + $silver * 0.25;
            $workMultiplier = $gems * 3 + $gold * 2 + $silver;

            $dragon
                ->addEarnings($businessSkill * $moneysMultiplier)
                ->addByproductProgress(($businessSkill + 4) * $workMultiplier)
            ;

            if($dragon->getEarnings() > 0)
            {
                $totalMoneys = (int)$dragon->getEarnings();
                $dragon->addEarnings(-$totalMoneys);

                $user->increaseMoneys($totalMoneys);
            }

            if($dragon->getByproductProgress() >= 100)
            {
                $dragon->addByproductProgress(-100);

                $possibleItems = $treasureMapService->getFluffmongerFlavorFoods($helper->getFavoriteFlavor());

                if($helperSkills->getNature()->getTotal() >= 5)
                    $possibleItems[] = 'Large Bag of Fertilizer';

                if($helperSkills->getScience()->getTotal() >= 5)
                    $possibleItems[] = 'Space Junk';

                if($helperSkills->getUmbra()->getTotal() >= 5 || $helper->hasMerit(MeritEnum::NATURAL_CHANNEL))
                    $possibleItems[] = 'Quintessence';

                if($helper->hasMerit(MeritEnum::LOLLIGOVORE))
                    $possibleItems[] = 'Tentacle Fried Rice';

                if($helperSkills->getSexDrive()->getTotal() >= 1)
                    $possibleItems[] = 'Goodberries';

                if($helperSkills->getMusic()->getTotal() >= 5)
                    $possibleItems[] = 'Musical Scales';

                if($helperSkills->getCrafts()->getTotal() >= 5)
                    $possibleItems[] = 'Handicrafts Supply Box';

                $extraItemName = $rng->rngNextFromArray($possibleItems);

                $extraItem = $inventoryService->receiveItem($extraItemName, $user, $user, $user->getName() . ' received this from their dragon, ' . $dragon->getName() . ', and pet, ' . $dragon->getHelper()->getName() . '.', LocationEnum::HOME);
            }
        }

        $em->flush();

        $itemNames = array_map(function($goodie) { return $goodie['item']; }, $goodies);
        sort($itemNames);

        $message = $dragon->getName() . ' thanks you for your gift, and gives you ' . ArrayFunctions::list_nice($itemNames) . ' in exchange';

        if($totalMoneys > 0)
        {
            if($extraItem)
                $message .= ', plus ' . $totalMoneys . '~~m~~ and ' . $extraItem->getItem()->getNameWithArticle() . ' earned in investments (thanks to ' . $dragon->getHelper()->getName() . '\'s help!)';
            else
                $message .= ', plus ' . $totalMoneys . '~~m~~ earned in investments (thanks to ' . $dragon->getHelper()->getName() . '\'s help!)';
        }
        else if($extraItem)
        {
            $message = ', plus ' . $extraItem->getItem()->getNameWithArticle() . ', which they earned from a particularly-lucrative deal (made in no small part due to ' . $dragon->getHelper()->getName() . '\'s help!)';
        }
        else
            $message .= '.';

        $responseService->addFlashMessage($message);

        return $responseService->success($dragon, [
            SerializationGroupEnum::MY_DRAGON,
            SerializationGroupEnum::HELPER_PET,
        ]);
    }

    /**
     * @Route("/assignHelper/{pet}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function assignHelper(
        Pet $pet, ResponseService $responseService, EntityManagerInterface $em,
        PetAssistantService $petAssistantService, DragonRepository $dragonRepository
    )
    {
        $user = $this->getUser();

        $petAssistantService->helpDragon($user, $pet);

        $em->flush();

        $dragon = $dragonRepository->findAdult($user);

        return $responseService->success($dragon, [
            SerializationGroupEnum::MY_DRAGON,
            SerializationGroupEnum::HELPER_PET
        ]);
    }

}
