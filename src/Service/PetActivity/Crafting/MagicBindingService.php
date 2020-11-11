<?php
namespace App\Service\PetActivity\Crafting;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\EnumInvalidValueException;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Model\ActivityCallback;
use App\Repository\ItemRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;

class MagicBindingService
{
    private $inventoryService;
    private $responseService;
    private $petExperienceService;
    private $itemRepository;

    public function __construct(
        InventoryService $inventoryService, ResponseService $responseService, PetExperienceService $petExperienceService,
        ItemRepository $itemRepository
    )
    {
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->petExperienceService = $petExperienceService;
        $this->itemRepository = $itemRepository;
    }

    /**
     * @return ActivityCallback[]
     */
    public function getCraftingPossibilities(Pet $pet, array $quantities): array
    {
        $hour = (int)((new \DateTimeImmutable())->format('G'));

        $isNight = ($hour <= 6 || $hour > 18);

        $possibilities = [];

        if(array_key_exists('Mermaid Egg', $quantities))
            $possibilities[] = new ActivityCallback($this, 'mermaidEggToQuint', 8);

        if(array_key_exists('Wings', $quantities))
        {
            if(array_key_exists('Talon', $quantities) && array_key_exists('Paper', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createSummoningScroll', 8);

            if(array_key_exists('Painted Dumbbell', $quantities) && array_key_exists('Glass', $quantities) && array_key_exists('Quinacridone Magenta Dye', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createSmilingWand', 8);

            if(array_key_exists('Potato', $quantities) && array_key_exists('Crooked Stick', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createRussetStaff', 8);

            if(array_key_exists('Bindle', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createFlyingBindle', 8);

            if(array_key_exists('Grappling Hook', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createFlyingGrapplingHook', 8);
        }

        if(array_key_exists('Armor', $quantities) && array_key_exists('Ruby Feather', $quantities))
            $possibilities[] = new ActivityCallback($this, 'createRubyeye', 8);

        if(array_key_exists('Everice', $quantities))
        {
            // frostbite sucks
            $evericeWeight = $pet->getSafety() < 0 ? 1 : 8;

            if(array_key_exists('Invisible Shovel', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createSleet', $evericeWeight);

            if(array_key_exists('Scythe', $quantities) && array_key_exists('String', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createFrostbite', $evericeWeight);
        }

        if(array_key_exists('Quintessence', $quantities))
        {
            if(array_key_exists('Crystal Ball', $quantities) && $isNight)
                $possibilities[] = new ActivityCallback($this, 'createMoonPearl', 8);

            if(array_key_exists('Silver Bar', $quantities) && array_key_exists('Paint Stripper', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createAmbrotypicSolvent', 8);

            if(array_key_exists('Aubergine Scepter', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createAubergineCommander', 7);

            if(array_key_exists('Vicious', $quantities) && array_key_exists('Black Feathers', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createBatmanIGuess', 8);

            if(array_key_exists('Sidereal Leaf Spear', $quantities))
                $possibilities[] = new ActivityCallback($this, 'enchantSiderealLeafSpear', 8);

            if(array_key_exists('Gold Trifecta', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createGoldTriskaidecta', 8);

            if(array_key_exists('Stereotypical Torch', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createCrazyHotTorch', 8);

            if(array_key_exists('Hourglass', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createMagicHourglass', 8);

            if(array_key_exists('Straw Broom', $quantities) && array_key_exists('Witch-hazel', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createWitchsBroom', 8);

            if(array_key_exists('Blackonite', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createBunchOfDice', 8);

            if(array_key_exists('Gold Tuning Fork', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createAstralTuningFork', 8);

            if(array_key_exists('Mirror', $quantities) && array_key_exists('Crooked Stick', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createMagicMirror', 8);

            if(array_key_exists('Dark Mirror', $quantities) && array_key_exists('Crooked Stick', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createPandemirrorum', 8);

            if(array_key_exists('Feathers', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createWings', 8);

            if(array_key_exists('Level 2 Sword', $quantities) && array_key_exists('White Feathers', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createArmor', 8);

            // magic scrolls
            if(array_key_exists('Paper', $quantities))
            {
                if(array_key_exists('Red', $quantities))
                    $possibilities[] = new ActivityCallback($this, 'createFruitScroll', 8);

                if(array_key_exists('Wheat Flower', $quantities))
                    $possibilities[] = new ActivityCallback($this, 'createFarmerScroll', 8);

                if(array_key_exists('Rice Flower', $quantities))
                    $possibilities[] = new ActivityCallback($this, 'createFlowerScroll', 8);

                if(array_key_exists('Seaweed', $quantities))
                    $possibilities[] = new ActivityCallback($this, 'createSeaScroll', 8);

                if(array_key_exists('Silver Bar', $quantities))
                    $possibilities[] = new ActivityCallback($this, 'createSilverScroll', 8);

                if(array_key_exists('Gold Bar', $quantities))
                    $possibilities[] = new ActivityCallback($this, 'createGoldScroll', 8);

                if(array_key_exists('Musical Scales', $quantities))
                    $possibilities[] = new ActivityCallback($this, 'createMusicScroll', 8);
            }

            if(array_key_exists('Ceremonial Trident', $quantities))
            {
                if(array_key_exists('Seaweed', $quantities) && array_key_exists('Sand Dollar', $quantities))
                    $possibilities[] = new ActivityCallback($this, 'createCeremonyOfSandAndSea', 8);

                if(array_key_exists('Blackonite', $quantities))
                    $possibilities[] = new ActivityCallback($this, 'createCeremonyOfShadows', 8);

                if(array_key_exists('Firestone', $quantities))
                    $possibilities[] = new ActivityCallback($this, 'createCeremonyOfFire', 8);
            }

            if(array_key_exists('Moon Pearl', $quantities))
            {
                if(array_key_exists('Blunderbuss', $quantities) && array_key_exists('Crooked Stick', $quantities))
                    $possibilities[] = new ActivityCallback($this, 'createIridescentHandCannon', 8);
                else if(array_key_exists('Plastic Shovel', $quantities))
                    $possibilities[] = new ActivityCallback($this, 'createInvisibleShovel', 8);

                if(array_key_exists('Elvish Magnifying Glass', $quantities) && array_key_exists('Gravitational Waves', $quantities))
                    $possibilities[] = new ActivityCallback($this, 'createWarpingWand', 8);
            }

            if(array_key_exists('Dark Scales', $quantities) && array_key_exists('Double Scythe', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createNewMoon', 8);

            if(array_key_exists('Farmer\'s Multi-tool', $quantities) && array_key_exists('Smallish Pumpkin', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createGizubisShovel', 8);

            if(array_key_exists('Rapier', $quantities))
            {
                if(array_key_exists('Sunflower', $quantities) && array_key_exists('Dark Matter', $quantities))
                    $possibilities[] = new ActivityCallback($this, 'createNightAndDay', 8);
            }

            if(array_key_exists('Iron Sword', $quantities))
            {
                if(array_key_exists('Musical Scales', $quantities))
                    $possibilities[] = new ActivityCallback($this, 'createDancingSword', 8);
            }

            if(array_key_exists('Poker', $quantities))
            {
                if(array_key_exists('Lightning in a Bottle', $quantities))
                    $possibilities[] = new ActivityCallback($this, 'createLightningWand', 8);
            }

            if(array_key_exists('Decorated Flute', $quantities) && array_key_exists('Quinacridone Magenta Dye', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createPraxilla', 8);

            if(array_key_exists('Compass', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createEnchantedCompass', 8);

            if(array_key_exists('Striped Microcline', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createWhisperStone', 8);

            $magicSmokeWeight = 1;
        }
        else
        {
            // no quint??
            $magicSmokeWeight = 6;
        }

        if(array_key_exists('Magic Smoke', $quantities))
            $possibilities[] = new ActivityCallback($this, 'magicSmokeToQuint', $magicSmokeWeight);

        return $possibilities;
    }

    public function createCrazyHotTorch(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck === 1)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Stereotypical Torch', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Stereotypical Torch, but mishandled the Quintessence. The torch flared up, and ' . $pet->getName() . ' dropped the torch, breaking it :( Some Charcoal was left over, at least...', '');
            $this->inventoryService->petCollectsItem('Charcoal', $pet, $pet->getName() . ' accidentally created this while trying to enchant a Stereotypical Torch.', $activityLog);
            return $activityLog;
        }
        else if($umbraCheck === 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Stereotypical Torch, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 13)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Stereotypical Torch, but couldn\'t get it hot enough!', 'icons/activity-logs/confused');
            else
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Stereotypical Torch, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Stereotypical Torch', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' enchanted a Stereotypical Torch into a Crazy-hot Torch.', '');
            $this->inventoryService->petCollectsItem('Crazy-hot Torch', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }
    }

    public function createBunchOfDice(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a block of glowing dice, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a block of glowing dice, but couldn\'t get the shape just right...', 'icons/activity-logs/confused');
            else
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a block of glowing dice, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $numberOfDice = mt_rand(3, 5);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Blackonite', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem($numberOfDice);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a block of glowing dice from a chunk of Blackonite, then gently tapped it to break the dice apart. ' . $numberOfDice . ' were made!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;

            for($x = 0; $x < $numberOfDice; $x++)
                $this->inventoryService->petCollectsItem(ArrayFunctions::pick_one([ 'Glowing Four-sided Die', 'Glowing Six-sided Die', 'Glowing Six-sided Die', 'Glowing Six-sided Die', 'Glowing Eight-sided Die' ]), $pet, $pet->getName() . ' got this from a block of glowing dice that they made.', $activityLog);

            return $activityLog;
        }
    }

    public function mermaidEggToQuint(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getDexterity());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Mermaid Egg', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to extract Quintessence from a Mermaid Egg, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 12)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' started to extract Quintessence from a Mermaid Egg, but almost screwed it all up. ' . $pet->getName() . ' decided to take a break from it for a bit...', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Mermaid Egg', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' successfully extracted Quintessence from a Mermaid Egg.', '');

            $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' extracted this from a Mermaid Egg.', $activityLog);

            return $activityLog;
        }
    }

    public function magicSmokeToQuint(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + floor(($pet->getUmbra() + $pet->getScience()) / 2) + $pet->getIntelligence() + $pet->getDexterity());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Magic Smoke', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(-1);

            if(mt_rand(1, 2) === 1)
            {
                $pet->increasePsychedelic(mt_rand(1, 3));
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to extract Quintessence from Magic Smoke, but accidentally breathed the smoke in :(', '');
            }
            else
            {
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to extract Quintessence from Magic Smoke, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
            }
        }
        else if($umbraCheck < 12)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' started to extract Quintessence from Magic Smoke, but almost screwed it all up. ' . $pet->getName() . ' decided to take a break from it for a bit...', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Magic Smoke', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::SCIENCE ]);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' successfully extracted Quintessence from Magic Smoke.', '');

            $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' extracted this from Magic Smoke.', $activityLog);

            return $activityLog;
        }
    }

    public function createMagicHourglass(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant an Hourglass, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant an Hourglass, but the sand was just too mesmerizing...', 'icons/activity-logs/confused');
            else
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant an Hourglass, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Hourglass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' enchanted an Hourglass. It\'s _magic_ now!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem('Magic Hourglass', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }
    }

    /**
     * note: THIS method should be private, but most methods here must be public!
     */
    private function bindCeremonialTrident(Pet $pet, array $otherMaterials, string $makes): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Ceremonial Trident, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 20)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(46, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant an Ceremonial Trident, but the enchantment kept refusing to stick >:(', 'icons/activity-logs/confused');
            else
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant an Ceremonial Trident, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);

            foreach($otherMaterials as $material)
                $this->inventoryService->loseItem($material, $pet->getOwner(), LocationEnum::HOME, 1);

            $this->inventoryService->loseItem('Ceremonial Trident', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' used a Ceremonial Trident to materialize the ' . $makes . '!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
            ;
            $this->inventoryService->petCollectsItem($makes, $pet, $pet->getName() . ' made this real.', $activityLog);
            return $activityLog;
        }
    }

    public function createCeremonyOfShadows(Pet $pet): PetActivityLog
    {
        return $this->bindCeremonialTrident($pet, [ 'Blackonite' ], 'Ceremony of Shadows');
    }

    public function createCeremonyOfFire(Pet $pet): PetActivityLog
    {
        return $this->bindCeremonialTrident($pet, [ 'Firestone' ], 'Ceremony of Fire');
    }

    public function createCeremonyOfSandAndSea(Pet $pet): PetActivityLog
    {
        return $this->bindCeremonialTrident($pet, [ 'Seaweed', 'Sand Dollar' ], 'Ceremony of Sand and Sea');
    }

    public function createIridescentHandCannon(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());
        $craftsCheck = mt_rand(1, 20 + $pet->getCrafts() + $pet->getDexterity() + $pet->getIntelligence());

        if($craftsCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to extend a Blunderbuss but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
        }
        else if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Blunderbuss, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($craftsCheck < 10)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(46, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Blunderbuss, but didn\'t arrange the material components properly.', 'icons/activity-logs/confused');
        }
        else if($umbraCheck < 16)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(46, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Blunderbuss, but the enchantment kept refusing to stick >:(', 'icons/activity-logs/confused');
            else
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Blunderbuss, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Blunderbuss', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Moon Pearl', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::UMBRA, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(5);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' made an Iridescent Hand Cannon by extending a Blunderbuss, and binding a Moon Pearl to it!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 21)
            ;
            $this->inventoryService->petCollectsItem('Iridescent Hand Cannon', $pet, $pet->getName() . ' bound a Moon Pearl to an extended Blunderbuss, making this!', $activityLog);
            return $activityLog;
        }
    }

    public function createWarpingWand(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());
        $craftsCheck = mt_rand(1, 20 + $pet->getCrafts() + $pet->getDexterity() + $pet->getIntelligence());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant an Elvish Magnifying Glass, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($craftsCheck < 12)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(46, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant an Elvish Magnifying Glass, but didn\'t arrange the material components properly.', 'icons/activity-logs/confused');
        }
        else if($umbraCheck < 18)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(46, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to bind an Elvish Magnifying Glass with a Moon Pearl, but had trouble wrangling the Gravitational Waves...', 'icons/activity-logs/confused');
            else
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to bind an Elvish Magnifying Glass with a Moon Pearl, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Gravitational Waves', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Moon Pearl', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Elvish Magnifying Glass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::UMBRA, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(5);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' made a Warping Wand!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 24)
            ;
            $this->inventoryService->petCollectsItem('Warping Wand', $pet, $pet->getName() . ' made this by enchanting an Elvish Magnifying Glass with the power of the Moon!', $activityLog);
            return $activityLog;
        }
    }

    public function createInvisibleShovel(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Plastic Shovel, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 18)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(46, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Plastic Shovel, but had trouble binding the Quintessence to something so artificial.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Plastic Shovel', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Moon Pearl', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(5);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' made an Invisible Shovel by binding the power of the moon to a Plastic Shovel!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
            ;
            $this->inventoryService->petCollectsItem('Invisible Shovel', $pet, $pet->getName() . ' made this by binding a Moon Pearl to Plastic Shovel!', $activityLog);
            return $activityLog;
        }
    }

    public function createSmilingWand(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());
        $craftsCheck = mt_rand(1, 20 + $pet->getCrafts() + $pet->getDexterity() + $pet->getIntelligence());

        if($craftsCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to decorate a Painted Dumbbell but broke the Glass :(', 'icons/activity-logs/broke-glass');
        }
        else if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Wings', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Painted Dumbbell, but accidentally disenchanted the Wings :(', '');
            $this->inventoryService->petCollectsItem('Feathers', $pet, 'Left over after ' . $pet->getName() . ' accidentally disenchanted some Wings...', $activityLog);
            return $activityLog;
        }
        else if($craftsCheck < 10)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(46, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Painted Dumbbell, but didn\'t arrange the material components properly.', 'icons/activity-logs/confused');
        }
        else if($umbraCheck < 16)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(46, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Painted Dumbbell, but couldn\'t get over how silly it looked!', 'icons/activity-logs/confused');
            else
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Painted Dumbbell, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Wings', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Quinacridone Magenta Dye', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Painted Dumbbell', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::UMBRA, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(5);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' made a Smiling Wand by decorating & enchanting a Painted Dumbbell!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;
            $this->inventoryService->petCollectsItem('Smiling Wand', $pet, $pet->getName() . ' made this by decorating & enchanting a Painted Dumbbell!', $activityLog);
            return $activityLog;
        }
    }

    public function createGizubisShovel(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 2) === 1)
            {
                $pet->increaseEsteem(-2);
                $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Farmer\'s Multi-tool, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
            }
            else
            {
                $pet->increaseEsteem(-2);
                $this->inventoryService->loseItem('Smallish Pumpkin', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make Gizubi\'s Shovel, but split the Smallish Pumpkin wrong, ruining the spell :(', '');
            }
        }
        else if($umbraCheck < 20)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Farmer\'s Multi-tool, but kept messing up the spell.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Smallish Pumpkin', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Farmer\'s Multi-tool', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(4);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' enchanted a Farmer\'s Multi-tool with one of Gizubi\'s rituals.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
            ;
            $this->inventoryService->petCollectsItem('Gizubi\'s Shovel', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }

    }

    public function createNewMoon(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 2) === 1)
            {
                $pet->increaseEsteem(-2);
                $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Double Scythe, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
            }
            else
            {
                $pet->increaseEsteem(-2);
                $this->inventoryService->loseItem('Dark Scales', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make New Moon, but cracked the Dark Scales :(', '');
            }
        }
        else if($umbraCheck < 20)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Double Scythe, but kept messing up the spell.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Dark Scales', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Double Scythe', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(4);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' enchanted a Double Scythe with Umbral magic...', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
            ;
            $this->inventoryService->petCollectsItem('New Moon', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }
    }

    public function createNightAndDay(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 2) === 1)
            {
                $pet->increaseEsteem(-2);
                $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Rapier, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
            }
            else
            {
                $pet->increaseEsteem(-2);
                $this->inventoryService->loseItem('Sunflower', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make Night and Day, but accidentally tore the Sunflower :(', '');
            }
        }
        else if($umbraCheck < 20)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Rapier, but kept messing up the spell.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Sunflower', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Dark Matter', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Rapier', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(6);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' bound Night and Day...', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
            ;
            $this->inventoryService->petCollectsItem('Night and Day', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }
    }

    public function createDancingSword(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + max($pet->getPerception(), ceil($pet->getMusic() / 4)));

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            $pet->increaseEsteem(-2);

            if(mt_rand(1, 2) === 1)
            {
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
                $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);

                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant an Iron Sword, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
            }
            else
            {
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::MUSIC ]);
                $this->inventoryService->loseItem('Musical Scales', $pet->getOwner(), LocationEnum::HOME, 1);

                for($i = 0; $i < 6; $i++)
                    $this->inventoryService->petCollectsItem('Music Note', $pet, $pet->getName() . ' accidentally broke apart Musical Scales into Music Notes, of which this is one.', null);

                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to bind a Dancing Sword, but accidentally dropped the Musical Scales, scattering Music Notes everywhere, and breaking one.', '');
            }
        }
        else if($umbraCheck < 18)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::MUSIC ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant an Iron Sword, but kept messing up the song.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Musical Scales', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Iron Sword', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA, PetSkillEnum::MUSIC ]);
            $pet
                ->increaseEsteem(4)
                ->increaseSafety(2)
            ;
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' bound a Dancing Sword...', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
            ;
            $this->inventoryService->petCollectsItem('Dancing Sword', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }
    }

    public function createLightningWand(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            $pet->increaseEsteem(-2);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);

            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Poker, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 19)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            $pet->increaseSafety(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Poker, but kept getting poked by it.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Lightning in a Bottle', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Poker', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' bound a Wand of Lightning...', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 19)
            ;
            $this->inventoryService->petCollectsItem('Wand of Lightning', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }
    }

    public function createBatmanIGuess(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            $pet->increaseEsteem(-1);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant Vicious, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 24)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);

            switch(mt_rand(1, 4))
            {
                case 1:
                    return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make The Dark Knight, but couldn\'t get Batman out of their head! It was so distracting!', 'icons/activity-logs/confused');

                case 2:
                    $pet->increaseSafety(-4);
                    return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant Vicious, but accidentally cut themselves on the blade! :(', '');

                default: // 3 & 4
                    return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant Vicious, but it kept resisting the enchantment! >:(', 'icons/activity-logs/confused');
            }
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Black Feathers', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Vicious', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(5);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' bound The Dark Knight!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 24)
            ;
            $this->inventoryService->petCollectsItem('The Dark Knight', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }
    }

    public function createWitchsBroom(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 2) === 1)
            {
                $pet->increaseEsteem(-1);
                $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Witch\'s Broom, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
            }
            else
            {
                $pet->increaseEsteem(-1);
                $this->inventoryService->loseItem('Witch-hazel', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Witch\'s Broom, but snapped the Witch-hazel in half :(', '');
            }
        }
        else if($umbraCheck < 14)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Witch\'s Broom, but it kept flying out of their hands half-way through! >:(', 'icons/activity-logs/confused');
            else
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Witch\'s Broom, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Straw Broom', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Witch-hazel', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' enchanted a broom into a Witch\'s Broom!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
            ;
            $this->inventoryService->petCollectsItem('Witch\'s Broom', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }
    }

    public function createMagicMirror(Pet $pet): PetActivityLog
    {
        return $this->createMirror($pet, 'Magic Mirror', 'Mirror');
    }

    public function createPandemirrorum(Pet $pet): PetActivityLog
    {
        return $this->createMirror($pet, 'Pandemirrorum', 'Dark Mirror');
    }

    public function createMirror(Pet $pet, string $makes, string $mirror): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 2) === 1)
            {
                $pet->increaseEsteem(-1);
                $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a ' . $mirror . ', but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
            }
            else
            {
                $pet->increaseEsteem(-1);
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a ' . $mirror . ', but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
            }
        }
        else if($umbraCheck < 16)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a ' . $mirror . ', but couldn\'t figure out a good enchantment...', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->inventoryService->loseItem($mirror, $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(2);

            $extraItem = null;
            $extraItemMessage = null;
            $usedMerit = false;
            $additionalTime = 0;

            if(mt_rand(1, 4) === 1)
            {
                [ $message, $extraItem, $extraItemMessage, $additionalTime, $usedMerit ] = $this->doMagicMirrorMaze($pet, $makes);
                $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ]);
            }
            else
            {
                $message = $pet->getName() . ' bound a ' . $makes . '!';
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            }

            $activityLog = $this->responseService->createActivityLog($pet, $message, '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;

            if($usedMerit)
                $activityLog->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60) + $additionalTime, PetActivityStatEnum::MAGIC_BIND, true);

            if($extraItem)
                $this->inventoryService->petCollectsItem($extraItem, $pet, $extraItemMessage, $activityLog);

            $this->inventoryService->petCollectsItem($makes, $pet, $pet->getName() . ' bound this.', $activityLog);

            return $activityLog;
        }
    }

    private function doMagicMirrorMaze(Pet $pet, string $makes): array
    {
        $loot = $this->itemRepository->findOneByName(ArrayFunctions::pick_one([
            'Alien Tissue', 'Apricot PB&J', 'Baking Powder', 'Blue Balloon', 'Candied Ginger', 'Chili Calamari',
            'Deed for Greenhouse Plot', 'Egg Carton', 'Feathers', 'Fortuneless Cookie', 'Glowing Six-sided Die',
            'Iron Ore', 'Limestone', 'Papadum', 'Password', 'Purple Gummies', 'Red Yogurt', 'Toadstool', 'Welcome Note',
        ]));

        if($pet->getClimbing() > 0)
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
            $roll = mt_rand(1, 5 + $pet->getIntelligence() + $pet->getPerception());

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

    public function createArmor(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            $pet->increaseEsteem(-1);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Level 2 Sword, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 18)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Level 2 Sword, but it resisted the spell...', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Level 2 Sword', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('White Feathers', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' bound Armor!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
            ;
            $this->inventoryService->petCollectsItem('Armor', $pet, $pet->getName() . ' bound this.', $activityLog);
            return $activityLog;
        }
    }

    public function createRubyeye(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck < 22)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant Armor, but it resisted the spell...', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Armor', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Ruby Feather', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(5);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' bound Rubyeye!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
            ;
            $this->inventoryService->petCollectsItem('Rubyeye', $pet, $pet->getName() . ' bound this.', $activityLog);
            return $activityLog;
        }
    }

