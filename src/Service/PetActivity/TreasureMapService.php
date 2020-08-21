<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\User;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Enum\TradeGroupEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Functions\GrammarFunctions;
use App\Functions\NumberFunctions;
use App\Model\PetChanges;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\PetService;
use App\Service\ResponseService;
use App\Service\TraderService;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Location;

class TreasureMapService
{
    private $responseService;
    private $inventoryService;
    private $userStatsRepository;
    private $em;
    private $petExperienceService;
    private $userQuestRepository;
    private $traderService;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, UserStatsRepository $userStatsRepository,
        EntityManagerInterface $em, PetExperienceService $petExperienceService, UserQuestRepository $userQuestRepository,
        TraderService $traderService
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->userStatsRepository = $userStatsRepository;
        $this->em = $em;
        $this->petExperienceService = $petExperienceService;
        $this->userQuestRepository = $userQuestRepository;
        $this->traderService = $traderService;
    }

    public function doCetguelisTreasureMap(Pet $pet)
    {
        $activityLog = null;
        $changes = new PetChanges($pet);

        $followMapCheck = mt_rand(1, 10 + $pet->getPerception() + $pet->getSkills()->getNature() + $pet->getIntelligence());

        if($followMapCheck < 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 90), PetActivityStatEnum::GATHER, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to decipher Cetgueli\'s Treasure Map, but couldn\'t make sense of it.', 'icons/activity-logs/confused');
            $pet->increaseEsteem(-1);

            if(mt_rand(1, 3) === 1)
            {
                $activityLog->setEntry($activityLog->getEntry() . ' ' . $pet->getName() . ' put the treasure map down.');
                $this->inventoryService->unequipPet($pet);
            }
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 90), PetActivityStatEnum::GATHER, true);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::NATURE ]);
            $pet->increaseEsteem(5);

            $prize = 'Outrageously Strongbox';

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' followed Cetgueli\'s Treasure Map, and found a ' . $prize . '! (Also, the map was lost, because video games.)', 'items/map/cetgueli');

            $this->em->remove($pet->getTool());
            $pet->setTool(null);

            $this->inventoryService->petCollectsItem($prize, $pet, $pet->getName() . ' found this by following Cetgueli\'s Treasure Map!', $activityLog);
        }

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));

        $activityLog->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY);

        if(mt_rand(1, 5) === 1)
            $this->inventoryService->petAttractsRandomBug($pet);
    }

    public function doGoldIdol(Pet $pet)
    {
        $activityLog = null;
        $changes = new PetChanges($pet);

        $this->petExperienceService->spendTime($pet, mt_rand(30, 45), PetActivityStatEnum::OTHER, null);
        $pet->increaseEsteem(5);

        $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found that Thieving Magpie, and offered it a "Gold" Idol in exchange for something else. The magpie eagerly accepted.', 'items/treasure/magpie-deal')
            ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
        ;

        $this->em->remove($pet->getTool());
        $pet->setTool(null);

        $this->inventoryService->petCollectsItem('Magpie\'s Deal', $pet, $pet->getName() . ' got this from a Thieving Magpie in exchange for a "Gold" Idol!', $activityLog);

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));

        if(mt_rand(1, 20) === 1)
            $this->inventoryService->petAttractsRandomBug($pet);
    }

    public function doKeybladeTower(Pet $pet)
    {
        $changes = new PetChanges($pet);

        $skill = 2 * ($pet->getBrawl() * 2 + $pet->getStamina() * 2 + $pet->getDexterity() + $pet->getStrength() + $pet->getLevel());

        $floor = mt_rand(max(1, ceil($skill / 2)), 20 + $skill);
        $floor = NumberFunctions::constrain($floor, 1, 100);

        $keybladeName = $pet->getTool()->getItem()->getName();

        $this->petExperienceService->spendTime($pet, 20 + floor($floor / 1.8), PetActivityStatEnum::OTHER, null);

        if($floor === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' took their ' . $keybladeName . ' to the Tower of Trials, but couldn\'t even get past the first floor...', '');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
            $pet
                ->increaseEsteem(-2)
                ->increaseFood(-1)
            ;
        }
        else if($floor < 25)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' took their ' . $keybladeName . ' to the Tower of Trials, but had to retreat after only the ' . GrammarFunctions::ordinalize($floor) . ' floor.', '');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
            $pet
                ->increaseFood(-2)
            ;
        }
        else if($floor < 50)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' took their ' . $keybladeName . ' to the Tower of Trials, but got tired and had to quit after the ' . GrammarFunctions::ordinalize($floor) . ' floor.', '');
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ]);
            $pet
                ->increaseFood(-3)
            ;
        }
        else if($floor < 75)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' took their ' . $keybladeName . ' to the Tower of Trials, and got as far as the ' . GrammarFunctions::ordinalize($floor) . ' floor before they had to quit. (Not bad!)', '');
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ]);
            $pet
                ->increaseFood(-4)
                ->increaseEsteem(2)
            ;
        }
        else if($floor < 100)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' took their ' . $keybladeName . ' to the Tower of Trials, and got all the way to the ' . GrammarFunctions::ordinalize($floor) . ' floor, but couldn\'t get any further. (Pretty good, though!)', '');
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::BRAWL ]);
            $pet
                ->increaseFood(-5)
                ->increaseEsteem(3)
            ;
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' took their ' . $keybladeName . ' to the Tower of Trials, and beat the 100th floor! They plunged the keyblade into the pedestal, unlocking the door to the treasure room, and claimed a Tower Chest!', '');
            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::BRAWL ]);
            $pet
                ->increaseFood(-6)
                ->increaseEsteem(5)
                ->increaseSafety(2)
            ;

            $this->inventoryService->petCollectsItem('Tower Chest', $pet, $pet->getName() . ' got this by defeating the 100th floor of the Tower of Trials!', $activityLog);
            $this->em->remove($pet->getTool());
            $pet->setTool(null);
        }

        $activityLog
            ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY + $floor)
            ->setChanges($changes->compare($pet))
        ;

        if(mt_rand(1, 20) === 1)
            $this->inventoryService->petAttractsRandomBug($pet);
    }

    public function doFluffmongerTrade(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::OTHER, null);

        if($this->inventoryService->loseItem('Fluff', $pet->getOwner(), LocationEnum::HOME, 1) === 1)
        {
            // had fluff!
            $fluffTradedStat = $this->userStatsRepository->incrementStat($pet->getOwner(), UserStatEnum::TRADED_WITH_THE_FLUFFMONGER);

            $fluffmongerSpecialTrades = [
                'Behatting Scroll' => 20,
                'Top Hat' => 40
            ];

            $possibleTrades = [];

            foreach($fluffmongerSpecialTrades as $item=>$tradeCount)
            {
                if($fluffTradedStat->getValue() >= $tradeCount)
                {
                    $traded = $this->userQuestRepository->findOrCreate($pet->getOwner(), 'Fluffmonger Trade #' . $tradeCount, false);
                    if(!$traded->getValue())
                    {
                        $traded->setValue(true);

                        $possibleTrades = [
                            [ 'item' => $item, 'weight' => 1, 'message' => 'Here\'s something special for you for our ' . GrammarFunctions::ordinalize($tradeCount) . ' trade!', 'locked' => true ],
                        ];
                    }
                }
            }

            if(count($possibleTrades) === 0)
            {
                $foods = $this->getFluffmongerFlavorFoods($pet->getFavoriteFlavor());

                $possibleTrades = [];

                foreach($foods as $food)
                    $possibleTrades[] = [ 'item' => $food, 'weight' => 50, 'message' => 'You really like ' . $food . ', eh? That works out perfectly: I really like Fluff!' ];

                if($pet->getFood() + $pet->getJunk() > 0)
                {
                    if(!$pet->wantsSobriety())
                        $possibleTrades[] = [ 'item' => 'Coffee Jelly', 'weight' => 50, 'message' => 'Those who partake of its wobbling flesh will never know sadness again,' ];

                    $possibleTrades = array_merge($possibleTrades, [
                        [ 'item' => 'Rice Flower', 'weight' => 50, 'message' => 'I hear these have some special uses, so I grow them for trading.' ],
                        [ 'item' => 'Paper Bag', 'weight' => 50, 'message' => 'I really hope there isn\'t just another Fluff in here. Or roaches. (Which would be worse, actually?)' ],
                        [ 'item' => 'Secret Seashell', 'weight' => 5, 'message' => 'It\'s a secret to everyone,' ],
                        [ 'item' => 'Silica Grounds', 'weight' => 50, 'message' => 'I mean, you could just scoop some up on the beach, but if you insist...' ],
                        [ 'item' => 'Magic Smoke', 'weight' => 35, 'message' => 'Are you going to make something with that?' ],
                    ]);

                    if($this->hasUnlockedTraderFoodTrades($pet->getOwner()))
                        $possibleTrades[] = [ 'item' => 'Limestone', 'weight' => 20, 'message' => 'You working on a trade with those Tell Samarzhoustia merchants, or something?' ];

                    if($pet->getOwner()->getUnlockedBeehive())
                        $possibleTrades[] = [ 'item' => 'Red Clover', 'weight' => 40, 'message' => 'You keep bees? Is this Bee Fluff you gave me??' ];

                    if($pet->getOwner()->getUnlockedFireplace())
                        $possibleTrades[] = [ 'item' => 'Charcoal', 'weight' => 40, 'message' => 'Trying to keep that Fireplace going? Have you got a Fairy Ring, yet?' ];
                }

                if($fluffTradedStat->getValue() > 40)
                {
                    $possibleTrades = [
                        [ 'item' => 'Top Hat', 'weight' => 15, 'message' => 'Want another hat? Can\'t blame you. It\'s a fine hat!' ],
                    ];
                }
            }

            $trade = ArrayFunctions::pick_one_weighted($possibleTrades, function($t) { return $t['weight']; });
            $item = $trade['item'];
            $message = $trade['message'];

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' visited the Fluffmonger, and traded Fluff for ' . $item . '. "' . $message . '" said the Fluffmonger.', '');

            $inventoryItem = $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' received this in a trade with the Fluffmonger.', $activityLog);

            if(array_key_exists('locked', $trade) && $trade['locked'])
                $inventoryItem->setLockedToOwner(true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' visited the Fluffmonger, but didn\'t have any Fluff to trade! They put the ' . $pet->getTool()->getItem()->getName() . ' down...', '');

            // didn't have fluff
            $this->inventoryService->unequipPet($pet);
        }

        return $activityLog;
    }

    private function hasUnlockedTraderFoodTrades(User $user): bool
    {
        if(!$user->getUnlockedTrader())
            return false;

        $groups = $this->traderService->getUnlockedTradeGroups($user);

        return in_array(TradeGroupEnum::FOODS, $groups);
    }

    public function getFluffmongerFlavorFoods($flavor)
    {
        switch($flavor)
        {
            case FlavorEnum::EARTHY: return [ 'Fried Tomato', 'Matzah Bread', 'Smashed Potatoes' ];
            case FlavorEnum::FRUITY: return [ 'Fried Tomato', 'Naner Yogurt', 'Red' ];
            case FlavorEnum::TANNIC: return [ 'Chocolate Ice Cream', 'Warm Red Muffin', 'Mixed Nuts' ];
            case FlavorEnum::SPICY: return [ 'Candied Ginger', 'Onion Rings', 'Shakshouka' ];
            case FlavorEnum::CREAMY: return [ 'Chocolate Ice Cream', 'Coconut Half', 'Eggnog' ];
            case FlavorEnum::MEATY: return [ 'Fish', 'Beans', 'Hakuna Frittata' ];
            case FlavorEnum::PLANTY: return [ 'Shakshouka', 'Hakuna Frittata', 'Coconut Half' ];
            case FlavorEnum::FISHY: return [ 'Battered, Fried Fish', 'Fermented Fish Onigiri' ];
            case FlavorEnum::FLORAL: return [ 'Apricot', 'Berry Muffin', 'Orange Juice' ];
            case FlavorEnum::FATTY: return [ 'Onion Rings', 'Eggnog', 'Hakuna Frittata' ];
            case FlavorEnum::ONIONY: return [ 'Onion Rings', 'Hakuna Frittata', 'Instant Ramen (Dry)' ];
            case FlavorEnum::CHEMICALLY: return [ 'Fermented Fish Onigiri', 'Century Egg', 'Tomato "Sushi"' ];
        }

        throw new \InvalidArgumentException('Ben forgot to code Fluffmonger foods for the flavor "' . $flavor . '"!');
    }
}
