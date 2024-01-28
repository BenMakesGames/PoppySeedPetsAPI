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
use App\Enum\UnlockableFeatureEnum;
use App\Enum\UserStatEnum;
use App\Functions\AdventureMath;
use App\Functions\ArrayFunctions;
use App\Functions\EquipmentFunctions;
use App\Functions\GrammarFunctions;
use App\Functions\InventoryModifierFunctions;
use App\Functions\ItemRepository;
use App\Functions\NumberFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\StatusEffectHelpers;
use App\Functions\UserQuestRepository;
use App\Functions\UserUnlockedFeatureHelpers;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Service\FieldGuideService;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;

class TreasureMapService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly UserStatsService $userStatsRepository,
        private readonly EntityManagerInterface $em,
        private readonly PetExperienceService $petExperienceService,
        private readonly IRandom $rng,
        private readonly HouseSimService $houseSimService,
        private readonly FieldGuideService $fieldGuideService
    )
    {
    }

    public function doCetguelisTreasureMap(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $changes = new PetChanges($pet);

        $followMapCheck = $this->rng->rngNextInt(1, 10 + $petWithSkills->getPerception()->getTotal() + $pet->getSkills()->getNature() + $petWithSkills->getIntelligence()->getTotal());

        if($followMapCheck < 14)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to follow Cetgueli\'s Treasure Map, but kept getting lost. (They\'re sure they\'re making progress, though!)')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering', 'Adventure!' ]))
            ;
            $pet->increaseEsteem(-1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 90), PetActivityStatEnum::GATHER, false);
        }
        else
        {
            $prize = 'Outrageously Strongbox';

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% followed Cetgueli\'s Treasure Map, and found a ' . $prize . '! (Also, the map was lost, because video games.)')
                ->setIcon('items/map/cetgueli')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering', 'Adventure!' ]))
            ;

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::NATURE ], $activityLog);
            $pet->increaseEsteem(5);

            $this->em->remove($pet->getTool());
            $pet->setTool(null);

            $this->inventoryService->petCollectsItem($prize, $pet, $pet->getName() . ' found this by following Cetgueli\'s Treasure Map!', $activityLog);

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 90), PetActivityStatEnum::GATHER, true);
        }

        $activityLog
            ->setChanges($changes->compare($pet))
            ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
        ;

        if(AdventureMath::petAttractsBug($this->rng, $pet, 5))
            $this->inventoryService->petAttractsRandomBug($pet);
    }

    public function doGoldIdol(Pet $pet)
    {
        $changes = new PetChanges($pet);

        $pet->increaseEsteem(5);

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% found that Thieving Magpie, and offered it a "Gold" Idol in exchange for something else. The magpie eagerly accepted.')
            ->setIcon('items/treasure/magpie-deal')
            ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Adventure!' ]))
        ;

        $this->em->remove($pet->getTool());
        $pet->setTool(null);

        $this->inventoryService->petCollectsItem('Magpie\'s Deal', $pet, $pet->getName() . ' got this from a Thieving Magpie in exchange for a "Gold" Idol!', $activityLog);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::OTHER, null);

        $activityLog->setChanges($changes->compare($pet));

        if(AdventureMath::petAttractsBug($this->rng, $pet, 20))
            $this->inventoryService->petAttractsRandomBug($pet);
    }

    public function doAbundantiasVault(Pet $pet)
    {
        $changes = new PetChanges($pet);

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% allowed themselves to be carried off by the Winged Key, to Abundantia\'s Vault. Upon arriving, they had to wait in a long line for, like, 2 hours, during which time they filled out some exceptionally-tedious paperwork. At the end of it all, a tired-looking house fairy took the Winged Key, led %pet:' . $pet->getId() . '.name% through a door which somehow took them right back home, performed a blessing on the house (probably their 50th of the day, based on their level of enthusiasm), and left.')
            ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Adventure!' ]))
        ;

        $this->em->remove($pet->getTool());

        $pet
            ->setTool(null)
            ->increaseFood(-1)
            ->increaseEsteem(-4)
        ;

        if(!$pet->getOwner()->hasUnlockedFeature(UnlockableFeatureEnum::BulkSelling))
            UserUnlockedFeatureHelpers::create($this->em, $pet->getOwner(), UnlockableFeatureEnum::BulkSelling);

        $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::ARCANA ], $activityLog);
        $this->petExperienceService->spendTime($pet, 120, PetActivityStatEnum::OTHER, null);

        $activityLog->setChanges($changes->compare($pet));
    }

    public function doKeybladeTower(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();

        $changes = new PetChanges($pet);

        $skill = 2 * ($petWithSkills->getBrawl()->getTotal() * 2 + $petWithSkills->getStamina()->getTotal() * 2 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStrength()->getTotal() + $pet->getLevel());

        $floor = $this->rng->rngNextInt(max(1, ceil($skill / 2)), 20 + $skill);
        $floor = NumberFunctions::clamp($floor, 1, 100);

        $keybladeName = $pet->getTool()->getItem()->getName();

        if($floor === 1)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% took their ' . $keybladeName . ' to the Tower of Trials, but couldn\'t even get past the first floor...');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);
            $pet
                ->increaseEsteem(-2)
                ->increaseFood(-1)
            ;
        }
        else if($floor < 25)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% took their ' . $keybladeName . ' to the Tower of Trials, but had to retreat after only the ' . GrammarFunctions::ordinalize($floor) . ' floor.');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);
            $pet
                ->increaseFood(-2)
            ;
        }
        else if($floor < 50)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% took their ' . $keybladeName . ' to the Tower of Trials, but got tired and had to quit after the ' . GrammarFunctions::ordinalize($floor) . ' floor.');
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ], $activityLog);
            $pet
                ->increaseFood(-3)
            ;
        }
        else if($floor < 75)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% took their ' . $keybladeName . ' to the Tower of Trials, and got as far as the ' . GrammarFunctions::ordinalize($floor) . ' floor before they had to quit. (Not bad!)');
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ], $activityLog);
            $pet
                ->increaseFood(-4)
                ->increaseEsteem(2)
            ;
        }
        else if($floor < 100)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% took their ' . $keybladeName . ' to the Tower of Trials, and got all the way to the ' . GrammarFunctions::ordinalize($floor) . ' floor, but couldn\'t get any further. (Pretty good, though!)');
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::BRAWL ], $activityLog);
            $pet
                ->increaseFood(-5)
                ->increaseEsteem(3)
            ;
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% took their ' . $keybladeName . ' to the Tower of Trials, and beat the 100th floor! They plunged the keyblade into the pedestal, unlocking the door to the treasure room, and claimed a Tower Chest!');
            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::BRAWL ], $activityLog);
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
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Fighting', 'Adventure!' ]))
            ->setChanges($changes->compare($pet))
        ;

        if(AdventureMath::petAttractsBug($this->rng, $pet, 20))
            $this->inventoryService->petAttractsRandomBug($pet);
    }

    public function doLeprechaun(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($pet->getTool()->isGrayscaling() || $pet->hasStatusEffect(StatusEffectEnum::BITTEN_BY_A_VAMPIRE))
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, 'While %pet:' . $pet->getId() . '.name% was thinking about what to do, a Leprechaun approached them... but upon seeing %pet:' . $pet->getId() . '.name%\'s pale visage, fled screaming into the woods! (Oops!) %pet:' . $pet->getId() . '.name% put their ' . $pet->getTool()->getFullItemName() . ' down...')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Fae-kind', 'Adventure!' ]))
            ;

            if($pet->hasStatusEffect(StatusEffectEnum::BITTEN_BY_A_VAMPIRE))
                $activityLog->setIcon('icons/status-effect/bite-vampire');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::ARCANA ], $activityLog);
            EquipmentFunctions::unequipPet($pet);
            return $activityLog;
        }

        $loot = ItemRepository::findOneByName($this->em, $this->rng->rngNextFromArray([
            'Pot of Gold', 'Green Scroll'
        ]));

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, 'While %pet:' . $pet->getId() . '.name% was thinking about what to do, a Leprechaun approached them, and, without a word, exchanged %pet:' . $pet->getId() . '.name%\'s ' . $pet->getTool()->getFullItemName() . ' for ' . $loot->getNameWithArticle() . '!')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Fae-kind', 'Adventure!' ]))
        ;

        $newInventory = $this->inventoryService->receiveItem($loot, $pet->getOwner(), $pet->getOwner(), 'Given to ' . $pet->getName() . ' by a Leprechaun.', LocationEnum::WARDROBE, $pet->getTool()->getLockedToOwner());

        $this->em->remove($pet->getTool());
        $pet->setTool($newInventory);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::ARCANA ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);

        return $activityLog;
    }

    public function doEggplantCurse(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::OTHER, null);

        $agk = $this->rng->rngNextFromArray([ 'Agk!', 'Oh dang!', 'Noooo!', 'Quel dommage!', 'Welp!' ]);

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, 'While ' . '%pet:' . $pet->getId() . '.name% was thinking about what to do, a weird, purple energy oozed out of their ' . InventoryModifierFunctions::getNameWithModifiers($pet->getTool()) . ', and enveloped them! (' . $agk . ' It\'s the Eggplant Curse!)')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Adventure!' ]))
        ;

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::ARCANA ], $activityLog);
        StatusEffectHelpers::applyStatusEffect($this->em, $pet, StatusEffectEnum::EGGPLANT_CURSED, $this->rng->rngNextInt(24, 48) * 60);

        $pet
            ->increaseEsteem(-$this->rng->rngNextInt(4, 8))
            ->increaseSafety(-$this->rng->rngNextInt(4, 8))
        ;

        return $activityLog;
    }

    public function doCookSomething(Pet $pet): PetActivityLog
    {
        $food = $this->rng->rngNextFromArray([
            '"Mud Pie" (made from actual mud)',
            '"Useless Fizz Soda"',
            '"Deep-fried Planetary Rings"',
            '"Grass Soup"',
            '"Liquid-hot Magma Cake"',
            '"Stick Salad"',
            '"Bug Gumbo"',
            '"Acorn Fugu"'
        ]);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::OTHER, null);
        
        $tags = [ 'Adventure!' ];

        if($pet->getSpiritCompanion() && $pet->getSpiritCompanion()->getStar() === SpiritCompanionStarEnum::SAGITTARIUS)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% and ' . $pet->getSpiritCompanion()->getName() . ' made %user:' . $pet->getOwner()->getId() . '.name% ' . $food . ' with their ' . $pet->getTool()->getItem()->getName() . '. %user:' . $pet->getOwner()->getId() . '.Name% pretended to eat it with them. It was very good.');
            $tags[] = 'Spirit Companion';
            $pet->increaseLove(6);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% made %user:' . $pet->getOwner()->getId() . '.name% ' . $food . ' with their ' . $pet->getTool()->getItem()->getName() . '. %user:' . $pet->getOwner()->getId() . '.Name% and %pet:' . $pet->getId() . '.name% pretended to eat it together. It was very good.');
            $pet->increaseLove(4);
        }

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ], $activityLog);
        $activityLog->addTags(PetActivityLogTagHelpers::findByNames($this->em, $tags));
        
        return $activityLog;
    }

    public function doUseDiffieHKey(Pet $pet): PetActivityLog
    {
        if(!$pet->hasMerit(MeritEnum::PROTOCOL_7))
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% didn\'t understand what they were supposed to do with the ' . $pet->getTool()->getItem()->getName() . ', so put it down... (The Protocol-7 Merit is needed.)')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E', 'Adventure!' ]))
            ;

            EquipmentFunctions::unequipPet($pet);

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(15, 30), PetActivityStatEnum::PROTOCOL_7, false);

            return $activityLog;
        }

        $this->em->remove($pet->getTool());
        $pet->setTool(null);

        $loot = $this->rng->rngNextFromArray([
            'Alice\'s Secret', 'Bob\'s Secret'
        ]);

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% found ' . $loot . ' in Project-E by using their Diffie-H Key.')
            ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Project-E', 'Adventure!' ]))
        ;
        $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' found this in Project-E by using a Diffie-H Key.', $activityLog);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $activityLog;
    }

    public function doFruitHunting(Pet $pet): PetActivityLog
    {
        $changes = new PetChanges($pet);

        $this->em->remove($pet->getTool());
        $pet->setTool(null);

        $loot = [ 'Naner' ];

        $loot[] = $this->rng->rngNextFromArray([
            'Orange', 'Apricot', 'Red', 'Cacao Fruit', 'Hot Dog', 'Blackberries', 'Blueberries', 'Sweet Beet'
        ]);

        $magicLocation = 'a strange tear in the fabric of the physical realm which flickered out of existence';

        $location = $this->rng->rngNextFromArray([
            'an open dumpster',
            'a discarded grocery bag near the river',
            'a recently-abandoned raccoon den',
            'a friendly deer with an open pack draped over its back',
            $magicLocation,
            'a half-buried hole just outside town'
        ]);

        $skillTrained = $location == $magicLocation ? PetSkillEnum::ARCANA : PetSkillEnum::NATURE;

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% followed a Fruit Fly on a String to ' . $location . ', and retrieved ' . ArrayFunctions::list_nice($loot) . ' after setting the fly free.')
            ->setIcon('items/bug/fly-fruit')
            ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Adventure!' ]))
        ;

        $this->userStatsRepository->incrementStat($pet->getOwner(), UserStatEnum::BUGS_PUT_OUTSIDE);

        foreach($loot as $itemName)
            $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' got this from ' . $location . ', which they found by following a Fruit Fly on a String.', $activityLog);

        $this->petExperienceService->gainExp($pet, 1, [ $skillTrained ], $activityLog);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);

        $activityLog->setChanges($changes->compare($pet));

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
                    $traded = UserQuestRepository::findOrCreate($this->em, $pet->getOwner(), 'Fluffmonger Trade #' . $tradeCount, false);
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
                $foods = TreasureMapService::getFluffmongerFlavorFoods($pet->getFavoriteFlavor());

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

                    if($pet->getOwner()->hasUnlockedFeature(UnlockableFeatureEnum::Trader))
                        $possibleTrades[] = [ 'item' => 'Limestone', 'weight' => 20, 'message' => 'You working on a trade with those Tell Samarzhoustia merchants, or something?' ];

                    if($pet->getOwner()->hasUnlockedFeature(UnlockableFeatureEnum::Beehive))
                        $possibleTrades[] = [ 'item' => 'Red Clover', 'weight' => 40, 'message' => 'You keep bees? Is this Bee Fluff you gave me??' ];

                    if($pet->getOwner()->hasUnlockedFeature(UnlockableFeatureEnum::Fireplace))
                        $possibleTrades[] = [ 'item' => 'Charcoal', 'weight' => 40, 'message' => 'Trying to keep that Fireplace going? Have you got a Fairy Ring, yet?' ];
                }

                if($fluffTradedStat->getValue() > 40)
                {
                    $possibleTrades[] = [ 'item' => 'Top Hat', 'weight' => 15, 'message' => 'Want another hat? Can\'t blame you. It\'s a fine hat!' ];
                }
            }

            $trade = ArrayFunctions::pick_one_weighted($possibleTrades, fn($t) => $t['weight']);
            $item = $trade['item'];
            $message = $trade['message'];

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% visited the Fluffmonger, and traded Fluff for ' . $item . '. "' . $message . '" said the Fluffmonger.');

            $inventoryItem = $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' received this in a trade with the Fluffmonger.', $activityLog);

            if(array_key_exists('locked', $trade) && $trade['locked'])
                $inventoryItem->setLockedToOwner(true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% visited the Fluffmonger, but didn\'t have any Fluff to trade! They put the ' . $pet->getTool()->getItem()->getName() . ' down...');

            // didn't have fluff
            EquipmentFunctions::unequipPet($pet);
        }

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);

        $activityLog
            ->setChanges($changes->compare($pet))
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Adventure!' ]))
        ;

        return $activityLog;
    }

    public static function getFluffmongerFlavorFoods($flavor)
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

        throw new \Exception('Ben forgot to code Fluffmonger foods for the flavor "' . $flavor . '"!');
    }

    public function doToastSkeweredMarshmallow(Pet $pet)
    {
        $changes = new PetChanges($pet);

        if($pet->getTool()->getItem()->getName() !== 'Skewered Marshmallow')
            throw new \Exception('Cannot toast a Skewered Marshmallow without a Skewered Marshmallow!');

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% went to the foot of the island\'s volcano, and toasted their Skewered Marshmallow - it\'s now a Toasted Marshmallow!')
            ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Adventure!' ]))
        ;

        $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Île Volcan', '%pet:' . $pet->getId() . '.name% went out to the island\'s volcano to toast a Skewered Marshmallow...');

        $pet
            ->getTool()
            ->changeItem(ItemRepository::findOneByName($this->em, 'Toasted Marshmallow'))
            ->addComment($pet->getName() . ' toasted this at the foot of the island\'s volcano!');

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::OTHER, null);

        $activityLog->setChanges($changes->compare($pet));

        if(AdventureMath::petAttractsBug($this->rng, $pet, 20))
            $this->inventoryService->petAttractsRandomBug($pet);
    }
}