    public function createAstralTuningFork(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            $pet->increaseEsteem(-1);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Gold Tuning Fork, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 14)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $randomPlace = ArrayFunctions::pick_one([
                    'Belize', 'Botswana', 'Brunei', 'Cape Verde', 'Croatia', 'Cyprus', 'East Timor', 'Estonia', 'Georgia', 'Grenada', 'Haiti',
                    'Ivory Coast', 'Kiribati', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Malawi', 'Maldives', 'Mauritania', 'Namibia', 'Oman',
                    'Palau', 'Qatar', 'Saint Kitts and Nevis', 'São Tomé and Príncipe', 'Seychelles', 'Suriname', 'Togo', 'Tuvalu', 'Vanuatu',
                    'Yemen'
                ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make an Astral Tuning Fork, but messed up the tuning and picked up a regular-ol\' radio station from somewhere in ' . $randomPlace . '!', 'icons/activity-logs/confused');
            }
            else
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make an Astral Tuning Fork, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Gold Tuning Fork', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' enchanted a regular old Gold Tuning Fork; now it\'s an _Astral_ Tuning Fork!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
            ;
            $this->inventoryService->petCollectsItem('Astral Tuning Fork', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }
    }

    public function createEnchantedCompass(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            $pet->increaseEsteem(-1);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Compass, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 14)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make an Enchanted Compass, but nearly demagnetized it, instead!', 'icons/activity-logs/confused');
            else
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make an Enchanted Compass, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Compass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' enchanted a regular ol\' Compass; now it\'s an _Enchanted_ Compass!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
            ;
            $this->inventoryService->petCollectsItem('Enchanted Compass', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }
    }

    public function createWhisperStone(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            $pet->increaseEsteem(-1);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to bind a Whisper Stone, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 14)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to bind a Whisper Stone, but had trouble with the incantations.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Striped Microcline', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' bound a Whisper Stone!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
            ;
            $this->inventoryService->petCollectsItem('Whisper Stone', $pet, $pet->getName() . ' bound this.', $activityLog);
            return $activityLog;
        }
    }

    public function createWings(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            $pet->increaseEsteem(-1);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to bind some Wings, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 14)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to bind some Wings, but kept mixing up the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Feathers', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' bound some Wings.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
            ;
            $this->inventoryService->petCollectsItem('Wings', $pet, $pet->getName() . ' bound this.', $activityLog);
            return $activityLog;
        }
    }

    public function createGoldTriskaidecta(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            $pet->increaseEsteem(-1);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Gold Trifecta, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 14)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Gold Triskaidecta, but the enchantment wouldn\'t stick! >:(', 'icons/activity-logs/confused');
            else
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Gold Triskaidecta, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Gold Trifecta', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' imbued a Gold Trifecta with the power of the number 13!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
            ;
            $this->inventoryService->petCollectsItem('Gold Triskaidecta', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }
    }

    public function createAmbrotypicSolvent(Pet $pet): PetActivityLog
    {
        $skillCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + ceil($pet->getScience() / 2));

        if($skillCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::SCIENCE ]);

            $lost = mt_rand(1, 3);

            if($lost < 3)
            {
                $pet->increaseEsteem(-1);

                $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to mix some Ambrotypic Solvent, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
            }
            else
            {
                $pet->increaseEsteem(-3);

                $this->inventoryService->loseItem('Silver Bar', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->inventoryService->loseItem('Paint Stripper', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to mix some Ambrotypic Solvent, but got the proportions wrong, ruining the Silver Bar AND the Paint Stripper! :(', '');
            }
        }
        else if($skillCheck < 14)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::SCIENCE ]);

            if(mt_rand(1, 2) === 1)
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to mix some Ambrotypic Solvent, but wasn\'t confident in their measurements of the ratios...', 'icons/activity-logs/confused');
            else
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to mix some Ambrotypic Solvent, but wasn\'t confident about how to properly infuse the Quintessence...', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Silver Bar', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Paint Stripper', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA, PetSkillEnum::SCIENCE ]);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' mixed some magic Ambrotypic Solvent!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
            ;

            $this->inventoryService->petCollectsItem('Ambrotypic Solvent', $pet, $pet->getName() . ' mixed this.', $activityLog);
            return $activityLog;
        }
    }

    public function createSleet(Pet $pet): PetActivityLog
    {
        $skillCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getStamina());

        if($skillCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            $pet->increaseEsteem(-1);

            $this->inventoryService->loseItem('Everice', $pet->getOwner(), LocationEnum::HOME, 1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to bind Everice to an Invisible Shovel, but uttered the wrong sounds during the ritual, and melted the Everice, instead! :(', '');
        }
        else if($skillCheck < 21)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);

            $pet->increaseSafety(-1);

            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to bind Everice to an Invisible Shovel, but almost got frostbitten, and had to put it down for the time being...', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Invisible Shovel', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Everice', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::UMBRA ]);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' bound some Everice to an Invisible Shovel, creating Sleet!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 21)
            ;

            $this->inventoryService->petCollectsItem('Sleet', $pet, $pet->getName() . ' made this by binding Everice to an Invisible Shovel.', $activityLog);
            return $activityLog;
        }
    }

    public function createFrostbite(Pet $pet): PetActivityLog
    {
        $skillCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getStamina());

        if($skillCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            $pet->increaseEsteem(-1);

            $this->inventoryService->loseItem('Everice', $pet->getOwner(), LocationEnum::HOME, 1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to bind Everice to a Scythe, but uttered the wrong sounds during the ritual, and melted the Everice, instead! :(', '');
        }
        else if($skillCheck < 16)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            $pet->increaseSafety(-1);

            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to bind Everice to a Scythe, but almost got frostbitten, and had to put it down for the time being...', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Everice', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Scythe', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' bound some Everice to a Scythe, creating Frostbite!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;

            $this->inventoryService->petCollectsItem('Frostbite', $pet, $pet->getName() . ' made this by binding Everice to a Scythe, and making a grip with wound String.', $activityLog);
            return $activityLog;
        }
    }

    public function createMoonPearl(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            $pet->increaseEsteem(-1);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to bind a moonbeam to a Crystal Ball, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 10)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to bind a moonbeam to a Crystal Ball, but kept missing the moonbeams!', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crystal Ball', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' bound a moonbeam to a Crystal Ball, creating a Moon Pearl!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
            ;

            $this->inventoryService->petCollectsItem('Moon Pearl', $pet, $pet->getName() . ' created this by binding a moonbeam to a Crystal Ball...', $activityLog);
            return $activityLog;
        }
    }

    public function createAubergineCommander(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);

            $pet->increaseEsteem(-1);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant an Aubergine Scepter, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 16)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant an Aubergine Scepter, but the evil was _too strong_.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Aubergine Scepter', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);

            $message = mt_rand(1, 10) === 1
                ? $pet->getName() . ' bound an Aubergine Commander! (Was this really such a good idea...?)'
                : $pet->getName() . ' bound an Aubergine Commander!'
            ;

            $activityLog = $this->responseService->createActivityLog($pet, $message, '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;

            $this->inventoryService->petCollectsItem('Aubergine Commander', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }
    }

    public function enchantSiderealLeafSpear(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);

            $pet->increaseEsteem(-1);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Sidereal Leaf Spear, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 18)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Sidereal Leaf Spear, but messed up the calendar calculations.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Sidereal Leaf Spear', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(3);

            $hour = (int)((new \DateTimeImmutable())->format('G'));

            $makes = ($hour <= 6 || $hour > 18) ? 'Midnight' : 'Sunrise';

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' enchanted a Sidereal Spear, creating ' . $makes . '!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
            ;
            $this->inventoryService->petCollectsItem($makes, $pet, $pet->getName() . ' enchanted this.', $activityLog);
            return $activityLog;
        }
    }

    public function createGenericScroll(Pet $pet, string $uniqueIngredient, string $scroll): PetActivityLog
    {
        $craftsCheck = mt_rand(1, 20 + $pet->getCrafts() + $pet->getDexterity() + $pet->getIntelligence());
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        $scrollItem = $this->itemRepository->findOneByName($scroll);

        if($craftsCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Paper', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create ' . $scrollItem->getNameWithArticle() . ', but accidentally tore the Paper in the process :(', 'icons/activity-logs/torn-to-bits');
        }
        else if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create ' . $scrollItem->getNameWithArticle() . ', but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create ' . $scrollItem->getNameWithArticle() . ', but accidentally dropped the Paper at a crucial moment, and smudged the writing!', 'icons/activity-logs/confused');
            else
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create ' . $scrollItem->getNameWithArticle() . ', but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Paper', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem($uniqueIngredient, $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created ' . $scrollItem->getNameWithArticle() . '.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem($scrollItem, $pet, $pet->getName() . ' bound this.', $activityLog);
            return $activityLog;
        }
    }

    public function createFruitScroll(Pet $pet): PetActivityLog
    {
        return $this->createGenericScroll($pet, 'Red', 'Scroll of Fruit');
    }

    public function createFarmerScroll(Pet $pet): PetActivityLog
    {
        return $this->createGenericScroll($pet, 'Wheat Flower', 'Farmer\'s Scroll');
    }

    public function createFlowerScroll(Pet $pet): PetActivityLog
    {
        return $this->createGenericScroll($pet, 'Rice Flower', 'Scroll of Flowers');
    }

    public function createSeaScroll(Pet $pet): PetActivityLog
    {
        return $this->createGenericScroll($pet, 'Seaweed', 'Scroll of the Sea');
    }

    public function createSilverScroll(Pet $pet): PetActivityLog
    {
        return $this->createGenericScroll($pet, 'Silver Bar', 'Minor Scroll of Riches');
    }

    public function createGoldScroll(Pet $pet): PetActivityLog
    {
        return $this->createGenericScroll($pet, 'Gold Bar', 'Major Scroll of Riches');
    }

    public function createMusicScroll(Pet $pet): PetActivityLog
    {
        return $this->createGenericScroll($pet, 'Musical Scales', 'Scroll of Songs');
    }

    public function createSummoningScroll(Pet $pet): PetActivityLog
    {
        $craftsCheck = mt_rand(1, 20 + $pet->getCrafts() + $pet->getDexterity() + $pet->getIntelligence());
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($craftsCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Paper', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a Monster-summoning Scroll, but accidentally tore the Paper in the process :(', 'icons/activity-logs/torn-to-bits');
        }
        else if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Wings', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a Monster-summoning Scroll, but accidentally tore the Wings back into Feathers :(', '');
            $this->inventoryService->petCollectsItem('Feathers', $pet, $pet->getName() . ' accidentally tore some Wings, leaving only these Feathers.', $activityLog);
            return $activityLog;
        }
        else if($umbraCheck < 18)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a Monster-summoning Scroll, but accidentally dropped the Paper at a crucial moment, and smudged the writing!', 'icons/activity-logs/confused');
            else
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a Monster-summoning Scroll, but couldn\'t quite remember the steps.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Wings', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Paper', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Talon', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Monster-summoning Scroll.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
            ;
            $this->inventoryService->petCollectsItem('Monster-summoning Scroll', $pet, $pet->getName() . ' bound this.', $activityLog);
            return $activityLog;
        }
    }

    public function createRussetStaff(Pet $pet): PetActivityLog
    {
        $craftsCheck = mt_rand(1, 20 + $pet->getCrafts() + $pet->getDexterity() + $pet->getIntelligence());
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($craftsCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a potato staff, but accidentally splintered the Crooked Stick :(', 'icons/activity-logs/broke-stick');
        }
        else if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Wings', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a potato staff, but accidentally tore the Wings back into Feathers :(', '');
            $this->inventoryService->petCollectsItem('Feathers', $pet, $pet->getName() . ' accidentally tore some Wings, leaving only these Feathers.', $activityLog);
            return $activityLog;
        }
        else if($umbraCheck < 16)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a potato staff, but couldn\'t help but wonder if it was really such a good idea...', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Wings', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Potato', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(5);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Glowing Russet Staff of Swiftness.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;
            $this->inventoryService->petCollectsItem('Glowing Russet Staff of Swiftness', $pet, $pet->getName() . ' bound this.', $activityLog);
            return $activityLog;
        }
    }

    public function createFlyingBindle(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Wings', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to bind a Flying Bindle, but accidentally tore the Wings back into Feathers :(', '');
            $this->inventoryService->petCollectsItem('Feathers', $pet, $pet->getName() . ' accidentally tore some Wings, leaving only these Feathers.', $activityLog);
            return $activityLog;
        }
        else if($umbraCheck < 16)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to bind a Flying Bindle, but the wings were being super-uncooperative, and kept trying to fly away!', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Wings', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Bindle', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(5);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' gifted a Bindle with the power of flight!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;
            $this->inventoryService->petCollectsItem('Flying Bindle', $pet, $pet->getName() . ' bound this.', $activityLog);
            return $activityLog;
        }
    }

    public function createFlyingGrapplingHook(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence() + $pet->getPerception());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Wings', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to bind a Flying Grappling Hook, but accidentally tore the Wings back into Feathers :(', '');
            $this->inventoryService->petCollectsItem('Feathers', $pet, $pet->getName() . ' accidentally tore some Wings, leaving only these Feathers.', $activityLog);
            return $activityLog;
        }
        else if($umbraCheck < 16)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to bind a Flying Grappling Hook, but the wings were being super-uncooperative, and kept trying to fly away!', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Wings', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Grappling Hook', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(5);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' gifted a Grappling Hook with the power of flight!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;
            $this->inventoryService->petCollectsItem('Flying Grappling Hook', $pet, $pet->getName() . ' bound this.', $activityLog);
            return $activityLog;
        }
    }

    public function createPraxilla(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence());

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            $pet->increaseEsteem(-2);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);

            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Decorated Flute, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($umbraCheck < 19)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);
            $pet->increaseSafety(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchant a Decorated Flute, but kept messing up the blessing.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Quinacridone Magenta Dye', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Decorated Flute', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' blessed a Decorated Flute with the skills of an ancient poet...', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
            ;
            $this->inventoryService->petCollectsItem('Praxilla', $pet, $pet->getName() . ' blessed this.', $activityLog);
            return $activityLog;
        }
    }
}
