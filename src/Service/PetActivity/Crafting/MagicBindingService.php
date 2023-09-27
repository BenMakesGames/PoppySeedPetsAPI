<?php
namespace App\Service\PetActivity\Crafting;

use App\Entity\PetActivityLog;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ActivityHelpers;
use App\Functions\StatusEffectHelpers;
use App\Model\ActivityCallback;
use App\Model\ComputedPetSkills;
use App\Repository\EnchantmentRepository;
use App\Repository\ItemRepository;
use App\Repository\PetActivityLogTagRepository;
use App\Service\HattierService;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetActivity\Crafting\Helpers\CoinSmithingService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\WeatherService;
use Doctrine\ORM\EntityManagerInterface;

class MagicBindingService
{
    private InventoryService $inventoryService;
    private ResponseService $responseService;
    private PetExperienceService $petExperienceService;
    private ItemRepository $itemRepository;
    private IRandom $squirrel3;
    private CoinSmithingService $coinSmithingService;
    private HouseSimService $houseSimService;
    private HattierService $hattierService;
    private EntityManagerInterface $em;

    public function __construct(
        InventoryService $inventoryService, ResponseService $responseService, PetExperienceService $petExperienceService,
        ItemRepository $itemRepository, Squirrel3 $squirrel3, CoinSmithingService $coinSmithingService,
        HouseSimService $houseSimService, HattierService $hattierService, EntityManagerInterface $em
    )
    {
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->petExperienceService = $petExperienceService;
        $this->itemRepository = $itemRepository;
        $this->squirrel3 = $squirrel3;
        $this->coinSmithingService = $coinSmithingService;
        $this->houseSimService = $houseSimService;
        $this->hattierService = $hattierService;
        $this->em = $em;
    }

