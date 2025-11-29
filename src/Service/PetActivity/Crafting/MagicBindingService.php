<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Service\PetActivity\Crafting;

use App\Entity\PetActivityLog;
use App\Enum\ActivityPersonalityEnum;
use App\Enum\MeritEnum;
use App\Enum\MoonPhaseEnum;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetActivityLogTagEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetBadgeEnum;
use App\Enum\PetSkillEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ActivityHelpers;
use App\Functions\DateFunctions;
use App\Functions\EnchantmentRepository;
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Functions\StatusEffectHelpers;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Service\Clock;
use App\Service\HattierService;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetActivity\Crafting\Helpers\CoinSmithingService;
use App\Service\PetActivity\IPetActivity;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class MagicBindingService implements IPetActivity
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly PetExperienceService $petExperienceService,
        private readonly IRandom $rng,
        private readonly CoinSmithingService $coinSmithingService,
        private readonly HouseSimService $houseSimService,
        private readonly HattierService $hattierService,
        private readonly EntityManagerInterface $em,
        private readonly Clock $clock
    )
    {
    }

    public function preferredWithFullHouse(): bool { return true; }

    public function groupKey(): string { return 'magicBinding'; }

    public function groupDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();

        if($pet->hasStatusEffect(StatusEffectEnum::Wereform))
            return 0;

        $desire = $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal();

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getArcana() + $pet->getTool()->getItem()->getTool()->getMagicBinding();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::CraftingMagic))
            $desire += 4;
        else
            $desire += $this->rng->rngNextInt(1, 4);

        return max(1, (int)round($desire * (1 + $this->rng->rngNextInt(-10, 10) / 100)));
    }

    public function possibilities(ComputedPetSkills $petWithSkills): array
    {
        $moonPhase = DateFunctions::moonPhase($this->clock->now);

        $possibilities = [];

        if($this->houseSimService->hasInventory('Mermaid Egg'))
            $possibilities[] = $this->mermaidEggToQuint(...);

        if($this->houseSimService->hasInventory('Thaumatoxic Cookies'))
            $possibilities[] = $this->thaumatoxicCookiesToQuint(...);

        if($this->houseSimService->hasInventory('Wings'))
        {
            if($this->houseSimService->hasInventory('Coriander Flower') && $this->houseSimService->hasInventory('Crooked Stick'))
                $possibilities[] = $this->createMericarp(...);

            if($this->houseSimService->hasInventory('Talon') && $this->houseSimService->hasInventory('Paper'))
                $possibilities[] = $this->createSummoningScroll(...);

            if($this->houseSimService->hasInventory('Painted Dumbbell') && $this->houseSimService->hasInventory('Glass') && $this->houseSimService->hasInventory('Quinacridone Magenta Dye'))
                $possibilities[] = $this->createSmilingWand(...);

            if($this->houseSimService->hasInventory('Potato') && $this->houseSimService->hasInventory('Crooked Stick'))
                $possibilities[] = $this->createRussetStaff(...);

            if($this->houseSimService->hasInventory('Bindle'))
                $possibilities[] = $this->createFlyingBindle(...);

            if($this->houseSimService->hasInventory('Grappling Hook'))
                $possibilities[] = $this->createFlyingGrapplingHook(...);

            if($this->houseSimService->hasInventory('Rapier') && $this->houseSimService->hasInventory('White Feathers'))
                $possibilities[] = $this->createWhiteEpee(...);

            if($this->houseSimService->hasInventory('Rainbow'))
                $possibilities[] = $this->createRainbowWings(...);

            if($this->houseSimService->hasInventory('Gold Harp') && $this->houseSimService->hasInventory('Jar of Fireflies'))
                $possibilities[] = $this->createFireflyHarp(...);
        }

        if($this->houseSimService->hasInventory('Ruby Feather'))
        {
            if($this->houseSimService->hasInventory('Armor'))
                $possibilities[] = $this->createRubyeye(...);

            if($this->houseSimService->hasInventory('Blunderbuss') && $this->houseSimService->hasInventory('Rainbow') && $this->houseSimService->hasInventory('Gold Bar'))
                $possibilities[] = $this->createWunderbuss(...);
        }

        if($this->houseSimService->hasInventory('Blood Wine'))
        {
            if($this->houseSimService->hasInventory('Heavy Lance'))
                $possibilities[] = $this->createAmbuLance(...);
        }

        if($this->houseSimService->hasInventory('Everice'))
        {
            if($this->houseSimService->hasInventory('Pinecone'))
                $possibilities[] = $this->createMagicPinecone(...);

            if($this->houseSimService->hasInventory('Invisible Shovel'))
                $possibilities[] = $this->createSleet(...);

            if($this->houseSimService->hasInventory('Scythe') && $this->houseSimService->hasInventory('String'))
                $possibilities[] = $this->createFrostbite(...);

            if($this->houseSimService->hasInventory('Nonsenserang') && $this->houseSimService->hasInventory('Quintessence'))
                $possibilities[] = $this->createHexicle(...);

            if($this->houseSimService->hasInventory('Heavy Hammer'))
                $possibilities[] = $this->createFimbulvetr(...);
        }

        if($this->houseSimService->hasInventory('Wand of Ice') && $this->houseSimService->hasInventory('Mint'))
            $possibilities[] = $this->createCoolMintScepter(...);

        if($this->houseSimService->hasInventory('Crystal Ball'))
        {
            if($this->houseSimService->hasInventory('Meteorite') && $this->houseSimService->hasInventory('Quinacridone Magenta Dye'))
                $possibilities[] = $this->createNoetalasEye(...);

            if($this->houseSimService->hasInventory('Tachyon') && $this->houseSimService->hasInventory('Gold Bar'))
                $possibilities[] = $this->createMagicCrystalBall(...);
        }

        if($this->houseSimService->hasInventory('Quintessence'))
        {
            if($this->houseSimService->hasInventory('Crystal Ball') && $this->houseSimService->hasInventory('Lotus Flower'))
                $possibilities[] = $this->createLotusjar(...);

            if($this->houseSimService->hasInventory('Viscaria') && $this->houseSimService->hasInventory('Jar of Fireflies'))
                $possibilities[] = $this->createLullablade(...);

            if($this->houseSimService->hasInventory('Red Flail') && $this->houseSimService->hasInventory('Scales'))
                $possibilities[] = $this->createYggdrasil(...);

            if($this->houseSimService->hasInventory('Grappling Hook') && $this->houseSimService->hasInventory('Gravitational Waves'))
                $possibilities[] = $this->createGravelingHook(...);

            if(
                $this->houseSimService->hasInventory('Silver Bar', 1) &&
                $this->houseSimService->hasInventory('Iron Axe', 1) &&
                $this->houseSimService->hasInventory('Lightning in a Bottle', 1)
            )
            {
                $possibilities[] = $this->createLightningAxe(...);
            }

            if($this->houseSimService->hasInventory('Crooked Stick'))
            {
                if($this->houseSimService->hasInventory('Mirror'))
                    $possibilities[] = $this->createMagicMirror(...);

                if($this->houseSimService->hasInventory('Dark Mirror'))
                    $possibilities[] = $this->createPandemirrorum(...);

                if($this->houseSimService->hasInventory('Blackberries') && $this->houseSimService->hasInventory('Goodberries'))
                    $possibilities[] = $this->createTwiggenBerries(...);

                if($this->houseSimService->hasInventory('Glass Pendulum'))
                    $possibilities[] = $this->createAosSi(...);
            }

            if($this->houseSimService->hasInventory('Leaf Spear') && $this->houseSimService->hasInventory('Mint'))
                $possibilities[] = $this->createSpearmint(...);

            if($this->houseSimService->hasInventory('Fishing Recorder') && $this->houseSimService->hasInventory('Music Note'))
                $possibilities[] = $this->createKokopelli(...);

            if(
                $this->houseSimService->hasInventory('Crystal Ball') &&
                (
                    $moonPhase === MoonPhaseEnum::FullMoon ||
                    $moonPhase === MoonPhaseEnum::WaningGibbous ||
                    $moonPhase === MoonPhaseEnum::WaxingGibbous
                )
            )
            {
                $possibilities[] = $this->createMoonPearl(...);
            }

            if($this->houseSimService->hasInventory('Silver Bar') && $this->houseSimService->hasInventory('Paint Stripper'))
                $possibilities[] = $this->createAmbrotypicSolvent(...);

            if($this->houseSimService->hasInventory('Aubergine Scepter'))
                $possibilities[] = $this->createAubergineCommander(...);

            if($this->houseSimService->hasInventory('Vicious') && $this->houseSimService->hasInventory('Black Feathers'))
                $possibilities[] = $this->createBatmanIGuess(...);

            if($this->houseSimService->hasInventory('Sidereal Leaf Spear'))
                $possibilities[] = $this->enchantSiderealLeafSpear(...);

            if($this->houseSimService->hasInventory('Gold Trifecta'))
                $possibilities[] = $this->createGoldTriskaidecta(...);

            if($this->houseSimService->hasInventory('Stereotypical Torch'))
                $possibilities[] = $this->createCrazyHotTorch(...);

            if($this->houseSimService->hasInventory('Hourglass'))
                $possibilities[] = $this->createMagicHourglass(...);

            if($this->houseSimService->hasInventory('Straw Broom') && $this->houseSimService->hasInventory('Witch-hazel'))
                $possibilities[] = $this->createWitchsBroom(...);

            if($this->houseSimService->hasInventory('Blackonite'))
            {
                if($this->houseSimService->hasInventory('Fish Head Shovel'))
                    $possibilities[] = $this->createNephthys(...);

                if($this->houseSimService->hasInventory('Glass'))
                    $possibilities[] = $this->createTemperance(...);

                $possibilities[] = $this->createBunchOfDice(...);
            }

            if($this->houseSimService->hasInventory('Gold Tuning Fork'))
                $possibilities[] = $this->createAstralTuningFork(...);

            if($this->houseSimService->hasInventory('Feathers'))
                $possibilities[] = $this->createWings(...);

            if($this->houseSimService->hasInventory('White Feathers'))
            {
                if($this->houseSimService->hasInventory('Level 2 Sword'))
                    $possibilities[] = $this->createArmor(...);

                if($this->houseSimService->hasInventory('Heavy Hammer') && $this->houseSimService->hasInventory('Lightning in a Bottle'))
                    $possibilities[] = $this->createMjolnir(...);
            }

            // magic scrolls
            if($this->houseSimService->hasInventory('Paper'))
            {
                if($this->houseSimService->hasInventory('Red') || $this->houseSimService->hasInventory('Orange'))
                    $possibilities[] = $this->createFruitScroll(...);

                if($this->houseSimService->hasInventory('Wheat Flower'))
                    $possibilities[] = $this->createFarmerScroll(...);

                if($this->houseSimService->hasInventory('Rice Flower'))
                    $possibilities[] = $this->createFlowerScroll(...);

                if($this->houseSimService->hasInventory('Seaweed'))
                    $possibilities[] = $this->createSeaScroll(...);

                if($this->houseSimService->hasInventory('Silver Bar'))
                    $possibilities[] = $this->createSilverScroll(...);

                if($this->houseSimService->hasInventory('Gold Bar'))
                    $possibilities[] = $this->createGoldScroll(...);

                if($this->houseSimService->hasInventory('Musical Scales'))
                    $possibilities[] = $this->createMusicScroll(...);
            }

            if($this->houseSimService->hasInventory('Ceremonial Trident'))
            {
                if($this->houseSimService->hasInventory('Seaweed') && $this->houseSimService->hasInventory('Silica Grounds'))
                    $possibilities[] = $this->createCeremonyOfSandAndSea(...);

                if($this->houseSimService->hasInventory('Blackonite'))
                    $possibilities[] = $this->createCeremonyOfShadows(...);

                if($this->houseSimService->hasInventory('Firestone'))
                    $possibilities[] = $this->createCeremonyOfFire(...);
            }

            if($this->houseSimService->hasInventory('Moon Pearl'))
            {
                if($this->houseSimService->hasInventory('Blunderbuss') && $this->houseSimService->hasInventory('Crooked Stick'))
                    $possibilities[] = $this->createIridescentHandCannon(...);
                else if($this->houseSimService->hasInventory('Plastic Shovel'))
                    $possibilities[] = $this->createInvisibleShovel(...);

                if($this->houseSimService->hasInventory('Elvish Magnifying Glass') && $this->houseSimService->hasInventory('Gravitational Waves'))
                    $possibilities[] = $this->createWarpingWand(...);
            }

            if($this->houseSimService->hasInventory('Dark Scales') && $this->houseSimService->hasInventory('Double Scythe'))
                $possibilities[] = $this->createNewMoon(...);

            if($this->houseSimService->hasInventory('Farmer\'s Multi-tool') && $this->houseSimService->hasInventory('Smallish Pumpkin'))
                $possibilities[] = $this->createGizubisShovel(...);

            if($this->houseSimService->hasInventory('Rapier'))
            {
                if($this->houseSimService->hasInventory('Sunflower') && $this->houseSimService->hasInventory('Dark Matter'))
                    $possibilities[] = $this->createNightAndDay(...);

                if($this->houseSimService->hasInventory('Scales') && $this->houseSimService->hasInventory('Tentacle'))
                    $possibilities[] = $this->createSEpee(...);
            }

            if($this->houseSimService->hasInventory('Iron Sword'))
            {
                if($this->houseSimService->hasInventory('Musical Scales'))
                    $possibilities[] = $this->createDancingSword(...);
            }

            if($this->houseSimService->hasInventory('Poker'))
            {
                if($this->houseSimService->hasInventory('Lightning in a Bottle'))
                    $possibilities[] = $this->createLightningWand(...);
            }

            if($this->houseSimService->hasInventory('Decorated Flute') && $this->houseSimService->hasInventory('Quinacridone Magenta Dye'))
                $possibilities[] = $this->createPraxilla(...);

            if($this->houseSimService->hasInventory('Compass'))
                $possibilities[] = $this->createEnchantedCompass(...);

            if($this->houseSimService->hasInventory('Striped Microcline'))
                $possibilities[] = $this->createWhisperStone(...);

            if($this->houseSimService->hasInventory('Fluff'))
            {
                if($this->houseSimService->hasInventory('Snakebite') || $this->houseSimService->hasInventory('Wood\'s Metal'))
                    $possibilities[] = $this->createCattail(...);
            }

            if($this->houseSimService->hasInventory('Snakebite') && $this->houseSimService->hasInventory('Hebenon'))
                $possibilities[] = $this->createMalice(...);

            if($this->houseSimService->hasInventory('Painted Whorl Staff'))
                $possibilities[] = $this->createGyre(...);
        }

        if($this->houseSimService->hasInventory('Aos Sí') && $this->houseSimService->hasInventory('Blackonite'))
            $possibilities[] = $this->createBeanSidhe(...);

        if($this->houseSimService->hasInventory('Cattail') && $this->houseSimService->hasInventory('Moon Pearl') && $this->houseSimService->hasInventory('Fish'))
            $possibilities[] = $this->createMolly(...);

        if($this->houseSimService->hasInventory('Rainbow Wings') && $this->houseSimService->hasInventory('Heavy Hammer'))
            $possibilities[] = $this->createLessHeavyHeavyHammer(...);

        if($this->houseSimService->hasInventory('Witch\'s Broom'))
        {
            if($this->houseSimService->hasInventory('Wood\'s Metal'))
                $possibilities[] = $this->createSnickerblade(...);

            if($this->houseSimService->hasInventory('Evil Feather Duster'))
                $possibilities[] = $this->createWickedBroom(...);
        }

        if($this->houseSimService->hasInventory('Tiny Black Hole') && $this->houseSimService->hasInventory('Mericarp'))
            $possibilities[] = $this->createSunlessMericarp(...);

        if($this->clock->getMonthAndDay() >= 1000 && $this->clock->getMonthAndDay() < 1200)
        {
            if($this->houseSimService->hasInventory('Quintessence') && $this->houseSimService->hasInventory('Quinacridone Magenta Dye') && $this->houseSimService->hasInventory('Mysterious Seed'))
                $possibilities[] = $this->createTerrorSeed(...);
        }

        return $possibilities;
    }

    public function createSnickerblade(ComputedPetSkills  $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStrength()->getTotal());

        if($umbraCheck < 23)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to bind a Wood\'s Metal and a Witch\'s Broom together, but the two objects seemed to naturally repel one another!')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Witch\'s Broom', 1);
            $this->houseSimService->getState()->loseItem('Wood\'s Metal', 1);
            $pet->increaseEsteem($this->rng->rngNextInt(3, 6));
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% bound a Wood\'s Metal and a Witch\'s Broom, creating a Snickerblade!')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Snickerblade', $pet, $pet->getName() . ' bound this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana ], $activityLog);
        }

        return $activityLog;
    }

    public function createWickedBroom(ComputedPetSkills  $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal());

        if($umbraCheck < 23)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to replace a Witch\'s Broom\'s bristles with those of an Evil Feather Duster, but the two objects seemed to naturally repel one another!')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Witch\'s Broom', 1);
            $this->houseSimService->getState()->loseItem('Evil Feather Duster', 1);
            $pet->increaseEsteem($this->rng->rngNextInt(3, 6));
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% replaced a Witch\'s Broom\'s bristles with those of an Evil Feather Duster, creating a Wicked Broom!')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Wicked Broom', $pet, $pet->getName() . ' bound this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana ], $activityLog);
        }

        return $activityLog;
    }

    public function createCrazyHotTorch(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck <= 2 && $petWithSkills->getHasProtectionFromHeat()->getTotal() <= 0)
        {
            $pet->increaseEsteem(-1);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Stereotypical Torch, but the torch flared up, and %pet:' . $pet->getId() . '.name% got burned! :(')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            return $activityLog;
        }
        else if($umbraCheck < 13)
        {
            if($this->rng->rngNextInt(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Stereotypical Torch, but couldn\'t get it hot enough!')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Stereotypical Torch, but couldn\'t quite remember the steps.')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Stereotypical Torch', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% enchanted a Stereotypical Torch into a Crazy-hot Torch.')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Crazy-hot Torch', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            return $activityLog;
        }
    }

    public function createBunchOfDice(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 15)
        {
            if($this->rng->rngNextInt(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to create a block of glowing dice, but couldn\'t get the shape just right...')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to create a block of glowing dice, but couldn\'t quite remember the steps.')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Blackonite', 1);

            if($umbraCheck >= 30 && $this->rng->rngNextInt(1, 5) === 1)
            {
                $pet->increaseEsteem(6);

                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Glowing Twenty-sided Die from a chunk of Blackonite!')
                    ->addInterestingness(PetActivityLogInterestingness::HoHum + 30)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                        PetActivityLogTagEnum::Magic_binding,
                        PetActivityLogTagEnum::Location_At_Home,
                    ]))
                ;

                $this->inventoryService->petCollectsItem('Glowing Twenty-sided Die', $pet, $pet->getName() . ' created this from a chunk of Blackonite!', $activityLog);
            }
            else
            {
                $numberOfDice = $this->rng->rngNextInt(3, 5);

                $pet->increaseEsteem($numberOfDice);

                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a block of glowing dice from a chunk of Blackonite, then gently tapped it to break the dice apart. ' . $numberOfDice . ' were made!')
                    ->addInterestingness(PetActivityLogInterestingness::HoHum + 15)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                        PetActivityLogTagEnum::Magic_binding,
                        PetActivityLogTagEnum::Location_At_Home,
                    ]))
                ;

                for($x = 0; $x < $numberOfDice; $x++)
                    $this->inventoryService->petCollectsItem($this->rng->rngNextFromArray([ 'Glowing Four-sided Die', 'Glowing Six-sided Die', 'Glowing Six-sided Die', 'Glowing Six-sided Die', 'Glowing Eight-sided Die' ]), $pet, $pet->getName() . ' got this from a block of glowing dice that they made.', $activityLog);
            }

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);

            return $activityLog;
        }
    }

    public function thaumatoxicCookiesToQuint(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 12)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% started to extract Quintessence from some Thaumatoxic Cookies, but started getting inexplicable shakes. %pet:' . $pet->getId() . '.name% decided to take a break from it for a bit...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $pet
                ->increaseSafety(-4)
                ->increasePoison(4)
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else if($umbraCheck >= 32)
        {
            $this->houseSimService->getState()->loseItem('Thaumatoxic Cookies', 1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% successfully extracted THREE Quintessence from some Thaumatoxic Cookies.')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::ExtractQuintFromCookies, $activityLog);

            $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' extracted this from some Thaumatoxic Cookies.', $activityLog);
            $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' extracted this from some Thaumatoxic Cookies.', $activityLog);
            $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' extracted this from some Thaumatoxic Cookies.', $activityLog);

            $this->petExperienceService->gainExp($pet, 5, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(65, 80), PetActivityStatEnum::MAGIC_BIND, true);
        }
        else if($umbraCheck >= 22)
        {
            $this->houseSimService->getState()->loseItem('Thaumatoxic Cookies', 1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% successfully extracted TWO Quintessence from some Thaumatoxic Cookies.')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' extracted this from some Thaumatoxic Cookies.', $activityLog);
            $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' extracted this from some Thaumatoxic Cookies.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(55, 70), PetActivityStatEnum::MAGIC_BIND, true);
        }
        else
        {
            $this->houseSimService->getState()->loseItem('Thaumatoxic Cookies', 1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% successfully extracted Quintessence from some Thaumatoxic Cookies.')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' extracted this from some Thaumatoxic Cookies.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function mermaidEggToQuint(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 12)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% started to extract Quintessence from a Mermaid Egg, but almost screwed it all up. %pet:' . $pet->getId() . '.name% decided to take a break from it for a bit...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Mermaid Egg', 1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% successfully extracted Quintessence from a Mermaid Egg.')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' extracted this from a Mermaid Egg.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createMagicHourglass(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 15)
        {
            if($this->rng->rngNextInt(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant an Hourglass, but the sand was just too mesmerizing...')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant an Hourglass, but couldn\'t quite remember the steps.')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Hourglass', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% enchanted an Hourglass. It\'s _magic_ now!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 15)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Magic Hourglass', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createNephthys(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 18)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Fish Bone Shovel, but somehow kept dozing off...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Blackonite', 1);
            $this->houseSimService->getState()->loseItem('Fish Head Shovel', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% enchanted a Fish Head Shovel in Nephthys\'s name!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 18)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Nephthys', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createTemperance(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck <= 2)
        {
            $pet->increaseEsteem(-1)->increaseSafety(-4);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a piece of Glass, but accidentally cut themselves :(')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding', 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else if($umbraCheck < 18)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to shape a piece of Blackonite into a staff, but the Blackonite was proving difficult to work with...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding', 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Blackonite', 1);
            $this->houseSimService->getState()->loseItem('Glass', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% made and enchanted Temperance!')
                ->setIcon('items/tool/scythe/little-death')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 18)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding', 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Temperance', $pet, $pet->getName() . ' made this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    /**
     * note: THIS method should be private, but most methods here must be public!
     */
    private function bindCeremonialTrident(ComputedPetSkills $petWithSkills, array $otherMaterials, string $makes): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck <= 2)
        {
            $pet->increaseSafety(-6);
            StatusEffectHelpers::applyStatusEffect($this->em, $pet, StatusEffectEnum::HexHexed, 6 * 60);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Ceremonial Trident, but accidentally hexed themselves, instead! :(')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else if($umbraCheck < 20)
        {
            if($this->rng->rngNextInt(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant an Ceremonial Trident, but the enchantment kept refusing to stick >:(')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant an Ceremonial Trident, but couldn\'t quite remember the steps.')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(46, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);

            foreach($otherMaterials as $material)
                $this->houseSimService->getState()->loseItem($material, 1);

            $this->houseSimService->getState()->loseItem('Ceremonial Trident', 1);
            $pet->increaseEsteem(3);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% used a Ceremonial Trident to materialize the ' . $makes . '!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 20)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem($makes, $pet, $pet->getName() . ' made this real.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 75), PetActivityStatEnum::MAGIC_BIND, true);
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
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());
        $craftsCheck = $this->rng->rngSkillRoll($petWithSkills->getCrafts()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($craftsCheck < 10)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Blunderbuss, but didn\'t arrange the material components properly.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding', 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(46, 60), PetActivityStatEnum::CRAFT, false);
        }
        else if($umbraCheck < 16)
        {
            if($this->rng->rngNextInt(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Blunderbuss, but the enchantment kept refusing to stick >:(')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding', 'Crafting' ]))
                ;
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Blunderbuss, but couldn\'t quite remember the steps.')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding', 'Crafting' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(46, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Blunderbuss', 1);
            $this->houseSimService->getState()->loseItem('Moon Pearl', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $pet->increaseEsteem(5);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% made an Iridescent Hand Cannon by extending a Blunderbuss, and binding a Moon Pearl to it!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 21)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Iridescent Hand Cannon', $pet, $pet->getName() . ' bound a Moon Pearl to an extended Blunderbuss, making this!', $activityLog);
            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::Arcana, PetSkillEnum::Crafts ], $activityLog);
        }

        return $activityLog;
    }

    public function createWarpingWand(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());
        $craftsCheck = $this->rng->rngSkillRoll($petWithSkills->getCrafts()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($craftsCheck < 12)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant an Elvish Magnifying Glass, but didn\'t arrange the material components properly.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding', 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(46, 60), PetActivityStatEnum::CRAFT, false);
        }
        else if($umbraCheck < 18)
        {
            if($this->rng->rngNextInt(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to bind an Elvish Magnifying Glass with a Moon Pearl, but had trouble wrangling the Gravitational Waves...')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding', 'Crafting' ]))
                ;
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to bind an Elvish Magnifying Glass with a Moon Pearl, but couldn\'t quite remember the steps.')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding', 'Crafting' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(46, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Gravitational Waves', 1);
            $this->houseSimService->getState()->loseItem('Moon Pearl', 1);
            $this->houseSimService->getState()->loseItem('Elvish Magnifying Glass', 1);
            $pet->increaseEsteem(5);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% made a Warping Wand!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 24)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Warping Wand', $pet, $pet->getName() . ' made this by enchanting an Elvish Magnifying Glass with the power of the Moon!', $activityLog);
            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::Arcana, PetSkillEnum::Crafts ], $activityLog);
        }

        return $activityLog;
    }

    public function createInvisibleShovel(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 18)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Plastic Shovel, but had trouble binding the Quintessence to something so artificial.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(46, 60), PetActivityStatEnum::MAGIC_BIND, false);

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
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% made an Invisible Shovel by binding the power of the moon to a Plastic Shovel! Oh: and there\'s a little Invisibility Juice left over!')
                    ->addInterestingness(PetActivityLogInterestingness::HoHum + 28)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                        PetActivityLogTagEnum::Magic_binding,
                        PetActivityLogTagEnum::Location_At_Home,
                    ]))
                ;

                $this->inventoryService->petCollectsItem('Invisibility Juice', $pet, $pet->getName() . ' got this as a byproduct when binding an Invisible Shovel!', $activityLog);
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% made an Invisible Shovel by binding the power of the moon to a Plastic Shovel!')
                    ->addInterestingness(PetActivityLogInterestingness::HoHum + 18)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                        PetActivityLogTagEnum::Magic_binding,
                        PetActivityLogTagEnum::Location_At_Home,
                    ]))
                ;
            }

            $this->inventoryService->petCollectsItem('Invisible Shovel', $pet, $pet->getName() . ' made this by binding a Moon Pearl to Plastic Shovel!', $activityLog);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 75), PetActivityStatEnum::MAGIC_BIND, true);

            return $activityLog;
        }
    }

    public function createSmilingWand(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());
        $craftsCheck = $this->rng->rngSkillRoll($petWithSkills->getCrafts()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($craftsCheck < 10)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Painted Dumbbell, but didn\'t arrange the material components properly.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding', 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(46, 60), PetActivityStatEnum::CRAFT, false);
        }
        else if($umbraCheck < 16)
        {
            if($this->rng->rngNextInt(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Painted Dumbbell, but couldn\'t get over how silly it looked!');
            else
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Painted Dumbbell, but couldn\'t quite remember the steps.');

            $activityLog
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding', 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(46, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Wings', 1);
            $this->houseSimService->getState()->loseItem('Glass', 1);
            $this->houseSimService->getState()->loseItem('Quinacridone Magenta Dye', 1);
            $this->houseSimService->getState()->loseItem('Painted Dumbbell', 1);
            $pet->increaseEsteem(5);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% made a Smiling Wand by decorating & enchanting a Painted Dumbbell!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 16)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                    PetActivityLogTagEnum::Crafting,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Smiling Wand', $pet, $pet->getName() . ' made this by decorating & enchanting a Painted Dumbbell!', $activityLog);
            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::Arcana, PetSkillEnum::Crafts ], $activityLog);
        }

        return $activityLog;
    }

    public function createGizubisShovel(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 20)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Farmer\'s Multi-tool, but kept messing up the spell.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Smallish Pumpkin', 1);
            $this->houseSimService->getState()->loseItem('Farmer\'s Multi-tool', 1);
            $pet->increaseEsteem(4);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% enchanted a Farmer\'s Multi-tool with one of Gizubi\'s rituals.')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 20)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Gizubi\'s Shovel', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana ], $activityLog);
        }

        return $activityLog;
    }

    public function createNewMoon(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 20)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Double Scythe, but kept messing up the spell.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Dark Scales', 1);
            $this->houseSimService->getState()->loseItem('Double Scythe', 1);
            $pet->increaseEsteem(4);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% enchanted a Double Scythe with Umbral magic...')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 20)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('New Moon', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana ], $activityLog);
        }

        return $activityLog;
    }

    public function createNightAndDay(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 20)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Rapier, but kept messing up the spell.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Sunflower', 1);
            $this->houseSimService->getState()->loseItem('Dark Matter', 1);
            $this->houseSimService->getState()->loseItem('Rapier', 1);
            $pet->increaseEsteem(6);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% bound Night and Day...')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 20)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Night and Day', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana ], $activityLog);
        }

        return $activityLog;
    }

    public function createSEpee(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 20)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Rapier, but kept messing up the spell.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Scales', 1);
            $this->houseSimService->getState()->loseItem('Tentacle', 1);
            $this->houseSimService->getState()->loseItem('Rapier', 1);
            $pet->increaseEsteem(6);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% bound S. Epée...')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 20)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('S. Epée', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana ], $activityLog);
        }

        return $activityLog;
    }

    public function createDancingSword(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + max($petWithSkills->getPerception()->getTotal(), (int)ceil($petWithSkills->getMusic()->getTotal() / 4)) + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck === 1)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            $pet->increaseEsteem(-2);

            $this->houseSimService->getState()->loseItem('Musical Scales', 1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to bind a Dancing Sword, but accidentally dropped the Musical Scales, scattering Music Notes everywhere, and breaking one.')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            for($i = 0; $i < 6; $i++)
                $this->inventoryService->petCollectsItem('Music Note', $pet, $pet->getName() . ' accidentally broke apart Musical Scales into Music Notes, of which this is one.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana, PetSkillEnum::Music ], $activityLog);
        }
        else if($umbraCheck < 18)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant an Iron Sword, but kept messing up the song.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana, PetSkillEnum::Music ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Musical Scales', 1);
            $this->houseSimService->getState()->loseItem('Iron Sword', 1);
            $pet
                ->increaseEsteem(4)
                ->increaseSafety(2)
            ;
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% bound a Dancing Sword...')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 18)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Dancing Sword', $pet, $pet->getName() . ' enchanted this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana, PetSkillEnum::Music ], $activityLog);
        }

        return $activityLog;
    }

    public function createLightningWand(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 19)
        {
            $pet->increaseSafety(-1);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Poker, but kept getting poked by it.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Lightning in a Bottle', 1);
            $this->houseSimService->getState()->loseItem('Poker', 1);
            $pet->increaseEsteem(3);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% bound a Wand of Lightning...')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 19)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Wand of Lightning', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana ], $activityLog);
        }

        return $activityLog;
    }

    public function createMjolnir(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck <= 2)
        {
            if($pet->hasMerit(MeritEnum::SHOCK_RESISTANT))
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' tried to bind some lightning to a Heavy Hammer, but it kept trying to zap them! ' . ActivityHelpers::PetName($pet) . '\'s shock-resistance protected them from any harm, but it was still annoying as heck.')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                    ->addInterestingness(PetActivityLogInterestingness::ActivityUsingMerit)
                ;

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::MAGIC_BIND, false);
            }
            else
            {
                $pet->increaseSafety(-4);

                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to bind some lightning to a Heavy Hammer, but accidentally zapped themselves! :(')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                ;

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            }

            return $activityLog;
        }
        else if($umbraCheck < 25)
        {
            if($this->rng->rngNextBool())
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' tried to bind lightning to a Heavy Hammer, but the hammer was just NOT having it...')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }
            else if($pet->hasMerit(MeritEnum::SHOCK_RESISTANT))
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' tried to bind lightning to a Heavy Hammer, but the lightning was throwing sparks like crazy. ' . ActivityHelpers::PetName($pet) . '\'s shock-resistance protected them, but it was still annoying...')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                    ->addInterestingness(PetActivityLogInterestingness::ActivityUsingMerit)
                ;
            }
            else
            {
                $pet->increaseSafety(-2);
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' tried to bind lightning to a Heavy Hammer, but the lightning was throwing sparks like crazy, and they kept getting zapped! >:|')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Lightning in a Bottle', 1);
            $this->houseSimService->getState()->loseItem('Heavy Hammer', 1);
            $this->houseSimService->getState()->loseItem('White Feathers', 1);
            $pet->increaseEsteem($this->rng->rngNextInt(4, 8));
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% bound Mjölnir!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 25)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Mjölnir', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);

            return $activityLog;
        }
    }

    public function createBatmanIGuess(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 24)
        {
            switch($this->rng->rngNextInt(1, 4))
            {
                case 1:
                    $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make The Dark Knight, but couldn\'t get Batman out of their head! It was so distracting!')
                        ->setIcon('icons/activity-logs/confused')
                        ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                    ;
                    break;

                case 2:
                    $pet->increaseSafety(-4);
                    $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant Vicious, but accidentally cut themselves on the blade! :(')
                        ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                    ;
                    break;

                default: // 3 & 4
                    $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant Vicious, but it kept resisting the enchantment! >:(')
                        ->setIcon('icons/activity-logs/confused')
                        ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                    ;
                    break;
            }

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Black Feathers', 1);
            $this->houseSimService->getState()->loseItem('Vicious', 1);
            $pet->increaseEsteem(5);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% bound The Dark Knight!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 24)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('The Dark Knight', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);

            return $activityLog;
        }
    }

    public function createWitchsBroom(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 14)
        {
            if($this->rng->rngNextInt(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Witch\'s Broom, but it kept flying out of their hands half-way through! >:(')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Witch\'s Broom, but couldn\'t quite remember the steps.')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Straw Broom', 1);
            $this->houseSimService->getState()->loseItem('Witch-hazel', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% enchanted a broom into a Witch\'s Broom!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 14)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Witch\'s Broom', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);

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
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck <= 2)
        {
            $pet->increaseSafety(-4);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a ' . $mirror . ', but accidentally cut themselves on it! :(')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else if($umbraCheck < 16)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a ' . $mirror . ', but couldn\'t figure out a good enchantment...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
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

            if($this->rng->rngNextInt(1, 4) === 1)
            {
                [ $message, $extraItem, $extraItemMessage, $additionalTime, $usedMerit ] = $this->doMagicMirrorMaze($petWithSkills, $makes);
                $exp = 3;
            }
            else
            {
                $message = $pet->getName() . ' bound a ' . $makes . '!';
                $exp = 2;
            }

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $message)
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 16)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            if($usedMerit)
                $activityLog->addInterestingness(PetActivityLogInterestingness::ActivityUsingMerit);

            $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60) + $additionalTime, PetActivityStatEnum::MAGIC_BIND, true);

            if($extraItem)
                $this->inventoryService->petCollectsItem($extraItem, $pet, $extraItemMessage, $activityLog);

            $this->inventoryService->petCollectsItem($makes, $pet, $pet->getName() . ' bound this.', $activityLog);

            return $activityLog;
        }
    }

    private function doMagicMirrorMaze(ComputedPetSkills $petWithSkills, string $makes): array
    {
        $pet = $petWithSkills->getPet();

        $loot = ItemRepository::findOneByName($this->em, $this->rng->rngNextFromArray([
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
            $roll = $this->rng->rngNextInt(1, 5 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal());

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
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 18)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Level 2 Sword, but it resisted the spell...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Level 2 Sword', 1);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('White Feathers', 1);
            $pet->increaseEsteem(2);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% bound Armor!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 18)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Armor', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana ], $activityLog);
            return $activityLog;
        }
    }

    public function createWunderbuss(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        $makingItem = ItemRepository::findOneByName($this->em, 'Wunderbuss');

        if($umbraCheck === 1)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            $reRoll = $this->rng->rngSkillRoll($petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

            if($reRoll >= 12)
                return $this->coinSmithingService->makeGoldCoins($petWithSkills, $makingItem);
            else
                return $this->coinSmithingService->spillGold($petWithSkills, $makingItem);
        }
        else if($umbraCheck < 30)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% had an idea for a brilliantly-colored Blunderbuss, but couldn\'t figure out how to realize it...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding', 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Rainbow', 1);
            $this->houseSimService->getState()->loseItem('Blunderbuss', 1);
            $this->houseSimService->getState()->loseItem('Ruby Feather', 1);
            $pet->increaseEsteem($this->rng->rngNextInt(4, 8));
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Wunderbuss!!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 30)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding', 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' created this!', $activityLog);
            $this->petExperienceService->gainExp($pet, 5, [ PetSkillEnum::Arcana ], $activityLog);
            return $activityLog;
        }
    }

    public function createRainbowWings(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck === 1)
        {
            $pet->increaseEsteem(-4);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to bind a Rainbow to some Wings, but lost sight of the Rainbow for a second, and was unable to find it again :(')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else if($umbraCheck < 26)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to bind a Rainbow to some Wings, but ended up wasting all their time making sure the Wings didn\'t fly off, while making sure the Rainbow didn\'t fade from view.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Rainbow', 1);
            $this->houseSimService->getState()->loseItem('Wings', 1);
            $pet->increaseEsteem($this->rng->rngNextInt(4, 8));
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created Rainbow Wings!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 30)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Rainbow Wings', $pet, $pet->getName() . ' created this!', $activityLog);
            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::Arcana ], $activityLog);
            return $activityLog;
        }
    }

    public function createFireflyHarp(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $magicBindingCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($magicBindingCheck < 22)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' tried to attract some fireflies to a Gold Harp, but they weren\'t going for it...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else
        {
            $this->houseSimService->getState()->loseItem('Gold Harp', 1);
            $this->houseSimService->getState()->loseItem('Jar of Fireflies', 1);
            $this->houseSimService->getState()->loseItem('Wings', 1);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Firefly Harp!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 22)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Firefly Harp', $pet, $pet->getName() . ' created this!', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createAmbuLance(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        $makingItem = ItemRepository::findOneByName($this->em, 'Ambu Lance');

        if($umbraCheck < 22)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to imbue a Heavy Lance with Blood Wine, but the Blood Wine proved difficult to work with!')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else
        {
            $this->houseSimService->getState()->loseItem('Blood Wine', 1);
            $this->houseSimService->getState()->loseItem('Heavy Lance', 1);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created an Ambu Lance!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 22)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' created this!', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createRubyeye(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 22)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant Armor, but it resisted the spell...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Armor', 1);
            $this->houseSimService->getState()->loseItem('Ruby Feather', 1);
            $pet->increaseEsteem(5);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% bound Rubyeye!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 18)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Rubyeye', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::Arcana ], $activityLog);
        }

        return $activityLog;
    }

    public function createAstralTuningFork(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 14)
        {
            if($this->rng->rngNextInt(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $randomPlace = $this->rng->rngNextFromArray([
                    'Belize', 'Botswana', 'Brunei', 'Cape Verde', 'Croatia', 'Cyprus', 'East Timor', 'Estonia', 'Georgia', 'Grenada', 'Haiti',
                    'Ivory Coast', 'Kiribati', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Malawi', 'Maldives', 'Mauritania', 'Namibia', 'Oman',
                    'Palau', 'Qatar', 'Saint Kitts and Nevis', 'São Tomé and Príncipe', 'Seychelles', 'Suriname', 'Togo', 'Tuvalu', 'Vanuatu',
                    'Yemen'
                ]);

                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make an Astral Tuning Fork, but messed up the tuning and picked up a regular-ol\' radio station from somewhere in ' . $randomPlace . '!')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make an Astral Tuning Fork, but couldn\'t quite remember the steps.')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Gold Tuning Fork', 1);
            $pet->increaseEsteem(2);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% enchanted a regular old Gold Tuning Fork; now it\'s an _Astral_ Tuning Fork!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 14)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Astral Tuning Fork', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
        }

        return $activityLog;
    }

    public function createEnchantedCompass(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 14)
        {
            if($this->rng->rngNextInt(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make an Enchanted Compass, but nearly demagnetized it, instead!')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make an Enchanted Compass, but couldn\'t quite remember the steps.')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Compass', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% enchanted a regular ol\' Compass; now it\'s an _Enchanted_ Compass!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 14)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Enchanted Compass', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);

            return $activityLog;
        }
    }

    public function createWhisperStone(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 14)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to bind a Whisper Stone, but had trouble with the incantations.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Striped Microcline', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% bound a Whisper Stone!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 14)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Whisper Stone', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createWings(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 14)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to bind some Wings, but kept mixing up the steps.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $getQuill = 20 < $this->rng->rngSkillRoll($petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Feathers', 1);
            $pet->increaseEsteem(2);

            if($getQuill)
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% bound some Wings... and made a Quill with a spare Feather.')
                    ->addInterestingness(PetActivityLogInterestingness::HoHum + 20)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                        PetActivityLogTagEnum::Magic_binding,
                        PetActivityLogTagEnum::Crafting,
                        PetActivityLogTagEnum::Location_At_Home,
                    ]))
                ;

                $this->inventoryService->petCollectsItem('Quill', $pet, $pet->getName() . ' made this with a spare feather while binding Wings.', $activityLog);

                $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana, PetSkillEnum::Crafts ], $activityLog);
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% bound some Wings.')
                    ->addInterestingness(PetActivityLogInterestingness::HoHum + 14)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                        PetActivityLogTagEnum::Magic_binding,
                        PetActivityLogTagEnum::Location_At_Home,
                    ]))
                ;

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            }

            $this->inventoryService->petCollectsItem('Wings', $pet, $pet->getName() . ' bound this.', $activityLog);
        }

        return $activityLog;
    }

    public function createGoldTriskaidecta(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck <= 2)
        {
            $pet->increaseSafety(-6);
            StatusEffectHelpers::applyStatusEffect($this->em, $pet, StatusEffectEnum::HexHexed, 6 * 60);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Gold Trifecta, but accidentally hexed themselves, instead! :(')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else if($umbraCheck < 14)
        {
            if($this->rng->rngNextInt(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Gold Triskaidecta, but the enchantment wouldn\'t stick! >:(')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Gold Triskaidecta, but couldn\'t quite remember the steps.')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Gold Trifecta', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% imbued a Gold Trifecta with the power of the number 13!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 14)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Gold Triskaidecta', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createSpearmint(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skillCheck = $this->rng->rngSkillRoll(min($petWithSkills->getNature()->getTotal(), $petWithSkills->getArcana()->getTotal()) + max($petWithSkills->getDexterity()->getTotal(), $petWithSkills->getPerception()->getTotal()) + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($skillCheck < 16)
        {
            $message = $this->rng->rngNextInt(1, 4) === 1
                ? $pet->getName() . ' tried to bind Mint to a Leaf Spear, but couldn\'t handle THE FRESHNESS.'
                : $pet->getName() . ' tried to bind Mint to a Leaf Spear, but couldn\'t figure it out...'
            ;

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $message)
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana, PetSkillEnum::Nature ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Mint', 1);
            $this->houseSimService->getState()->loseItem('Leaf Spear', 1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% bound Mint to a Leaf Spear, creating a Spearmint!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 16)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $this->inventoryService->petCollectsItem('Spearmint', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana, PetSkillEnum::Nature ], $activityLog);
        }

        return $activityLog;
    }

    public function createKokopelli(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skillCheck = $this->rng->rngSkillRoll((int)ceil(($petWithSkills->getArcana()->getTotal() + $petWithSkills->getMusic()->getTotal()) / 2) + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($skillCheck < 22)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to bind a Music Note to a Fishing Recorder, but couldn\'t get the pitch right...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana, PetSkillEnum::Music ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Music Note', 1);
            $this->houseSimService->getState()->loseItem('Fishing Recorder', 1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% bound a Music Note to a Fishing Recorder, creating a Kokopelli!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 22)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $this->inventoryService->petCollectsItem('Kokopelli', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana, PetSkillEnum::Music ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createTwiggenBerries(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $craftsCheck = $this->rng->rngSkillRoll($petWithSkills->getNature()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal());

        if($craftsCheck < 10)
        {
            $pet->increaseSafety(-2);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to bind some berries to a Crooked Stick, but the vines were scratchy, and berries kept falling off, and ugh!')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding', 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana, PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else if($umbraCheck < 20)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to bind some berries to a Crooked Stick, but kept messing up the enchantment...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding', 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana, PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $this->houseSimService->getState()->loseItem('Blackberries', 1);
            $this->houseSimService->getState()->loseItem('Goodberries', 1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% bound some berries to a Crooked Stick, creating Twiggen Berries!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 20)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $this->inventoryService->petCollectsItem('Twiggen Berries', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana, PetSkillEnum::Crafts ], $activityLog);
        }

        return $activityLog;
    }

    public function createAmbrotypicSolvent(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skillCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + (int)ceil($petWithSkills->getScience()->getTotal() / 2) + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($pet->hasMerit(MeritEnum::SILVERBLOOD))
            $skillCheck += 5;

        if($skillCheck < 14)
        {
            if($this->rng->rngNextInt(1, 2) === 1)
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to mix some Ambrotypic Solvent, but wasn\'t confident in their measurements of the ratios...')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding', 'Physics' ]))
                ;
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to mix some Ambrotypic Solvent, but wasn\'t confident about how to properly infuse the Quintessence...')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding', 'Physics' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana, PetSkillEnum::Science ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Silver Bar', 1);
            $this->houseSimService->getState()->loseItem('Paint Stripper', 1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% mixed some magic Ambrotypic Solvent!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 14)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Physics,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $this->inventoryService->petCollectsItem('Ambrotypic Solvent', $pet, $pet->getName() . ' mixed this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana, PetSkillEnum::Science ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createMagicCrystalBall(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngSkillRoll($petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + max($petWithSkills->getArcana()->getTotal(), $petWithSkills->getScience()->getTotal()) + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($roll >= 20)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Crystal Ball', 1);
            $this->houseSimService->getState()->loseItem('Tachyon', 1);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $pet->increaseEsteem(4);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% trapped a Tachyon in a Crystal Ball!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 20)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Magic Crystal Ball', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::Arcana, PetSkillEnum::Science ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to trap a Tachyon in a Crystal Ball, but they\'re just so dang tiny and fast!')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana, PetSkillEnum::Science ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }

        return $activityLog;
    }

    public function createNoetalasEye(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngSkillRoll($petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($roll >= 18)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Crystal Ball', 1);
            $this->houseSimService->getState()->loseItem('Quinacridone Magenta Dye', 1);
            $this->houseSimService->getState()->loseItem('Meteorite', 1);
            $pet->increaseEsteem(1);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% channeled Noetala\'s watchful eye from the depths of space...')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 18)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Eye of Noetala', $pet, $pet->getName() . ' channeled this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);

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
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% considered invoking the watchful eye of Noetala, but thought better of it...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
    }

    public function createLotusjar(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngSkillRoll($petWithSkills->getIntelligence()->getTotal() + (int)floor(($petWithSkills->getDexterity()->getTotal() + $petWithSkills->getPerception()->getTotal()) / 2) + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($roll >= 24)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Crystal Ball', 1);
            $this->houseSimService->getState()->loseItem('Lotus Flower', 1);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $pet->increaseEsteem(1);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% magically bound a Lotusjar!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 24)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Lotusjar', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::Arcana ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% started making a Lotusjar, but almost accidentally tore the flower, so decided to take a break...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }

        return $activityLog;
    }

    public function createCoolMintScepter(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skillCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() * 2 + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($skillCheck < 16)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to infuse a Wand of Ice with Mint, but it wasn\'t working...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Mint', 1);
            $this->houseSimService->getState()->loseItem('Wand of Ice', 1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% infused a Wand of Ice with Mint, creating a Cool Mint Scepter!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 16)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $this->inventoryService->petCollectsItem('Cool Mint Scepter', $pet, $pet->getName() . ' made this by infusing a Wand of Ice with Mint.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);

            return $activityLog;
        }
    }

    public function createMagicPinecone(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skillCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($skillCheck < 12)
        {
            $pet->increaseSafety(-1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to bind Everice to a Pinecone, but almost got frostbitten, and had to put it down for the time being...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Pinecone', 1);
            $this->houseSimService->getState()->loseItem('Everice', 1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% bound some Everice to a Pinecone, creating a _Magic_ Pinecone!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 12)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $this->inventoryService->petCollectsItem('Magic Pinecone', $pet, $pet->getName() . ' made this by binding Everice to a Pinecone.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createSleet(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skillCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($skillCheck < 21)
        {
            $pet->increaseSafety(-1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to bind Everice to an Invisible Shovel, but almost got frostbitten, and had to put it down for the time being...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Invisible Shovel', 1);
            $this->houseSimService->getState()->loseItem('Everice', 1);

            if($skillCheck >= 26)
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% bound some Everice to an Invisible Shovel, creating Sleet! Oh: and there\'s a little Invisibility Juice left over!')
                    ->addInterestingness(PetActivityLogInterestingness::HoHum + 26)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                        PetActivityLogTagEnum::Magic_binding,
                        PetActivityLogTagEnum::Location_At_Home,
                    ]))
                ;

                $this->inventoryService->petCollectsItem('Invisibility Juice', $pet, $pet->getName() . ' got this as a byproduct when binding Sleet!', $activityLog);
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% bound some Everice to an Invisible Shovel, creating Sleet!')
                    ->addInterestingness(PetActivityLogInterestingness::HoHum + 21)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                        PetActivityLogTagEnum::Magic_binding,
                        PetActivityLogTagEnum::Location_At_Home,
                    ]))
                ;
            }

            $this->inventoryService->petCollectsItem('Sleet', $pet, $pet->getName() . ' made this by binding Everice to an Invisible Shovel.', $activityLog);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createFrostbite(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skillCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($skillCheck <= 2)
        {
            $pet->increaseSafety(-4);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to bind Everice to a Scythe, but uttered the wrong sounds during the ritual, and got frostbitten! :(')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else if($skillCheck < 16)
        {
            $pet->increaseSafety(-1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to bind Everice to a Scythe, but almost got frostbitten, and had to put it down for the time being...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Everice', 1);
            $this->houseSimService->getState()->loseItem('Scythe', 1);
            $this->houseSimService->getState()->loseItem('String', 1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% bound some Everice to a Scythe, creating Frostbite!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 16)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $this->inventoryService->petCollectsItem('Frostbite', $pet, $pet->getName() . ' made this by binding Everice to a Scythe, and making a grip with wound String.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createHexicle(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skillCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($skillCheck <= 2)
        {
            $pet->increaseSafety(-6);
            StatusEffectHelpers::applyStatusEffect($this->em, $pet, StatusEffectEnum::HexHexed, 6 * 60);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to transmute plastic into ice, but accidentally hexed themselves, instead! :(')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else if($skillCheck < 16)
        {
            $pet->increaseSafety(-1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to transmute plastic into ice, but the plastic resisted...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Everice', 1);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Nonsenserang', 1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% transmuted the plastic of a Nonsenserang into ice, creating a Hexicle!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 16)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $this->inventoryService->petCollectsItem('Hexicle', $pet, $pet->getName() . ' made this transmuting its plastic into ice.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createMoonPearl(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 10)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to bind a moonbeam to a Crystal Ball, but kept missing the moonbeams!')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Crystal Ball', 1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% bound a moonbeam to a Crystal Ball, creating a Moon Pearl!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 10)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $this->inventoryService->petCollectsItem('Moon Pearl', $pet, $pet->getName() . ' created this by binding a moonbeam to a Crystal Ball...', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createAubergineCommander(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck <= 2)
        {
            $pet->increaseSafety(-6);
            StatusEffectHelpers::applyStatusEffect($this->em, $pet, StatusEffectEnum::HexHexed, 6 * 60);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant an Aubergine Scepter, but accidentally hexed themselves, instead! :(')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else if($umbraCheck < 16)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant an Aubergine Scepter, but the evil was _too strong_.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Aubergine Scepter', 1);

            $message = $this->rng->rngNextInt(1, 10) === 1
                ? $pet->getName() . ' bound an Aubergine Commander! (Was this really such a good idea...?)'
                : $pet->getName() . ' bound an Aubergine Commander!'
            ;

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $message)
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 16)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $this->inventoryService->petCollectsItem('Aubergine Commander', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function enchantSiderealLeafSpear(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 18)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Sidereal Leaf Spear, but messed up the calendar calculations.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Sidereal Leaf Spear', 1);
            $pet->increaseEsteem(3);

            $makes = $this->rng->rngNextFromArray([ 'Midnight', 'Sunrise' ]);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% enchanted a Sidereal Spear, creating ' . $makes . '!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 18)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem($makes, $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            return $activityLog;
        }
    }

    public function createGenericScroll(ComputedPetSkills $petWithSkills, string $uniqueIngredient, string $scroll): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($uniqueIngredient == 'Silver Bar' && $pet->hasMerit(MeritEnum::SILVERBLOOD))
            $umbraCheck += 5;

        $scrollItem = ItemRepository::findOneByName($this->em, $scroll);

        if($umbraCheck < 15)
        {
            if($this->rng->rngNextInt(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to create ' . $scrollItem->getNameWithArticle() . ', but accidentally dropped the Paper at a crucial moment, and smudged the writing!')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                        PetActivityLogTagEnum::Magic_binding,
                    ]))
                ;
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to create ' . $scrollItem->getNameWithArticle() . ', but couldn\'t quite remember the steps.')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                        PetActivityLogTagEnum::Magic_binding,
                    ]))
                ;
            }

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts, PetSkillEnum::Arcana ], $activityLog);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Paper', 1);
            $this->houseSimService->getState()->loseItem($uniqueIngredient, 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created ' . $scrollItem->getNameWithArticle() . '.')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 15)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem($scrollItem, $pet, $pet->getName() . ' bound this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Crafts, PetSkillEnum::Arcana ], $activityLog);
        }

        return $activityLog;
    }

    public function createFruitScroll(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        $scrollItem = ItemRepository::findOneByName($this->em, 'Scroll of Fruit');

        if($umbraCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);

            $lostItem = $this->houseSimService->getState()->loseOneOf($this->rng, [ 'Red', 'Orange' ]);

            $randomGoop = $this->rng->rngNextFromArray([
                'Blackberry Jam', 'Blueberry Jam',
                'Apricot Preserves', 'Naner Preserves',
                'Orange Marmalade', 'Pamplemousse Marmalade', 'Red Marmalade',
            ]);

            $pet->increaseEsteem(-2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried creating ' . $scrollItem->getNameWithArticle() . ', but the Quintessence got out of control and melted their ' . $lostItem . ' into a pile of ' . $randomGoop . '!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 2)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem($scrollItem, $pet, $pet->getName() . ' bound this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts, PetSkillEnum::Arcana ], $activityLog);
        }
        else if($umbraCheck < 15)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to create ' . $scrollItem->getNameWithArticle() . ', but accidentally dropped the Paper at a crucial moment, and smudged the writing!')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                ]))
            ;

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts, PetSkillEnum::Arcana ], $activityLog);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Paper', 1);
            $this->houseSimService->getState()->loseOneOf($this->rng, [ 'Red', 'Orange' ]);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created ' . $scrollItem->getNameWithArticle() . '.')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 15)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem($scrollItem, $pet, $pet->getName() . ' bound this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Crafts, PetSkillEnum::Arcana ], $activityLog);
        }

        return $activityLog;
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
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 18)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a magic wand, but the Wings kept getting away,  and ' . $pet->getName() . ' spent all their time chasing them down!')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Crafts, PetSkillEnum::Arcana ], $activityLog);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Wings', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $this->houseSimService->getState()->loseItem('Coriander Flower', 1);
            $pet->increaseEsteem(3);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Mericarp.')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 18)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Mericarp', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Crafts, PetSkillEnum::Arcana ], $activityLog);
        }

        return $activityLog;
    }

    public function createSummoningScroll(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 18)
        {
            if($this->rng->rngNextInt(1, 2) === 1 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to create a Monster-summoning Scroll, but accidentally dropped the Paper at a crucial moment, and smudged the writing!')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to create a Monster-summoning Scroll, but couldn\'t quite remember the steps.')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Crafts, PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Wings', 1);
            $this->houseSimService->getState()->loseItem('Paper', 1);
            $this->houseSimService->getState()->loseItem('Talon', 1);
            $pet->increaseEsteem(3);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Monster-summoning Scroll.')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 18)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Monster-summoning Scroll', $pet, $pet->getName() . ' bound this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Crafts, PetSkillEnum::Arcana ], $activityLog);
        }

        return $activityLog;
    }

    public function createRussetStaff(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 16)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to create a potato staff, but couldn\'t help but wonder if it was really such a good idea...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts, PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Wings', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $this->houseSimService->getState()->loseItem('Potato', 1);
            $pet->increaseEsteem(5);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Glowing Russet Staff of Swiftness.')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 16)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Glowing Russet Staff of Swiftness', $pet, $pet->getName() . ' bound this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Crafts, PetSkillEnum::Arcana ], $activityLog);
        }

        return $activityLog;
    }

    public function createWhiteEpee(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 17)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Rapier, but was having trouble getting the Wings to cooperate...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts, PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Wings', 1);
            $this->houseSimService->getState()->loseItem('White Feathers', 1);
            $this->houseSimService->getState()->loseItem('Rapier', 1);
            $pet->increaseEsteem($this->rng->rngNextInt(3, 6));
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% bound a White Épée.')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 17)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('White Épée', $pet, $pet->getName() . ' bound this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Crafts, PetSkillEnum::Arcana ], $activityLog);
        }
        return $activityLog;
    }

    public function createFlyingBindle(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 16)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to bind a Flying Bindle, but the wings were being super-uncooperative, and kept trying to fly away!')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Wings', 1);
            $this->houseSimService->getState()->loseItem('Bindle', 1);
            $pet->increaseEsteem(5);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% gifted a Bindle with the power of flight!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 16)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Flying Bindle', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createFlyingGrapplingHook(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 16)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to bind a Flying Grappling Hook, but the wings were being super-uncooperative, and kept trying to fly away!')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Wings', 1);
            $this->houseSimService->getState()->loseItem('Grappling Hook', 1);
            $pet->increaseEsteem(5);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% gifted a Grappling Hook with the power of flight!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 16)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Flying Grappling Hook', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }
        return $activityLog;
    }

    public function createGravelingHook(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());
        $scienceCheck = $this->rng->rngSkillRoll($petWithSkills->getScience()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal());

        if($umbraCheck < 12 || $scienceCheck < 12)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to bind a Gravitational Waves to a Grappling Hook, but couldn\'t wrap their head around working with forces governed by such different paradigms...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding', 'Physics' ]))
            ;

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana, PetSkillEnum::Science ], $activityLog);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Gravitational Waves', 1);
            $this->houseSimService->getState()->loseItem('Grappling Hook', 1);
            $pet->increaseEsteem(4);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% bound some Gravitational Waves to a Grappling Hook! Now it\'s real good at SMASHING STUFF!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 18)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Physics,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Graveling Hook', $pet, $pet->getName() . ' bound this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana, PetSkillEnum::Science ], $activityLog);
        }

        return $activityLog;
    }

    public function createYggdrasil(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 17)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant some scales, but almost accidentally brought them to life!')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Scales', 1);
            $this->houseSimService->getState()->loseItem('Red Flail', 1);
            $pet->increaseEsteem(4);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% bound some Scales to a Red Flail, creating a Yggdrasil Branch!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 17)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Yggdrasil Branch', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);

            return $activityLog;
        }
    }

    public function createLullablade(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());
        $craftsCheck = $this->rng->rngSkillRoll($petWithSkills->getCrafts()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal());

        if($craftsCheck <= 2)
        {
            $this->houseSimService->getState()->loseItem('Jar of Fireflies', 1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% had an idea for a dreamy blade made of fireflies, but when handling the Jar of Fireflies, accidentally let them all out!')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else if($umbraCheck < 17)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% had an idea for a dreamy blade made of Viscaria, but couldn\'t figure out the right enchantment to use...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Viscaria', 1);
            $this->houseSimService->getState()->loseItem('Jar of Fireflies', 1);
            $pet->increaseEsteem(4);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% bound a Lullablade!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 17)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Lullablade', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);

            return $activityLog;
        }
    }

    public function createPraxilla(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 19)
        {
            $pet->increaseSafety(-1);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Decorated Flute, but kept messing up the blessing.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Quinacridone Magenta Dye', 1);
            $this->houseSimService->getState()->loseItem('Decorated Flute', 1);
            $pet->increaseEsteem(3);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% blessed a Decorated Flute with the skills of an ancient poet...')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 20)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Praxilla', $pet, $pet->getName() . ' blessed this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);

            return $activityLog;
        }
    }

    public function createCattail(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 16)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to bring some Fluff to life, but kept messing up the spell.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Fluff', 1);
            $this->houseSimService->getState()->loseOneOf($this->rng, [ 'Snakebite', 'Wood\'s Metal' ]);
            $pet->increaseEsteem(3);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% brought some Fluff to life, and bound it to a sword, creating a Cattail!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 16)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Cattail', $pet, $pet->getName() . ' created this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);

            return $activityLog;
        }
    }

    public function createMalice(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 20)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Snakebite, but the enchantment wouldn\'t stick.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Hebenon', 1);
            $this->houseSimService->getState()->loseItem('Snakebite');
            $pet->increaseEsteem(3);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% steeped a Snakebite in Hebenon, then cast a spell on it, turning the Snakebite into Malice!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 20)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Malice', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);

            return $activityLog;
        }
    }

    public function createGyre(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck < 20)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Painted Whorl Staff, but the staff started to get all slimy.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Painted Whorl Staff', 1);
            $pet->increaseEsteem(3);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% bound a Gyre!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 20)
                ->addTags(
                    PetActivityLogTagHelpers::findByNames($this->em, [
                        PetActivityLogTagEnum::Magic_binding,
                        PetActivityLogTagEnum::Location_At_Home,
                    ])
                )
            ;
            $this->inventoryService->petCollectsItem('Gyre', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);

            return $activityLog;
        }
    }

    public function createMolly(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $umbraCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($umbraCheck <= 2 && $pet->getFood() < 4)
        {
            $pet->increaseEsteem(-2);

            $pet->increaseFood(8);
            $this->houseSimService->getState()->loseItem('Fish', 1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% was going to feed a Cattail, but ended up eating the Fish themselves...')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding', 'Eating' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        if($umbraCheck < 20)
        {
            if($this->rng->rngNextBool())
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to further enchant a Cattail, but it refused to eat >:(')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to further enchant a Cattail, but it kept batting the Moon Pearl away >:(')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Fish', 1);
            $this->houseSimService->getState()->loseItem('Cattail', 1);
            $this->houseSimService->getState()->loseItem('Moon Pearl', 1);
            $pet->increaseEsteem(4);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% fed and enchanted a Cattail, creating a Molly!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 20)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Molly', $pet, $pet->getName() . ' created this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);

            return $activityLog;
        }
    }

    public function createFimbulvetr(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skillCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($skillCheck <= 2)
        {
            $pet->increaseSafety(-4);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to bind Everice to a Heavy Hammer, but uttered the wrong sounds during the ritual, and got frostbitten! :(')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else if($skillCheck < 22)
        {

            $pet->increaseSafety(-1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to bind Everice to a Heavy Hammer, but almost got frostbitten, and had to put it down for the time being...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Heavy Hammer', 1);
            $this->houseSimService->getState()->loseItem('Everice', 1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% bound some Everice to a Heavy Hammer, creating Fimbulvetr!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 22)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $this->inventoryService->petCollectsItem('Fimbulvetr', $pet, $pet->getName() . ' made this by binding Everice to a Heavy Hammer.', $activityLog);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createLightningAxe(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($pet->hasMerit(MeritEnum::SILVERBLOOD))
            $roll += 5;

        if($roll <= 2)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to imbue an Iron Axe with lightning, but accidentally melted it, instead!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 22)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding', 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->houseSimService->getState()->loseItem('Iron Axe', 1);
            $this->inventoryService->petCollectsItem('Iron Bar', $pet, $pet->getName() . ' tried to enchant an Iron Axe, but it melted, instead :|', $activityLog);
        }
        else if($roll >= 22)
        {
            $this->houseSimService->getState()->loseItem('Silver Bar', 1);
            $this->houseSimService->getState()->loseItem('Iron Axe', 1);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Lightning in a Bottle', 1);
            $pet->increaseEsteem($this->rng->rngNextInt(2, 4));
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% silvered an Iron Axe, and imbued with lightning, creating a Lightning Axe.')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 22)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding', 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Lightning Axe', $pet, $pet->getName() . ' created this by adding a silver-iron blade to a Wand of Lightning.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to imbue an Iron Axe with lightning, but it wasn\'t working out...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding', 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }

        return $activityLog;
    }

    public function createSunlessMericarp(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($roll >= 22)
        {
            $this->houseSimService->getState()->loseItem('Tiny Black Hole', 1);
            $this->houseSimService->getState()->loseItem('Mericarp', 1);
            $pet->increaseEsteem(4);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Sunless Mericarp!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 22)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Sunless Mericarp', $pet, $pet->getName() . ' created this by binding a Tiny Black Hole to a Mericarp.', $activityLog);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, true);
        }
        else if($roll == 1)
        {
            $this->houseSimService->getState()->loseItem('Tiny Black Hole', 1);
            $pet->increaseEsteem(-4);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to bind a Tiny Black Hole to a Mericarp, but accidentally evaporated the black hole, instead :|')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 22)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to bind a Tiny Black Hole to a Mericarp, but almost evaporated the black hole, instead!')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }

        return $activityLog;
    }

    public function createTerrorSeed(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($roll === 1)
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $pet->increaseEsteem(-4);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Mysterious Seed, but it resisted the enchantment, and the Quintessence evaporated away! :|')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 22)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }

        if($roll < 22)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Mysterious Seed, but it was like the seed was actively resisting the enchantment!')
                ->setIcon('icons/activity-logs/confused')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 22)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, false);

            return $activityLog;
        }

        $this->houseSimService->getState()->loseItem('Quinacridone Magenta Dye', 1);
        $this->houseSimService->getState()->loseItem('Mysterious Seed', 1);
        $this->houseSimService->getState()->loseItem('Quintessence', 1);
        $pet->increaseEsteem(4);

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% successfully enchanted a Mysterious Seed... into something _terrible!_')
            ->addInterestingness(PetActivityLogInterestingness::HoHum + 22)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                PetActivityLogTagEnum::Magic_binding,
                PetActivityLogTagEnum::Location_At_Home,
            ]))
        ;

        $this->inventoryService->petCollectsItem('Terror Seed', $pet, $pet->getName() . ' created this by enchanting a Mysterious Seed during the month of ' . $this->clock->now->format('F') . '.', $activityLog);

        $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::Arcana ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::MAGIC_BIND, false);

        return $activityLog;
    }

    public function createAosSi(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skillCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($skillCheck < 20)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Glass Pendulum, but couldn\'t get the enchantment to stick...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Glass Pendulum', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% enchanted a Glass Pendulum, creating an Aos Sí!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 20)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $this->inventoryService->petCollectsItem('Aos Sí', $pet, $pet->getName() . ' bound this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createBeanSidhe(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skillCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($skillCheck < 27)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to cast a spell with an Aos Sí, but wasn\'t able to get anywhere...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Aos Sí', 1);
            $this->houseSimService->getState()->loseItem('Blackonite', 1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% used an Aos Sí to absorb a piece of Blackonite, creating a Bean Sídhe!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 27)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $this->inventoryService->petCollectsItem('Bean Sídhe', $pet, $pet->getName() . ' created this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }

    public function createLessHeavyHeavyHammer(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skillCheck = $this->rng->rngSkillRoll($petWithSkills->getArcana()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal());

        if($skillCheck < 21)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to bind a pair of Rainbow Wings to a Heavy Hammer, but the wings kept flying off!')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Rainbow Wings', 1);
            $this->houseSimService->getState()->loseItem('Heavy Hammer', 1);
            $pet->increaseEsteem(4);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% bound a pair of Rainbow Wings to a Heavy Hammer! Shiny!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 21)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Less-heavy Heavy Hammer', $pet, $pet->getName() . ' enchanted this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::MAGIC_BIND, true);
        }

        return $activityLog;
    }
}
