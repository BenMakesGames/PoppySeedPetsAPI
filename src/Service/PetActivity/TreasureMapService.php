<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Enum\SpiritCompanionStarEnum;
use App\Enum\StatusEffectEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Functions\GrammarFunctions;
use App\Functions\NumberFunctions;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\ItemRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\EquipmentService;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\InventoryModifierService;
use App\Service\Squirrel3;
use App\Service\StatusEffectService;
use Doctrine\ORM\EntityManagerInterface;

class TreasureMapService
{
    private $responseService;
    private $inventoryService;
    private $userStatsRepository;
    private $em;
    private $petExperienceService;
    private $userQuestRepository;
    private $statusEffectService;
    private $toolBonusService;
    private $squirrel3;
    private $itemRepository;
    private $equipmentService;
    private HouseSimService $houseSimService;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, UserStatsRepository $userStatsRepository,
        EntityManagerInterface $em, PetExperienceService $petExperienceService, UserQuestRepository $userQuestRepository,
        StatusEffectService $statusEffectService, InventoryModifierService $toolBonusService, Squirrel3 $squirrel3,
        ItemRepository $itemRepository, EquipmentService $equipmentService, HouseSimService $houseSimService
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->userStatsRepository = $userStatsRepository;
        $this->em = $em;
        $this->petExperienceService = $petExperienceService;
        $this->userQuestRepository = $userQuestRepository;
        $this->statusEffectService = $statusEffectService;
        $this->toolBonusService = $toolBonusService;
        $this->squirrel3 = $squirrel3;
        $this->itemRepository = $itemRepository;
        $this->equipmentService = $equipmentService;
        $this->houseSimService = $houseSimService;
    }

    public function doCetguelisTreasureMap(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $activityLog = null;
        $changes = new PetChanges($pet);

        $followMapCheck = $this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getPerception()->getTotal() + $pet->getSkills()->getNature() + $petWithSkills->getIntelligence()->getTotal());

        if($followMapCheck < 15)
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to decipher Cetgueli\'s Treasure Map, but couldn\'t make sense of it.', 'icons/activity-logs/confused');
            $pet->increaseEsteem(-1);

            if($this->squirrel3->rngNextInt(1, 3) === 1)
            {
                $activityLog->setEntry($activityLog->getEntry() . ' %pet:' . $pet->getId() . '.name% put the treasure map down.');
                $this->equipmentService->unequipPet($pet);
            }

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 90), PetActivityStatEnum::GATHER, false);
        }
        else
        {
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::NATURE ]);
            $pet->increaseEsteem(5);

            $prize = 'Outrageously Strongbox';

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% followed Cetgueli\'s Treasure Map, and found a ' . $prize . '! (Also, the map was lost, because video games.)', 'items/map/cetgueli');

            $this->em->remove($pet->getTool());
            $pet->setTool(null);

            $this->inventoryService->petCollectsItem($prize, $pet, $pet->getName() . ' found this by following Cetgueli\'s Treasure Map!', $activityLog);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 90), PetActivityStatEnum::GATHER, true);
        }

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));

        $activityLog->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY);

        if($this->squirrel3->rngNextInt(1, 5) === 1)
            $this->inventoryService->petAttractsRandomBug($pet);
    }

    public function doGoldIdol(Pet $pet)
    {
        $changes = new PetChanges($pet);

        $pet->increaseEsteem(5);

        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found that Thieving Magpie, and offered it a "Gold" Idol in exchange for something else. The magpie eagerly accepted.', 'items/treasure/magpie-deal')
            ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
        ;

        $this->em->remove($pet->getTool());
        $pet->setTool(null);

        $this->inventoryService->petCollectsItem('Magpie\'s Deal', $pet, $pet->getName() . ' got this from a Thieving Magpie in exchange for a "Gold" Idol!', $activityLog);

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::OTHER, null);

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));

        if($this->squirrel3->rngNextInt(1, 20) === 1)
            $this->inventoryService->petAttractsRandomBug($pet);
    }

    public function doAbundantiasVault(Pet $pet)
    {
        $changes = new PetChanges($pet);

        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% allowed themselves to be carried off by the Winged Key, to Abundantia\'s Valut. Upon arriving, they had to wait in a long ling for, like, 2 hours, during which time they filled out some exceptionally-tedious paperwork. At the end of it all, a tired-looking house fairy took the Winged Key, lead %pet:' . $pet->getId() . '.name% through a door which somehow took them right back home, performed a blessing on the house (probably their 50th of the day, based on their level of enthusiasm), and left.', '')
            ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
        ;

        $this->em->remove($pet->getTool());

        $pet
            ->setTool(null)
            ->increaseFood(-1)
            ->increaseEsteem(-4)
        ;

        $pet->getOwner()->setUnlockedBulkSelling();

        $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ] );
        $this->petExperienceService->spendTime($pet, 120, PetActivityStatEnum::OTHER, null);

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));
    }

    public function doKeybladeTower(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();

        $changes = new PetChanges($pet);

        $skill = 2 * ($petWithSkills->getBrawl()->getTotal() * 2 + $petWithSkills->getStamina()->getTotal() * 2 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStrength()->getTotal() + $pet->getLevel());

        $floor = $this->squirrel3->rngNextInt(max(1, ceil($skill / 2)), 20 + $skill);
        $floor = NumberFunctions::clamp($floor, 1, 100);

        $keybladeName = $pet->getTool()->getItem()->getName();

        if($floor === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% took their ' . $keybladeName . ' to the Tower of Trials, but couldn\'t even get past the first floor...', '');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
            $pet
                ->increaseEsteem(-2)
                ->increaseFood(-1)
            ;
        }
        else if($floor < 25)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% took their ' . $keybladeName . ' to the Tower of Trials, but had to retreat after only the ' . GrammarFunctions::ordinalize($floor) . ' floor.', '');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
            $pet
                ->increaseFood(-2)
            ;
        }
        else if($floor < 50)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% took their ' . $keybladeName . ' to the Tower of Trials, but got tired and had to quit after the ' . GrammarFunctions::ordinalize($floor) . ' floor.', '');
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ]);
            $pet
                ->increaseFood(-3)
            ;
        }
        else if($floor < 75)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% took their ' . $keybladeName . ' to the Tower of Trials, and got as far as the ' . GrammarFunctions::ordinalize($floor) . ' floor before they had to quit. (Not bad!)', '');
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ]);
            $pet
                ->increaseFood(-4)
                ->increaseEsteem(2)
            ;
        }
        else if($floor < 100)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% took their ' . $keybladeName . ' to the Tower of Trials, and got all the way to the ' . GrammarFunctions::ordinalize($floor) . ' floor, but couldn\'t get any further. (Pretty good, though!)', '');
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::BRAWL ]);
            $pet
                ->increaseFood(-5)
                ->increaseEsteem(3)
            ;
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% took their ' . $keybladeName . ' to the Tower of Trials, and beat the 100th floor! They plunged the keyblade into the pedestal, unlocking the door to the treasure room, and claimed a Tower Chest!', '');
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

        $this->petExperienceService->spendTime($pet, 20 + floor($floor / 1.8), PetActivityStatEnum::OTHER, null);

        $activityLog
            ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY + $floor)
            ->setChanges($changes->compare($pet))
        ;

        if($this->squirrel3->rngNextInt(1, 20) === 1)
            $this->inventoryService->petAttractsRandomBug($pet);
    }

    public function doLeprechaun(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

        if($pet->getTool()->isGrayscaling())
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While %pet:' . $pet->getId() . '.name% was thinking about what to do, a Leprechaun approached them... but upon seeing %pet:' . $pet->getId() . '.name%\'s pale visage, fled screaming into the woods! (Oops!) %pet:' . $pet->getId() . '.name% put their ' . $pet->getTool()->getFullItemName() . ' down...', '');
            $this->equipmentService->unequipPet($pet);
            return $activityLog;
        }

        $loot = $this->itemRepository->findOneByName($this->squirrel3->rngNextFromArray([
            'Pot of Gold', 'Green Scroll'
        ]));

        $activityLog = $this->responseService->createActivityLog($pet, 'While %pet:' . $pet->getId() . '.name% was thinking about what to do, a Leprechaun approached them, and, without a word, exchanged %pet:' . $pet->getId() . '.name%\'s ' . $pet->getTool()->getFullItemName() . ' for ' . $loot->getNameWithArticle() . '!', '');

        $newInventory = $this->inventoryService->receiveItem($loot, $pet->getOwner(), $pet->getOwner(), 'Given to ' . $pet->getName() . ' by a Leprechaun.', LocationEnum::WARDROBE, $pet->getTool()->getLockedToOwner());

        $this->em->remove($pet->getTool());
        $pet->setTool($newInventory);

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);

        return $activityLog;
    }

    public function doEggplantCurse(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::OTHER, null);

        $agk = $this->squirrel3->rngNextFromArray([ 'Agk!', 'Oh dang!', 'Noooo!', 'Quel dommage!', 'Welp!' ]);

        $activityLog = $this->responseService->createActivityLog($pet, 'While ' . '%pet:' . $pet->getId() . '.name% was thinking about what to do, a weird, purple energy oozed out of their ' . $this->toolBonusService->getNameWithModifiers($pet->getTool()) . ', and enveloped them! (' . $agk . ' It\'s the Eggplant Curse!)', '');
        $this->statusEffectService->applyStatusEffect($pet, StatusEffectEnum::EGGPLANT_CURSED, $this->squirrel3->rngNextInt(24, 48) * 60);

        $pet
            ->increaseEsteem(-$this->squirrel3->rngNextInt(4, 8))
            ->increaseSafety(-$this->squirrel3->rngNextInt(4, 8))
        ;

        return $activityLog;
    }

    public function doCookSomething(Pet $pet): PetActivityLog
    {
        $food = $this->squirrel3->rngNextFromArray([
            '"Mud Pie" (made from actual mud)',
            '"Useless Fizz Soda"',
            '"Deep-fried Planetary Rings"',
            '"Grass Soup"',
            '"Liquid-hot Magma Cake"',
            '"Stick Salad"',
            '"Bug Gumbo"',
            '"Acorn Fugu"'
        ]);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::OTHER, null);

        if($pet->getSpiritCompanion() && $pet->getSpiritCompanion()->getStar() === SpiritCompanionStarEnum::SAGITTARIUS)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% and ' . $pet->getSpiritCompanion()->getName() . ' made %user:' . $pet->getOwner()->getId() . '.name% ' . $food . ' with their ' . $pet->getTool()->getItem()->getName() . '. %user:' . $pet->getOwner()->getId() . '.Name% pretended to eat it with them. It was very good.', '');
            $pet->increaseLove(6);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made %user:' . $pet->getOwner()->getId() . '.name% ' . $food . ' with their ' . $pet->getTool()->getItem()->getName() . '. %user:' . $pet->getOwner()->getId() . '.Name% and %pet:' . $pet->getId() . '.name% pretended to eat it together. It was very good.', '');
            $pet->increaseLove(4);
        }

        return $activityLog;
    }

    public function doUseDiffieHKey(Pet $pet): PetActivityLog
    {
        if(!$pet->hasMerit(MeritEnum::PROTOCOL_7))
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% didn\'t understand what they were supposed to do with the ' . $pet->getTool()->getItem()->getName() . ', so put it down...', '');

            $this->equipmentService->unequipPet($pet);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 30), PetActivityStatEnum::PROTOCOL_7, false);

            return $activityLog;
        }

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);

        $this->em->remove($pet->getTool());
        $pet->setTool(null);

        $loot = $this->squirrel3->rngNextFromArray([
            'Alice\'s Secret', 'Bob\'s Secret'
        ]);

        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found ' . $loot . ' in Project-E by using their Diffie-H Key.', '')
            ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
        ;
        $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' found this in Project-E by using a Diffie-H Key.', $activityLog);

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $activityLog;
    }

    public function doFluffmongerTrade(Pet $pet): PetActivityLog
    {
        $changes = new PetChanges($pet);

        if($this->houseSimService->hasInventory('Fluff', 1))
        {
            $this->houseSimService->getState()->loseItem('Fluff', 1);

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

                    if($pet->getOwner()->getUnlockedTrader())
                        $possibleTrades[] = [ 'item' => 'Limestone', 'weight' => 20, 'message' => 'You working on a trade with those Tell Samarzhoustia merchants, or something?' ];

                    if($pet->getOwner()->getUnlockedBeehive())
                        $possibleTrades[] = [ 'item' => 'Red Clover', 'weight' => 40, 'message' => 'You keep bees? Is this Bee Fluff you gave me??' ];

                    if($pet->getOwner()->getUnlockedFireplace())
                        $possibleTrades[] = [ 'item' => 'Charcoal', 'weight' => 40, 'message' => 'Trying to keep that Fireplace going? Have you got a Fairy Ring, yet?' ];
                }

                if($fluffTradedStat->getValue() > 40)
                {
                    $possibleTrades[] = [ 'item' => 'Top Hat', 'weight' => 15, 'message' => 'Want another hat? Can\'t blame you. It\'s a fine hat!' ];
                }
            }

            $trade = ArrayFunctions::pick_one_weighted($possibleTrades, function($t) { return $t['weight']; });
            $item = $trade['item'];
            $message = $trade['message'];

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the Fluffmonger, and traded Fluff for ' . $item . '. "' . $message . '" said the Fluffmonger.', '');

            $inventoryItem = $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' received this in a trade with the Fluffmonger.', $activityLog);

            if(array_key_exists('locked', $trade) && $trade['locked'])
                $inventoryItem->setLockedToOwner(true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% visited the Fluffmonger, but didn\'t have any Fluff to trade! They put the ' . $pet->getTool()->getItem()->getName() . ' down...', '');

            // didn't have fluff
            $this->equipmentService->unequipPet($pet);
        }

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);

        $activityLog->setChanges($changes->compare($pet));

        return $activityLog;
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