    /**
     * @return ActivityCallback[]
     */
    public function getCraftingPossibilities(ComputedPetSkills $petWithSkills): array
    {
        $weather = WeatherService::getWeather(new \DateTimeImmutable(), $petWithSkills->getPet());

        $possibilities = [];

        if($this->houseSimService->hasInventory('Mermaid Egg'))
            $possibilities[] = new ActivityCallback($this, 'mermaidEggToQuint', 8);

        if($this->houseSimService->hasInventory('Wings'))
        {
            if($this->houseSimService->hasInventory('Coriander Flower') && $this->houseSimService->hasInventory('Crooked Stick'))
                $possibilities[] = new ActivityCallback($this, 'createMericarp', 8);

            if($this->houseSimService->hasInventory('Talon') && $this->houseSimService->hasInventory('Paper'))
                $possibilities[] = new ActivityCallback($this, 'createSummoningScroll', 8);

            if($this->houseSimService->hasInventory('Painted Dumbbell') && $this->houseSimService->hasInventory('Glass') && $this->houseSimService->hasInventory('Quinacridone Magenta Dye'))
                $possibilities[] = new ActivityCallback($this, 'createSmilingWand', 8);

            if($this->houseSimService->hasInventory('Potato') && $this->houseSimService->hasInventory('Crooked Stick'))
                $possibilities[] = new ActivityCallback($this, 'createRussetStaff', 8);

            if($this->houseSimService->hasInventory('Bindle'))
                $possibilities[] = new ActivityCallback($this, 'createFlyingBindle', 8);

            if($this->houseSimService->hasInventory('Grappling Hook'))
                $possibilities[] = new ActivityCallback($this, 'createFlyingGrapplingHook', 8);

            if($this->houseSimService->hasInventory('Rapier') && $this->houseSimService->hasInventory('White Feathers'))
                $possibilities[] = new ActivityCallback($this, 'createWhiteEpee', 8);
        }

        if($this->houseSimService->hasInventory('Ruby Feather'))
        {
            if($this->houseSimService->hasInventory('Armor'))
                $possibilities[] = new ActivityCallback($this, 'createRubyeye', 8);

            if($this->houseSimService->hasInventory('Blunderbuss') && $this->houseSimService->hasInventory('Rainbow') && $this->houseSimService->hasInventory('Gold Bar'))
                $possibilities[] = new ActivityCallback($this, 'createWunderbuss', 8);
        }

        if($this->houseSimService->hasInventory('Blood Wine'))
        {
            if($this->houseSimService->hasInventory('Heavy Lance'))
                $possibilities[] = new ActivityCallback($this, 'createAmbuLance', 8);
        }

        if($this->houseSimService->hasInventory('Everice'))
        {
            // frostbite sucks
            $evericeWeight = $petWithSkills->getPet()->getSafety() < 0 ? 1 : 8;

            if($this->houseSimService->hasInventory('Pinecone'))
                $possibilities[] = new ActivityCallback($this, 'createMagicPinecone', $evericeWeight);

            if($this->houseSimService->hasInventory('Invisible Shovel'))
                $possibilities[] = new ActivityCallback($this, 'createSleet', $evericeWeight);

            if($this->houseSimService->hasInventory('Scythe') && $this->houseSimService->hasInventory('String'))
                $possibilities[] = new ActivityCallback($this, 'createFrostbite', $evericeWeight);

            if($this->houseSimService->hasInventory('Nonsenserang') && $this->houseSimService->hasInventory('Quintessence'))
                $possibilities[] = new ActivityCallback($this, 'createHexicle', $evericeWeight);

            if($this->houseSimService->hasInventory('Heavy Hammer'))
                $possibilities[] = new ActivityCallback($this, 'createFimbulvetr', $evericeWeight);
        }

        if($this->houseSimService->hasInventory('Wand of Ice') && $this->houseSimService->hasInventory('Mint'))
            $possibilities[] = new ActivityCallback($this, 'createCoolMintScepter', 10);

        if($this->houseSimService->hasInventory('Crystal Ball') && $this->houseSimService->hasInventory('Meteorite') && $this->houseSimService->hasInventory('Quinacridone Magenta Dye'))
            $possibilities[] = new ActivityCallback($this, 'createNoetalasEye', 10);

        if($this->houseSimService->hasInventory('Quintessence'))
        {
            if($this->houseSimService->hasInventory('Crystal Ball') && $this->houseSimService->hasInventory('Lotus Flower'))
                $possibilities[] = new ActivityCallback($this, 'createLotusjar', 8);

            if($this->houseSimService->hasInventory('Viscaria') && $this->houseSimService->hasInventory('Jar of Fireflies'))
                $possibilities[] = new ActivityCallback($this, 'createLullablade', 8);

            if($this->houseSimService->hasInventory('Red Flail') && $this->houseSimService->hasInventory('Scales'))
                $possibilities[] = new ActivityCallback($this, 'createYggdrasil', 8);

            if($this->houseSimService->hasInventory('Grappling Hook') && $this->houseSimService->hasInventory('Gravitational Waves'))
                $possibilities[] = new ActivityCallback($this, 'createGravelingHook', 8);

            if(
                $this->houseSimService->hasInventory('Silver Bar', 1) &&
                $this->houseSimService->hasInventory('Iron Axe', 1) &&
                $this->houseSimService->hasInventory('Lightning in a Bottle', 1)
            )
            {
                $possibilities[] = new ActivityCallback($this, 'createLightningAxe', 8);
            }

            if($this->houseSimService->hasInventory('Crooked Stick'))
            {
                if($this->houseSimService->hasInventory('Mirror'))
                    $possibilities[] = new ActivityCallback($this, 'createMagicMirror', 8);

                if($this->houseSimService->hasInventory('Dark Mirror'))
                    $possibilities[] = new ActivityCallback($this, 'createPandemirrorum', 8);

                if($this->houseSimService->hasInventory('Blackberries') && $this->houseSimService->hasInventory('Goodberries'))
                    $possibilities[] = new ActivityCallback($this, 'createTwiggenBerries', 16);
            }

            if($this->houseSimService->hasInventory('Leaf Spear') && $this->houseSimService->hasInventory('Mint'))
                $possibilities[] = new ActivityCallback($this, 'createSpearmint', 8);

            if($this->houseSimService->hasInventory('Fishing Recorder') && $this->houseSimService->hasInventory('Music Note'))
                $possibilities[] = new ActivityCallback($this, 'createKokopelli', 8);

            if($this->houseSimService->hasInventory('Crystal Ball') && $weather->isNight)
                $possibilities[] = new ActivityCallback($this, 'createMoonPearl', 8);

            if($this->houseSimService->hasInventory('Silver Bar') && $this->houseSimService->hasInventory('Paint Stripper'))
                $possibilities[] = new ActivityCallback($this, 'createAmbrotypicSolvent', 8);

            if($this->houseSimService->hasInventory('Aubergine Scepter'))
                $possibilities[] = new ActivityCallback($this, 'createAubergineCommander', 7);

            if($this->houseSimService->hasInventory('Vicious') && $this->houseSimService->hasInventory('Black Feathers'))
                $possibilities[] = new ActivityCallback($this, 'createBatmanIGuess', 8);

            if($this->houseSimService->hasInventory('Sidereal Leaf Spear'))
                $possibilities[] = new ActivityCallback($this, 'enchantSiderealLeafSpear', 8);

            if($this->houseSimService->hasInventory('Gold Trifecta'))
                $possibilities[] = new ActivityCallback($this, 'createGoldTriskaidecta', 8);

            if($this->houseSimService->hasInventory('Stereotypical Torch'))
                $possibilities[] = new ActivityCallback($this, 'createCrazyHotTorch', 8);

            if($this->houseSimService->hasInventory('Hourglass'))
                $possibilities[] = new ActivityCallback($this, 'createMagicHourglass', 8);

            if($this->houseSimService->hasInventory('Straw Broom') && $this->houseSimService->hasInventory('Witch-hazel'))
                $possibilities[] = new ActivityCallback($this, 'createWitchsBroom', 8);

            if($this->houseSimService->hasInventory('Blackonite'))
            {
                if($this->houseSimService->hasInventory('Fish Head Shovel'))
                    $possibilities[] = new ActivityCallback($this, 'createNephthys', 8);

                if($this->houseSimService->hasInventory('Glass'))
                    $possibilities[] = new ActivityCallback($this, 'createTemperance', 8);

                $possibilities[] = new ActivityCallback($this, 'createBunchOfDice', 8);
            }

            if($this->houseSimService->hasInventory('Gold Tuning Fork'))
                $possibilities[] = new ActivityCallback($this, 'createAstralTuningFork', 8);

            if($this->houseSimService->hasInventory('Feathers'))
                $possibilities[] = new ActivityCallback($this, 'createWings', 8);

            if($this->houseSimService->hasInventory('White Feathers'))
            {
                if($this->houseSimService->hasInventory('Level 2 Sword'))
                    $possibilities[] = new ActivityCallback($this, 'createArmor', 8);

                if($this->houseSimService->hasInventory('Heavy Hammer') && $this->houseSimService->hasInventory('Lightning in a Bottle'))
                    $possibilities[] = new ActivityCallback($this, 'createMjolnir', 8);
            }

            // magic scrolls
            if($this->houseSimService->hasInventory('Paper'))
            {
                if($this->houseSimService->hasInventory('Red'))
                    $possibilities[] = new ActivityCallback($this, 'createFruitScroll', 8);

                if($this->houseSimService->hasInventory('Wheat Flower'))
                    $possibilities[] = new ActivityCallback($this, 'createFarmerScroll', 8);

                if($this->houseSimService->hasInventory('Rice Flower'))
                    $possibilities[] = new ActivityCallback($this, 'createFlowerScroll', 8);

                if($this->houseSimService->hasInventory('Seaweed'))
                    $possibilities[] = new ActivityCallback($this, 'createSeaScroll', 8);

                if($this->houseSimService->hasInventory('Silver Bar'))
                    $possibilities[] = new ActivityCallback($this, 'createSilverScroll', 8);

                if($this->houseSimService->hasInventory('Gold Bar'))
                    $possibilities[] = new ActivityCallback($this, 'createGoldScroll', 8);

                if($this->houseSimService->hasInventory('Musical Scales'))
                    $possibilities[] = new ActivityCallback($this, 'createMusicScroll', 8);
            }

            if($this->houseSimService->hasInventory('Ceremonial Trident'))
            {
                if($this->houseSimService->hasInventory('Seaweed') && $this->houseSimService->hasInventory('Silica Grounds'))
                    $possibilities[] = new ActivityCallback($this, 'createCeremonyOfSandAndSea', 8);

                if($this->houseSimService->hasInventory('Blackonite'))
                    $possibilities[] = new ActivityCallback($this, 'createCeremonyOfShadows', 8);

                if($this->houseSimService->hasInventory('Firestone'))
                    $possibilities[] = new ActivityCallback($this, 'createCeremonyOfFire', 8);
            }

            if($this->houseSimService->hasInventory('Moon Pearl'))
            {
                if($this->houseSimService->hasInventory('Blunderbuss') && $this->houseSimService->hasInventory('Crooked Stick'))
                    $possibilities[] = new ActivityCallback($this, 'createIridescentHandCannon', 8);
                else if($this->houseSimService->hasInventory('Plastic Shovel'))
                    $possibilities[] = new ActivityCallback($this, 'createInvisibleShovel', 8);

                if($this->houseSimService->hasInventory('Elvish Magnifying Glass') && $this->houseSimService->hasInventory('Gravitational Waves'))
                    $possibilities[] = new ActivityCallback($this, 'createWarpingWand', 8);
            }

            if($this->houseSimService->hasInventory('Dark Scales') && $this->houseSimService->hasInventory('Double Scythe'))
                $possibilities[] = new ActivityCallback($this, 'createNewMoon', 8);

            if($this->houseSimService->hasInventory('Farmer\'s Multi-tool') && $this->houseSimService->hasInventory('Smallish Pumpkin'))
                $possibilities[] = new ActivityCallback($this, 'createGizubisShovel', 8);

            if($this->houseSimService->hasInventory('Rapier'))
            {
                if($this->houseSimService->hasInventory('Sunflower') && $this->houseSimService->hasInventory('Dark Matter'))
                    $possibilities[] = new ActivityCallback($this, 'createNightAndDay', 8);
            }

            if($this->houseSimService->hasInventory('Iron Sword'))
            {
                if($this->houseSimService->hasInventory('Musical Scales'))
                    $possibilities[] = new ActivityCallback($this, 'createDancingSword', 8);
            }

            if($this->houseSimService->hasInventory('Poker'))
            {
                if($this->houseSimService->hasInventory('Lightning in a Bottle'))
                    $possibilities[] = new ActivityCallback($this, 'createLightningWand', 8);
            }

            if($this->houseSimService->hasInventory('Decorated Flute') && $this->houseSimService->hasInventory('Quinacridone Magenta Dye'))
                $possibilities[] = new ActivityCallback($this, 'createPraxilla', 8);

            if($this->houseSimService->hasInventory('Compass'))
                $possibilities[] = new ActivityCallback($this, 'createEnchantedCompass', 8);

            if($this->houseSimService->hasInventory('Striped Microcline'))
                $possibilities[] = new ActivityCallback($this, 'createWhisperStone', 8);

            if($this->houseSimService->hasInventory('Fluff'))
            {
                if($this->houseSimService->hasInventory('Snakebite') || $this->houseSimService->hasInventory('Wood\'s Metal'))
                    $possibilities[] = new ActivityCallback($this, 'createCattail', 8);
            }

            $magicSmokeWeight = 1;
        }
        else
        {
            // no quint??
            $magicSmokeWeight = 6;
        }

        if($this->houseSimService->hasInventory('Cattail') && $this->houseSimService->hasInventory('Moon Pearl') && $this->houseSimService->hasInventory('Fish'))
            $possibilities[] = new ActivityCallback($this, 'createMolly', 8);

        if($this->houseSimService->hasInventory('Magic Smoke'))
            $possibilities[] = new ActivityCallback($this, 'magicSmokeToQuint', $magicSmokeWeight);

        if($this->houseSimService->hasInventory('Witch\'s Broom') && $this->houseSimService->hasInventory('Wood\'s Metal'))
            $possibilities[] = new ActivityCallback($this, 'createSnickerblade', 10);

        if($this->houseSimService->hasInventory('Tiny Black Hole') && $this->houseSimService->hasInventory('Mericarp'))
            $possibilities[] = new ActivityCallback($this, 'createSunlessMericarp', 8);

        return $possibilities;
    }

    public function createSnickerblade(ComputedPetSkills  $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStrength()->getTotal());

        if($umbraCheck < 23)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to bind a Wood\'s Metal and a Witch\'s Broom together, but the two objects seemed to naturally repel one another!', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Witch\'s Broom', 1);
            $this->houseSimService->getState()->loseItem('Wood\'s Metal', 1);
            $pet->increaseEsteem($this->squirrel3->rngNextInt(3, 6));
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% bound a Wood\'s Metal and a Witch\'s Broom, creating a Snickerblade!', '')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Snickerblade', $pet, $pet->getName() . ' bound this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ], $activityLog);
        }

        return $activityLog;
    }

    public function createCrazyHotTorch(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck <= 2)
        {
            $pet->increaseEsteem(-1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Stereotypical Torch, but the torch flared up, and %pet:' . $pet->getId() . '.name% got burned! :(', '')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            return $activityLog;
        }
        else if($umbraCheck < 13)
        {
            if($this->squirrel3->rngNextInt(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Stereotypical Torch, but couldn\'t get it hot enough!', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Stereotypical Torch, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Stereotypical Torch', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% enchanted a Stereotypical Torch into a Crazy-hot Torch.', '')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Crazy-hot Torch', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            return $activityLog;
        }
    }

    public function createBunchOfDice(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 15)
        {
            if($this->squirrel3->rngNextInt(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to create a block of glowing dice, but couldn\'t get the shape just right...', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to create a block of glowing dice, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Blackonite', 1);

            if($umbraCheck >= 30 && $this->squirrel3->rngNextInt(1, 5) === 1)
            {
                $pet->increaseEsteem(6);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Glowing Twenty-sided Die from a chunk of Blackonite!', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 30)
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;

                $this->inventoryService->petCollectsItem('Glowing Twenty-sided Die', $pet, $pet->getName() . ' created this from a chunk of Blackonite!', $activityLog);
            }
            else
            {
                $numberOfDice = $this->squirrel3->rngNextInt(3, 5);

                $pet->increaseEsteem($numberOfDice);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a block of glowing dice from a chunk of Blackonite, then gently tapped it to break the dice apart. ' . $numberOfDice . ' were made!', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;

                for($x = 0; $x < $numberOfDice; $x++)
                    $this->inventoryService->petCollectsItem($this->squirrel3->rngNextFromArray([ 'Glowing Four-sided Die', 'Glowing Six-sided Die', 'Glowing Six-sided Die', 'Glowing Six-sided Die', 'Glowing Eight-sided Die' ]), $pet, $pet->getName() . ' got this from a block of glowing dice that they made.', $activityLog);
            }

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);

            return $activityLog;
        }
    }

    public function mermaidEggToQuint(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 12)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to extract Quintessence from a Mermaid Egg, but almost screwed it all up. %pet:' . $pet->getId() . '.name% decided to take a break from it for a bit...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Mermaid Egg', 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% successfully extracted Quintessence from a Mermaid Egg.', '')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' extracted this from a Mermaid Egg.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function magicSmokeToQuint(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + floor(($petWithSkills->getArcana()->getTotal() + $petWithSkills->getScience()->getTotal()) / 2) + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck <= 2)
        {
            $pet->increaseSafety(-1);

            $pet->increasePsychedelic($this->squirrel3->rngNextInt(1, 3));
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to extract Quintessence from Magic Smoke, but accidentally breathed a little bit of the smoke in! :O', '')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Physics' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::SCIENCE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else if($umbraCheck < 12)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to extract Quintessence from Magic Smoke, but almost screwed it all up. %pet:' . $pet->getId() . '.name% decided to take a break from it for a bit...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Physics' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::SCIENCE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Magic Smoke', 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% successfully extracted Quintessence from Magic Smoke.', '')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Physics' ]))
            ;

            $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' extracted this from Magic Smoke.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::SCIENCE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createMagicHourglass(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 15)
        {
            if($this->squirrel3->rngNextInt(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant an Hourglass, but the sand was just too mesmerizing...', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant an Hourglass, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Hourglass', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% enchanted an Hourglass. It\'s _magic_ now!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Magic Hourglass', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createNephthys(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 18)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Fish Bone Shovel, but somehow kept dozing off...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Blackonite', 1);
            $this->houseSimService->getState()->loseItem('Fish Head Shovel', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% enchanted a Fish Head Shovel in Nephthys\'s name!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Nephthys', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createTemperance(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck <= 2)
        {
            $pet->increaseEsteem(-1)->increaseSafety(-4);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant a piece of Glass, but accidentally cut themselves :(', '')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else if($umbraCheck < 18)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to shape a piece of Blackonite into a staff, but the Blackonite was proving difficult to work with...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Blackonite', 1);
            $this->houseSimService->getState()->loseItem('Glass', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made and enchanted Temperance!', 'items/tool/scythe/little-death')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Temperance', $pet, $pet->getName() . ' made this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    /**
     * note: THIS method should be private, but most methods here must be public!
     */
    private function bindCeremonialTrident(ComputedPetSkills $petWithSkills, array $otherMaterials, string $makes): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck <= 2)
        {
            $pet->increaseSafety(-6);
            StatusEffectHelpers::applyStatusEffect($this->em, $pet, StatusEffectEnum::HEX_HEXED, 6 * 60);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Ceremonial Trident, but accidentally hexed themselves, instead! :(', '')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else if($umbraCheck < 20)
        {
            if($this->squirrel3->rngNextInt(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant an Ceremonial Trident, but the enchantment kept refusing to stick >:(', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant an Ceremonial Trident, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(46, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);

            foreach($otherMaterials as $material)
                $this->houseSimService->getState()->loseItem($material, 1);

            $this->houseSimService->getState()->loseItem('Ceremonial Trident', 1);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% used a Ceremonial Trident to materialize the ' . $makes . '!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem($makes, $pet, $pet->getName() . ' made this real.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createCeremonyOfShadows(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        return $this->bindCeremonialTrident($petWithSkills, [ 'Blackonite' ], 'Ceremony of Shadows');
    }

    public function createCeremonyOfFire(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        return $this->bindCeremonialTrident($petWithSkills, [ 'Firestone' ], 'Ceremony of Fire');
    }

    public function createCeremonyOfSandAndSea(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        return $this->bindCeremonialTrident($petWithSkills, [ 'Seaweed', 'Silica Grounds' ], 'Ceremony of Sand and Sea');
    }

    public function createIridescentHandCannon(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());
        $craftsCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($craftsCheck < 10)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Blunderbuss, but didn\'t arrange the material components properly.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(46, 60), PetActivityStatEnum::CRAFT, false);
        }
        else if($umbraCheck < 16)
        {
            if($this->squirrel3->rngNextInt(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Blunderbuss, but the enchantment kept refusing to stick >:(', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Crafting' ]))
                ;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Blunderbuss, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Crafting' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(46, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Blunderbuss', 1);
            $this->houseSimService->getState()->loseItem('Moon Pearl', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $pet->increaseEsteem(5);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made an Iridescent Hand Cannon by extending a Blunderbuss, and binding a Moon Pearl to it!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 21)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Iridescent Hand Cannon', $pet, $pet->getName() . ' bound a Moon Pearl to an extended Blunderbuss, making this!', $activityLog);
            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::UMBRA, PetSkillEnum::CRAFTS ], $activityLog);
        }

        return $activityLog;
    }

    public function createWarpingWand(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());
        $craftsCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($craftsCheck < 12)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant an Elvish Magnifying Glass, but didn\'t arrange the material components properly.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(46, 60), PetActivityStatEnum::CRAFT, false);
        }
        else if($umbraCheck < 18)
        {
            if($this->squirrel3->rngNextInt(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to bind an Elvish Magnifying Glass with a Moon Pearl, but had trouble wrangling the Gravitational Waves...', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Crafting' ]))
                ;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to bind an Elvish Magnifying Glass with a Moon Pearl, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Crafting' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(46, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Gravitational Waves', 1);
            $this->houseSimService->getState()->loseItem('Moon Pearl', 1);
            $this->houseSimService->getState()->loseItem('Elvish Magnifying Glass', 1);
            $pet->increaseEsteem(5);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Warping Wand!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 24)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Warping Wand', $pet, $pet->getName() . ' made this by enchanting an Elvish Magnifying Glass with the power of the Moon!', $activityLog);
            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::UMBRA, PetSkillEnum::CRAFTS ], $activityLog);
        }

        return $activityLog;
    }

    public function createInvisibleShovel(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 18)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Plastic Shovel, but had trouble binding the Quintessence to something so artificial.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(46, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Plastic Shovel', 1);
            $this->houseSimService->getState()->loseItem('Moon Pearl', 1);
            $pet->increaseEsteem(5);

            if($umbraCheck >= 28)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made an Invisible Shovel by binding the power of the moon to a Plastic Shovel! Oh: and there\'s a little Invisibility Juice left over!', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 28)
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;

                $this->inventoryService->petCollectsItem('Invisibility Juice', $pet, $pet->getName() . ' got this as a byproduct when binding an Invisible Shovel!', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made an Invisible Shovel by binding the power of the moon to a Plastic Shovel!', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }

            $this->inventoryService->petCollectsItem('Invisible Shovel', $pet, $pet->getName() . ' made this by binding a Moon Pearl to Plastic Shovel!', $activityLog);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::MAGIC_BIND, true);

            return $activityLog;
        }
    }

    public function createSmilingWand(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());
        $craftsCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($craftsCheck < 10)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Painted Dumbbell, but didn\'t arrange the material components properly.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(46, 60), PetActivityStatEnum::CRAFT, false);
        }
        else if($umbraCheck < 16)
        {
            if($this->squirrel3->rngNextInt(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Painted Dumbbell, but couldn\'t get over how silly it looked!', 'icons/activity-logs/confused');
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Painted Dumbbell, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');

            $activityLog->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Crafting' ]));

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(46, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Wings', 1);
            $this->houseSimService->getState()->loseItem('Glass', 1);
            $this->houseSimService->getState()->loseItem('Quinacridone Magenta Dye', 1);
            $this->houseSimService->getState()->loseItem('Painted Dumbbell', 1);
            $pet->increaseEsteem(5);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Smiling Wand by decorating & enchanting a Painted Dumbbell!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Smiling Wand', $pet, $pet->getName() . ' made this by decorating & enchanting a Painted Dumbbell!', $activityLog);
            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::UMBRA, PetSkillEnum::CRAFTS ], $activityLog);
        }

        return $activityLog;
    }

    public function createGizubisShovel(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 20)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Farmer\'s Multi-tool, but kept messing up the spell.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Smallish Pumpkin', 1);
            $this->houseSimService->getState()->loseItem('Farmer\'s Multi-tool', 1);
            $pet->increaseEsteem(4);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% enchanted a Farmer\'s Multi-tool with one of Gizubi\'s rituals.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Gizubi\'s Shovel', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ], $activityLog);
        }

        return $activityLog;
    }

    public function createNewMoon(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 20)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Double Scythe, but kept messing up the spell.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Dark Scales', 1);
            $this->houseSimService->getState()->loseItem('Double Scythe', 1);
            $pet->increaseEsteem(4);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% enchanted a Double Scythe with Umbral magic...', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('New Moon', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ], $activityLog);
        }

        return $activityLog;
    }

    public function createNightAndDay(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 20)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Rapier, but kept messing up the spell.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Sunflower', 1);
            $this->houseSimService->getState()->loseItem('Dark Matter', 1);
            $this->houseSimService->getState()->loseItem('Rapier', 1);
            $pet->increaseEsteem(6);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% bound Night and Day...', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Night and Day', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ], $activityLog);
        }

        return $activityLog;
    }

    public function createDancingSword(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + max($petWithSkills->getPerception()->getTotal(), ceil($petWithSkills->getMusic()->getTotal() / 4)) + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck === 1)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            $pet->increaseEsteem(-2);

            $this->houseSimService->getState()->loseItem('Musical Scales', 1);

            for($i = 0; $i < 6; $i++)
                $this->inventoryService->petCollectsItem('Music Note', $pet, $pet->getName() . ' accidentally broke apart Musical Scales into Music Notes, of which this is one.', null);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to bind a Dancing Sword, but accidentally dropped the Musical Scales, scattering Music Notes everywhere, and breaking one.', '')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::MUSIC ], $activityLog);
        }
        else if($umbraCheck < 18)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant an Iron Sword, but kept messing up the song.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::MUSIC ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Musical Scales', 1);
            $this->houseSimService->getState()->loseItem('Iron Sword', 1);
            $pet
                ->increaseEsteem(4)
                ->increaseSafety(2)
            ;
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% bound a Dancing Sword...', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Dancing Sword', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA, PetSkillEnum::MUSIC ], $activityLog);
        }

        return $activityLog;
    }

    public function createLightningWand(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 19)
        {
            $pet->increaseSafety(-1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Poker, but kept getting poked by it.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Lightning in a Bottle', 1);
            $this->houseSimService->getState()->loseItem('Poker', 1);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% bound a Wand of Lightning...', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 19)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Wand of Lightning', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ], $activityLog);
        }

        return $activityLog;
    }

    public function createMjolnir(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck <= 2)
        {
            if($pet->hasMerit(MeritEnum::SHOCK_RESISTANT))
            {
                $activityLog = $this->responseService->createActivityLog($pet, ActivityHelpers::PetName($pet) . ' tried to bind some lightning to a Heavy Hammer, but it kept trying to zap them! ' . ActivityHelpers::PetName($pet) . '\'s shock-resistance protected them from any harm, but it was still annoying as heck.', '')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ;

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::MAGIC_BIND, false);
            }
            else
            {
                $pet->increaseSafety(-4);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to bind some lightning to a Heavy Hammer, but accidentally zapped themselves! :(', '')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            }

            return $activityLog;
        }
        else if($umbraCheck < 25)
        {
            if($this->squirrel3->rngNextBool())
            {
                $activityLog = $this->responseService->createActivityLog($pet, ActivityHelpers::PetName($pet) . ' tried to bind lightning to a Heavy Hammer, but the hammer was just NOT having it...', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }
            else if($pet->hasMerit(MeritEnum::SHOCK_RESISTANT))
            {
                $activityLog = $this->responseService->createActivityLog($pet, ActivityHelpers::PetName($pet) . ' tried to bind lightning to a Heavy Hammer, but the lightning was throwing sparks like crazy. ' . ActivityHelpers::PetName($pet) . '\'s shock-resistance protected them, but it was still annoying...', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ;
            }
            else
            {
                $pet->increaseSafety(-2);
                $activityLog = $this->responseService->createActivityLog($pet, ActivityHelpers::PetName($pet) . ' tried to bind lightning to a Heavy Hammer, but the lightning was throwing sparks like crazy, and they kept getting zapped! >:|', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Lightning in a Bottle', 1);
            $this->houseSimService->getState()->loseItem('Heavy Hammer', 1);
            $this->houseSimService->getState()->loseItem('White Feathers', 1);
            $pet->increaseEsteem($this->squirrel3->rngNextInt(4, 8));
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% bound Mjölnir!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 25)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Mjölnir', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);

            return $activityLog;
        }
    }

    public function createBatmanIGuess(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 24)
        {
            switch($this->squirrel3->rngNextInt(1, 4))
            {
                case 1:
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make The Dark Knight, but couldn\'t get Batman out of their head! It was so distracting!', 'icons/activity-logs/confused')
                        ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                    ;

                case 2:
                    $pet->increaseSafety(-4);
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant Vicious, but accidentally cut themselves on the blade! :(', '')
                        ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                    ;

                default: // 3 & 4
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant Vicious, but it kept resisting the enchantment! >:(', 'icons/activity-logs/confused')
                        ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                    ;
            }

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Black Feathers', 1);
            $this->houseSimService->getState()->loseItem('Vicious', 1);
            $pet->increaseEsteem(5);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% bound The Dark Knight!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 24)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('The Dark Knight', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);

            return $activityLog;
        }
    }

    public function createWitchsBroom(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 14)
        {
            if($this->squirrel3->rngNextInt(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Witch\'s Broom, but it kept flying out of their hands half-way through! >:(', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Witch\'s Broom, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Straw Broom', 1);
            $this->houseSimService->getState()->loseItem('Witch-hazel', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% enchanted a broom into a Witch\'s Broom!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Witch\'s Broom', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);

            return $activityLog;
        }
    }

    public function createMagicMirror(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        return $this->createMirror($petWithSkills, 'Magic Mirror', 'Mirror');
    }

    public function createPandemirrorum(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        return $this->createMirror($petWithSkills, 'Pandemirrorum', 'Dark Mirror');
    }

    public function createMirror(ComputedPetSkills $petWithSkills, string $makes, string $mirror): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck <= 2)
        {
            $pet->increaseSafety(-4);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant a ' . $mirror . ', but accidentally cut themselves on it! :(', '')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else if($umbraCheck < 16)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant a ' . $mirror . ', but couldn\'t figure out a good enchantment...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem($mirror, 1);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $pet->increaseEsteem(2);

            $extraItem = null;
            $extraItemMessage = null;
            $usedMerit = false;
            $additionalTime = 0;
            $exp = 0;

            if($this->squirrel3->rngNextInt(1, 4) === 1)
            {
                [ $message, $extraItem, $extraItemMessage, $additionalTime, $usedMerit ] = $this->doMagicMirrorMaze($petWithSkills, $makes);
                $exp = 3;
            }
            else
            {
                $message = $pet->getName() . ' bound a ' . $makes . '!';
                $exp = 2;
            }

            $activityLog = $this->responseService->createActivityLog($pet, $message, '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            if($usedMerit)
                $activityLog->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT);

            $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60) + $additionalTime, PetActivityStatEnum::MAGIC_BIND, true);

            if($extraItem)
                $this->inventoryService->petCollectsItem($extraItem, $pet, $extraItemMessage, $activityLog);

            $this->inventoryService->petCollectsItem($makes, $pet, $pet->getName() . ' bound this.', $activityLog);

            return $activityLog;
        }
    }

    private function doMagicMirrorMaze(ComputedPetSkills $petWithSkills, string $makes): array
    {
        $pet = $petWithSkills->getPet();

        $loot = $this->itemRepository->deprecatedFindOneByName($this->squirrel3->rngNextFromArray([
            'Alien Tissue', 'Apricot PB&J', 'Baking Powder', 'Blue Balloon', 'Candied Ginger', 'Chili Calamari',
            'Deed for Greenhouse Plot', 'Egg Carton', 'Feathers', 'Fortuneless Cookie', 'Glowing Ten-sided Die',
            'Iron Ore', 'Limestone', 'Papadum', 'Password', 'Purple Gummies', 'Red Yogurt', 'Toadstool', 'Welcome Note',
        ]));

        if($petWithSkills->getClimbingBonus()->getTotal() > 0)
        {
            return [
                $pet->getName() . ' bound a ' . $makes . '! As soon as they did so, they were sucked inside, and into a giant maze! They easily climbed their way out of the maze, and out of the mirror! On the way, they found ' . $loot->getNameWithArticle() . '.',
                $loot,
                $pet->getName() . ' found this while climbing out of a maze inside a ' . $makes . '!',
                5,
                false
            ];
        }
        else if($pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
        {
            return [
                $pet->getName() . ' bound a ' . $makes . '! As soon as they did so, they were sucked inside, and into a giant maze! It turns out mazes are really easy if you have an Eidetic Memory! On the way, they found ' . $loot->getNameWithArticle() . ', and a path out of the mirror entirely!',
                $loot,
                $pet->getName() . ' found this while escaping a maze inside a ' . $makes . '!',
                15,
                true
            ];
        }
        else
        {
            $roll = $this->squirrel3->rngNextInt(1, 5 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal());

            if($roll >= 5)
            {
                return [
                    $pet->getName() . ' bound a ' . $makes . '! As soon as they did so, they were sucked inside, and into a giant maze! It took a while, but they were able to find a path out of the maze, and back into the real world! On the way, they found ' . $loot->getNameWithArticle() . '.',
                    $loot,
                    $pet->getName() . ' found this while escaping a maze inside a ' . $makes . '!',
                    30,
                    false
                ];
            }
            else
            {
                return [
                    $pet->getName() . ' bound a ' . $makes . '! As soon as they did so, they were sucked inside, and into a giant maze! They got totally lost for a while; fortunately, they were eventually, and inexplicably, ejected from the mirror and back into the real world!',
                    null,
                    null,
                    30,
                    false
                ];
            }
        }
    }

    public function createArmor(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 18)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Level 2 Sword, but it resisted the spell...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Level 2 Sword', 1);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('White Feathers', 1);
            $pet->increaseEsteem(2);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% bound Armor!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Armor', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ], $activityLog);
            return $activityLog;
        }
    }

    public function createWunderbuss(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal() + $petWithSkills->getMagicBindingBonus());

        $makingItem = $this->itemRepository->deprecatedFindOneByName('Wunderbuss');

        if($umbraCheck === 1)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            $reRoll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

            if($reRoll >= 12)
                return $this->coinSmithingService->makeGoldCoins($petWithSkills, $makingItem);
            else
                return $this->coinSmithingService->spillGold($petWithSkills, $makingItem);
        }
        else if($umbraCheck < 30)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% had an idea for a brilliantly-colored Blunderbuss, but couldn\'t figure out how to realize it...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Rainbow', 1);
            $this->houseSimService->getState()->loseItem('Blunderbuss', 1);
            $this->houseSimService->getState()->loseItem('Ruby Feather', 1);
            $pet->increaseEsteem($this->squirrel3->rngNextInt(4, 8));
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Wunderbuss!!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 30)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' created this!', $activityLog);
            $this->petExperienceService->gainExp($pet, 5, [ PetSkillEnum::UMBRA ], $activityLog);
            return $activityLog;
        }
    }

    public function createAmbuLance(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        $makingItem = $this->itemRepository->deprecatedFindOneByName('Ambu Lance');

        if($umbraCheck < 22)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to imbue a Heavy Lance with Blood Wine, but the Blood Wine proved difficult to work with!', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else
        {
            $this->houseSimService->getState()->loseItem('Blood Wine', 1);
            $this->houseSimService->getState()->loseItem('Heavy Lance', 1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created an Ambu Lance!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 22)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' created this!', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createRubyeye(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 22)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant Armor, but it resisted the spell...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Armor', 1);
            $this->houseSimService->getState()->loseItem('Ruby Feather', 1);
            $pet->increaseEsteem(5);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% bound Rubyeye!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Rubyeye', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::UMBRA ], $activityLog);
        }

        return $activityLog;
    }

    public function createAstralTuningFork(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 14)
        {
            if($this->squirrel3->rngNextInt(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $randomPlace = $this->squirrel3->rngNextFromArray([
                    'Belize', 'Botswana', 'Brunei', 'Cape Verde', 'Croatia', 'Cyprus', 'East Timor', 'Estonia', 'Georgia', 'Grenada', 'Haiti',
                    'Ivory Coast', 'Kiribati', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Malawi', 'Maldives', 'Mauritania', 'Namibia', 'Oman',
                    'Palau', 'Qatar', 'Saint Kitts and Nevis', 'São Tomé and Príncipe', 'Seychelles', 'Suriname', 'Togo', 'Tuvalu', 'Vanuatu',
                    'Yemen'
                ]);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make an Astral Tuning Fork, but messed up the tuning and picked up a regular-ol\' radio station from somewhere in ' . $randomPlace . '!', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make an Astral Tuning Fork, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Gold Tuning Fork', 1);
            $pet->increaseEsteem(2);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% enchanted a regular old Gold Tuning Fork; now it\'s an _Astral_ Tuning Fork!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Astral Tuning Fork', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
        }

        return $activityLog;
    }

    public function createEnchantedCompass(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 14)
        {
            if($this->squirrel3->rngNextInt(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make an Enchanted Compass, but nearly demagnetized it, instead!', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make an Enchanted Compass, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Compass', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% enchanted a regular ol\' Compass; now it\'s an _Enchanted_ Compass!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Enchanted Compass', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);

            return $activityLog;
        }
    }

    public function createWhisperStone(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 14)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to bind a Whisper Stone, but had trouble with the incantations.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Striped Microcline', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% bound a Whisper Stone!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Whisper Stone', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createWings(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 14)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to bind some Wings, but kept mixing up the steps.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $getQuill = 20 < $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Feathers', 1);
            $pet->increaseEsteem(2);

            if($getQuill)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% bound some Wings... and made a Quill with a spare Feather.', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Crafting' ]))
                ;

                $this->inventoryService->petCollectsItem('Quill', $pet, $pet->getName() . ' made this with a spare feather while binding Wings.', $activityLog);

                $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA, PetSkillEnum::CRAFTS ], $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% bound some Wings.', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            }

            $this->inventoryService->petCollectsItem('Wings', $pet, $pet->getName() . ' bound this.', $activityLog);
        }

        return $activityLog;
    }

    public function createGoldTriskaidecta(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck <= 2)
        {
            $pet->increaseSafety(-6);
            StatusEffectHelpers::applyStatusEffect($this->em, $pet, StatusEffectEnum::HEX_HEXED, 6 * 60);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Gold Trifecta, but accidentally hexed themselves, instead! :(', '')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else if($umbraCheck < 14)
        {
            if($this->squirrel3->rngNextInt(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Gold Triskaidecta, but the enchantment wouldn\'t stick! >:(', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Gold Triskaidecta, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Gold Trifecta', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% imbued a Gold Trifecta with the power of the number 13!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Gold Triskaidecta', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createSpearmint(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skillCheck = $this->squirrel3->rngNextInt(1, 20 + min($petWithSkills->getNature()->getTotal(), $petWithSkills->getArcana()->getTotal()) + max($petWithSkills->getDexterity()->getTotal(), $petWithSkills->getPerception()->getTotal()) + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($skillCheck < 16)
        {
            $message = $this->squirrel3->rngNextInt(1, 4) === 1
                ? $pet->getName() . ' tried to bind Mint to a Leaf Spear, but couldn\'t handle THE FRESHNESS.'
                : $pet->getName() . ' tried to bind Mint to a Leaf Spear, but couldn\'t figure it out...'
            ;

            $activityLog = $this->responseService->createActivityLog($pet, $message, 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA, PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Mint', 1);
            $this->houseSimService->getState()->loseItem('Leaf Spear', 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% bound Mint to a Leaf Spear, creating a Spearmint!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->inventoryService->petCollectsItem('Spearmint', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA, PetSkillEnum::NATURE ], $activityLog);
        }

        return $activityLog;
    }

    public function createKokopelli(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skillCheck = $this->squirrel3->rngNextInt(1, 20 + ceil(($petWithSkills->getArcana()->getTotal() + $petWithSkills->getMusic()->getTotal()) / 2) + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($skillCheck < 22)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to bind a Music Note to a Fishing Recorder, but couldn\'t get the pitch right...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA, PetSkillEnum::MUSIC ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Music Note', 1);
            $this->houseSimService->getState()->loseItem('Fishing Recorder', 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% bound a Music Note to a Fishing Recorder, creating a Kokopelli!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 22)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->inventoryService->petCollectsItem('Kokopelli', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA, PetSkillEnum::SCIENCE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createTwiggenBerries(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $craftsCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getNature()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getMagicBindingBonus());
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal());

        if($craftsCheck < 10)
        {
            $pet->increaseSafety(-2);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to bind some berries to a Crooked Stick, but the vines were scratchy, and berries kept falling off, and ugh!', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else if($umbraCheck < 20)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to bind some berries to a Crooked Stick, but kept messing up the enchantment...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA, PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $this->houseSimService->getState()->loseItem('Blackberries', 1);
            $this->houseSimService->getState()->loseItem('Goodberries', 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% bound some berries to a Crooked Stick, creating Twiggen Berries!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Crafting' ]))
            ;

            $this->inventoryService->petCollectsItem('Twiggen Berries', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA, PetSkillEnum::CRAFTS ], $activityLog);
        }

        return $activityLog;
    }

    public function createAmbrotypicSolvent(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skillCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + ceil($petWithSkills->getScience()->getTotal() / 2) + $petWithSkills->getMagicBindingBonus());

        if($pet->hasMerit(MeritEnum::SILVERBLOOD))
            $skillCheck += 5;

        if($skillCheck < 14)
        {
            if($this->squirrel3->rngNextInt(1, 2) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to mix some Ambrotypic Solvent, but wasn\'t confident in their measurements of the ratios...', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Physics' ]))
                ;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to mix some Ambrotypic Solvent, but wasn\'t confident about how to properly infuse the Quintessence...', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Physics' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::SCIENCE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Silver Bar', 1);
            $this->houseSimService->getState()->loseItem('Paint Stripper', 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% mixed some magic Ambrotypic Solvent!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Physics' ]))
            ;

            $this->inventoryService->petCollectsItem('Ambrotypic Solvent', $pet, $pet->getName() . ' mixed this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA, PetSkillEnum::SCIENCE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createNoetalasEye(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($roll >= 18)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Crystal Ball', 1);
            $this->houseSimService->getState()->loseItem('Quinacridone Magenta Dye', 1);
            $this->houseSimService->getState()->loseItem('Meteorite', 1);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% channeled Noetala\'s watchful eye from the depths of space...', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Eye of Noetala', $pet, $pet->getName() . ' channeled this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);

            if($pet->hasMerit(MeritEnum::BEHATTED) && $roll >= 28)
            {
                $watchfulEnchantment = EnchantmentRepository::findOneByName($this->em, 'Watchful');

                if(!$this->hattierService->userHasUnlocked($pet->getOwner(), $watchfulEnchantment))
                {
                    $this->hattierService->unlockAuraDuringPetActivity(
                        $pet,
                        $activityLog,
                        $watchfulEnchantment,
                        'The watchful eye of Noetala focuses its gaze on ' . ActivityHelpers::PetName($pet) . ', attuning with their hat...',
                        'The watchful eye of Noetala focuses its gaze on ' . ActivityHelpers::PetName($pet) . '...',
                        ActivityHelpers::PetName($pet) . ' became aware of the watchful eye of Noetala...'
                    );
                }
            }

            return $activityLog;
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% considered invoking the watchful eye of Noetala, but thought better of it...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
    }

    public function createLotusjar(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + floor(($petWithSkills->getDexterity()->getTotal() + $petWithSkills->getPerception()->getTotal()) / 2) + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($roll >= 24)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Crystal Ball', 1);
            $this->houseSimService->getState()->loseItem('Lotus Flower', 1);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% magically bound a Lotusjar!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 24)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Lotusjar', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::UMBRA ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started making a Lotusjar, but almost accidentally tore the flower, so decided to take a break...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }

        return $activityLog;
    }

    public function createCoolMintScepter(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skillCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() * 2 + $petWithSkills->getMagicBindingBonus());

        if($skillCheck < 16)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to infuse a Wand of Ice with Mint, but it wasn\'t working...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Mint', 1);
            $this->houseSimService->getState()->loseItem('Wand of Ice', 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% infused a Wand of Ice with Mint, creating a Cool Mint Scepter!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->inventoryService->petCollectsItem('Cool Mint Scepter', $pet, $pet->getName() . ' made this by infusing a Wand of Ice with Mint.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);

            return $activityLog;
        }
    }

    public function createMagicPinecone(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skillCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($skillCheck < 12)
        {
            $pet->increaseSafety(-1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to bind Everice to a Pinecone, but almost got frostbitten, and had to put it down for the time being...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Pinecone', 1);
            $this->houseSimService->getState()->loseItem('Everice', 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% bound some Everice to a Pinecone, creating a _Magic_ Pinecone!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 12)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->inventoryService->petCollectsItem('Magic Pinecone', $pet, $pet->getName() . ' made this by binding Everice to a Pinecone.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createSleet(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skillCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($skillCheck < 21)
        {
            $pet->increaseSafety(-1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to bind Everice to an Invisible Shovel, but almost got frostbitten, and had to put it down for the time being...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Invisible Shovel', 1);
            $this->houseSimService->getState()->loseItem('Everice', 1);

            if($skillCheck >= 26)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% bound some Everice to an Invisible Shovel, creating Sleet! Oh: and there\'s a little Invisibility Juice left over!', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 26)
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;

                $this->inventoryService->petCollectsItem('Invisibility Juice', $pet, $pet->getName() . ' got this as a byproduct when binding Sleet!', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% bound some Everice to an Invisible Shovel, creating Sleet!', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 21)
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }

            $this->inventoryService->petCollectsItem('Sleet', $pet, $pet->getName() . ' made this by binding Everice to an Invisible Shovel.', $activityLog);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createFrostbite(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skillCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($skillCheck <= 2)
        {
            $pet->increaseSafety(-4);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to bind Everice to a Scythe, but uttered the wrong sounds during the ritual, and got frostbitten! :(', '')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else if($skillCheck < 16)
        {
            $pet->increaseSafety(-1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to bind Everice to a Scythe, but almost got frostbitten, and had to put it down for the time being...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Everice', 1);
            $this->houseSimService->getState()->loseItem('Scythe', 1);
            $this->houseSimService->getState()->loseItem('String', 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% bound some Everice to a Scythe, creating Frostbite!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->inventoryService->petCollectsItem('Frostbite', $pet, $pet->getName() . ' made this by binding Everice to a Scythe, and making a grip with wound String.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createHexicle(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skillCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($skillCheck <= 2)
        {
            $pet->increaseSafety(-6);
            StatusEffectHelpers::applyStatusEffect($this->em, $pet, StatusEffectEnum::HEX_HEXED, 6 * 60);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to transmute plastic into ice, but accidentally hexed themselves, instead! :(', '')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else if($skillCheck < 16)
        {
            $pet->increaseSafety(-1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to transmute plastic into ice, but the plastic resisted...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Everice', 1);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Nonsenserang', 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% transmuted the plastic of a Nonsenserang into ice, creating a Hexicle!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->inventoryService->petCollectsItem('Hexicle', $pet, $pet->getName() . ' made this transmuting its plastic into ice.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createMoonPearl(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 10)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to bind a moonbeam to a Crystal Ball, but kept missing the moonbeams!', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Crystal Ball', 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% bound a moonbeam to a Crystal Ball, creating a Moon Pearl!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->inventoryService->petCollectsItem('Moon Pearl', $pet, $pet->getName() . ' created this by binding a moonbeam to a Crystal Ball...', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createAubergineCommander(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck <= 2)
        {
            $pet->increaseSafety(-6);
            StatusEffectHelpers::applyStatusEffect($this->em, $pet, StatusEffectEnum::HEX_HEXED, 6 * 60);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant an Aubergine Scepter, but accidentally hexed themselves, instead! :(', '')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else if($umbraCheck < 16)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant an Aubergine Scepter, but the evil was _too strong_.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Aubergine Scepter', 1);

            $message = $this->squirrel3->rngNextInt(1, 10) === 1
                ? $pet->getName() . ' bound an Aubergine Commander! (Was this really such a good idea...?)'
                : $pet->getName() . ' bound an Aubergine Commander!'
            ;

            $activityLog = $this->responseService->createActivityLog($pet, $message, '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->inventoryService->petCollectsItem('Aubergine Commander', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function enchantSiderealLeafSpear(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 18)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Sidereal Leaf Spear, but messed up the calendar calculations.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Sidereal Leaf Spear', 1);
            $pet->increaseEsteem(3);

            $weather = WeatherService::getWeather(new \DateTimeImmutable(), $pet);

            $makes = $weather->isNight ? 'Midnight' : 'Sunrise';

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% enchanted a Sidereal Spear, creating ' . $makes . '!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem($makes, $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            return $activityLog;
        }
    }

    public function createGenericScroll(ComputedPetSkills $petWithSkills, string $uniqueIngredient, string $scroll): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($uniqueIngredient == 'Silver Bar' && $pet->hasMerit(MeritEnum::SILVERBLOOD))
            $umbraCheck += 5;

        $scrollItem = $this->itemRepository->deprecatedFindOneByName($scroll);

        if($umbraCheck < 15)
        {
            if($this->squirrel3->rngNextInt(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to create ' . $scrollItem->getNameWithArticle() . ', but accidentally dropped the Paper at a crucial moment, and smudged the writing!', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to create ' . $scrollItem->getNameWithArticle() . ', but couldn\'t quite remember the steps.', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ], $activityLog);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Paper', 1);
            $this->houseSimService->getState()->loseItem($uniqueIngredient, 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created ' . $scrollItem->getNameWithArticle() . '.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem($scrollItem, $pet, $pet->getName() . ' bound this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ], $activityLog);
        }

        return $activityLog;
    }

    public function createFruitScroll(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        return $this->createGenericScroll($petWithSkills, 'Red', 'Scroll of Fruit');
    }

    public function createFarmerScroll(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        return $this->createGenericScroll($petWithSkills, 'Wheat Flower', 'Farmer\'s Scroll');
    }

    public function createFlowerScroll(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        return $this->createGenericScroll($petWithSkills, 'Rice Flower', 'Scroll of Flowers');
    }

    public function createSeaScroll(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        return $this->createGenericScroll($petWithSkills, 'Seaweed', 'Scroll of the Sea');
    }

    public function createSilverScroll(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        return $this->createGenericScroll($petWithSkills, 'Silver Bar', 'Minor Scroll of Riches');
    }

    public function createGoldScroll(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        return $this->createGenericScroll($petWithSkills, 'Gold Bar', 'Major Scroll of Riches');
    }

    public function createMusicScroll(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        return $this->createGenericScroll($petWithSkills, 'Musical Scales', 'Scroll of Songs');
    }

    public function createMericarp(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 18)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a magic wand, but the Wings kept getting away,  and ' . $pet->getName() . ' spent all their time chasing them down!', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ], $activityLog);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Wings', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $this->houseSimService->getState()->loseItem('Coriander Flower', 1);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Mericarp.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Mericarp', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ], $activityLog);
        }

        return $activityLog;
    }

    public function createSummoningScroll(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 18)
        {
            if($this->squirrel3->rngNextInt(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to create a Monster-summoning Scroll, but accidentally dropped the Paper at a crucial moment, and smudged the writing!', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to create a Monster-summoning Scroll, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Wings', 1);
            $this->houseSimService->getState()->loseItem('Paper', 1);
            $this->houseSimService->getState()->loseItem('Talon', 1);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Monster-summoning Scroll.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Monster-summoning Scroll', $pet, $pet->getName() . ' bound this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ], $activityLog);
        }

        return $activityLog;
    }

    public function createRussetStaff(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 16)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to create a potato staff, but couldn\'t help but wonder if it was really such a good idea...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Wings', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $this->houseSimService->getState()->loseItem('Potato', 1);
            $pet->increaseEsteem(5);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Glowing Russet Staff of Swiftness.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Glowing Russet Staff of Swiftness', $pet, $pet->getName() . ' bound this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ], $activityLog);
        }

        return $activityLog;
    }

    public function createWhiteEpee(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 17)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Rapier, but was having trouble getting the Wings to cooperate...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Wings', 1);
            $this->houseSimService->getState()->loseItem('White Feathers', 1);
            $this->houseSimService->getState()->loseItem('Rapier', 1);
            $pet->increaseEsteem($this->squirrel3->rngNextInt(3, 6));
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% bound a White Épée.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 17)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('White Épée', $pet, $pet->getName() . ' bound this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ], $activityLog);
        }
        return $activityLog;
    }

    public function createFlyingBindle(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 16)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to bind a Flying Bindle, but the wings were being super-uncooperative, and kept trying to fly away!', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Wings', 1);
            $this->houseSimService->getState()->loseItem('Bindle', 1);
            $pet->increaseEsteem(5);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% gifted a Bindle with the power of flight!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Flying Bindle', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createFlyingGrapplingHook(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 16)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to bind a Flying Grappling Hook, but the wings were being super-uncooperative, and kept trying to fly away!', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Wings', 1);
            $this->houseSimService->getState()->loseItem('Grappling Hook', 1);
            $pet->increaseEsteem(5);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% gifted a Grappling Hook with the power of flight!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Flying Grappling Hook', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }
        return $activityLog;
    }

    public function createGravelingHook(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getMagicBindingBonus());
        $scienceCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getScience()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal());

        if($umbraCheck < 12 || $scienceCheck < 12)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to bind a Gravitational Waves to a Grappling Hook, but couldn\'t wrap their head around working with forces governed by such different paradigms...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Physics' ]))
            ;

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::SCIENCE ], $activityLog);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Gravitational Waves', 1);
            $this->houseSimService->getState()->loseItem('Grappling Hook', 1);
            $pet->increaseEsteem(4);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% bound some Gravitational Waves to a Grappling Hook! Now it\'s real good at SMASHING STUFF!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Physics' ]))
            ;
            $this->inventoryService->petCollectsItem('Graveling Hook', $pet, $pet->getName() . ' bound this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA, PetSkillEnum::SCIENCE ], $activityLog);
        }

        return $activityLog;
    }

    public function createYggdrasil(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 17)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant some scales, but almost accidentally brought them to life!', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Scales', 1);
            $this->houseSimService->getState()->loseItem('Red Flail', 1);
            $pet->increaseEsteem(4);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% bound some Scales to a Red Flail, creating a Yggdrasil Branch!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 17)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Yggdrasil Branch', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);

            return $activityLog;
        }
    }

    public function createLullablade(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getMagicBindingBonus());
        $craftsCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal());

        if($craftsCheck <= 2)
        {
            $this->houseSimService->getState()->loseItem('Jar of Fireflies', 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% had an idea for a dreamy blade made of fireflies, but when handling the Jar of Fireflies, accidentally let them all out!', '')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else if($umbraCheck < 17)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% had an idea for a dreamy blade made of Viscaria, but couldn\'t figure out the right enchantment to use...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Viscaria', 1);
            $this->houseSimService->getState()->loseItem('Jar of Fireflies', 1);
            $pet->increaseEsteem(4);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% bound a Lullablade!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 17)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Lullablade', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);

            return $activityLog;
        }
    }

    public function createPraxilla(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 19)
        {
            $pet->increaseSafety(-1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Decorated Flute, but kept messing up the blessing.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Quinacridone Magenta Dye', 1);
            $this->houseSimService->getState()->loseItem('Decorated Flute', 1);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% blessed a Decorated Flute with the skills of an ancient poet...', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Praxilla', $pet, $pet->getName() . ' blessed this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);

            return $activityLog;
        }
    }

    public function createCattail(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck < 16)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to bring some Fluff to life, but kept messing up the spell.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Fluff', 1);
            $this->houseSimService->getState()->loseOneOf($this->squirrel3, [ 'Snakebite', 'Wood\'s Metal' ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% brought some Fluff to life, and bound it to a sword, creating a Cattail!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Cattail', $pet, $pet->getName() . ' created this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);

            return $activityLog;
        }
    }

    public function createMolly(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($umbraCheck <= 2 && $pet->getFood() < 4)
        {
            $pet->increaseEsteem(-2);

            $pet->increaseFood(8);
            $this->houseSimService->getState()->loseItem('Fish', 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was going to feed a Cattail, but ended up eating the Fish themselves...', '')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Eating' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        if($umbraCheck < 20)
        {
            if($this->squirrel3->rngNextBool())
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to further enchant a Cattail, but it refused to eat >:(', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to further enchant a Cattail, but it kept batting the Moon Pearl away >:(', 'icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Fish', 1);
            $this->houseSimService->getState()->loseItem('Cattail', 1);
            $this->houseSimService->getState()->loseItem('Moon Pearl', 1);
            $pet->increaseEsteem(4);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% fed and enchanted a Cattail, creating a Molly!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Molly', $pet, $pet->getName() . ' created this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);

            return $activityLog;
        }
    }

    public function createFimbulvetr(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skillCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($skillCheck <= 2)
        {
            $pet->increaseSafety(-4);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to bind Everice to a Heavy Hammer, but uttered the wrong sounds during the ritual, and got frostbitten! :(', '')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else if($skillCheck < 22)
        {

            $pet->increaseSafety(-1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to bind Everice to a Heavy Hammer, but almost got frostbitten, and had to put it down for the time being...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Heavy Hammer', 1);
            $this->houseSimService->getState()->loseItem('Everice', 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% bound some Everice to a Heavy Hammer, creating Fimbulvetr!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 22)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->inventoryService->petCollectsItem('Fimbulvetr', $pet, $pet->getName() . ' made this by binding Everice to a Heavy Hammer.', $activityLog);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createLightningAxe(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($pet->hasMerit(MeritEnum::SILVERBLOOD))
            $roll += 5;

        if($roll <= 2)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to imbue an Iron Axe with lightning, but accidentally melted it, instead!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 22)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Smithing' ]))
            ;

            $this->houseSimService->getState()->loseItem('Iron Axe', 1);
            $this->inventoryService->petCollectsItem('Iron Bar', $pet, $pet->getName() . ' tried to enchant an Iron Axe, but it melted, instead :|', $activityLog);
        }
        else if($roll >= 22)
        {
            $this->houseSimService->getState()->loseItem('Silver Bar', 1);
            $this->houseSimService->getState()->loseItem('Iron Axe', 1);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Lightning in a Bottle', 1);
            $pet->increaseEsteem($this->squirrel3->rngNextInt(2, 4));
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% silvered an Iron Axe, and imbued with lightning, creating a Lightning Axe.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 22)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Lightning Axe', $pet, $pet->getName() . ' created this by adding a silver-iron blade to a Wand of Lightning.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to imbue an Iron Axe with lightning, but it wasn\'t working out...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding', 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }

        return $activityLog;
    }

    public function createSunlessMericarp(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus());

        if($roll >= 22)
        {
            $this->houseSimService->getState()->loseItem('Tiny Black Hole', 1);
            $this->houseSimService->getState()->loseItem('Mericarp', 1);
            $pet->increaseEsteem(4);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Sunless Mericarp!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 22)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Sunless Mericarp', $pet, $pet->getName() . ' created this by binding a Tiny Black Hole to a Mericarp.', $activityLog);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
        }
        else if($roll == 1)
        {
            $this->houseSimService->getState()->loseItem('Tiny Black Hole', 1);
            $pet->increaseEsteem(-4);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to bind a Tiny Black Hole to a Mericarp, but accidentally evaporated the black hole, instead :|', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 22)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to bind a Tiny Black Hole to a Mericarp, but almost evaporated the black hole, instead!', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }

        return $activityLog;
    }
}
