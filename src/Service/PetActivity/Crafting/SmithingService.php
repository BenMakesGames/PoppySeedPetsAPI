<?php
namespace App\Service\PetActivity\Crafting;

use App\Entity\PetActivityLog;
use App\Enum\GuildEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\CalendarFunctions;
use App\Functions\PetActivityLogFactory;
use App\Model\ActivityCallback;
use App\Model\ComputedPetSkills;
use App\Repository\PetActivityLogTagRepository;
use App\Service\Clock;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetActivity\Crafting\Helpers\GoldSmithingService;
use App\Service\PetActivity\Crafting\Helpers\HalloweenSmithingService;
use App\Service\PetActivity\Crafting\Helpers\IronSmithingService;
use App\Service\PetActivity\Crafting\Helpers\MeteoriteSmithingService;
use App\Service\PetActivity\Crafting\Helpers\SilverSmithingService;
use App\Service\PetActivity\Crafting\Helpers\TwuWuvCraftingService;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class SmithingService
{
    private InventoryService $inventoryService;
    private PetExperienceService $petExperienceService;
    private GoldSmithingService $goldSmithingService;
    private IronSmithingService $ironSmithingService;
    private MeteoriteSmithingService $meteoriteSmithingService;
    private HalloweenSmithingService $halloweenSmithingService;
    private SilverSmithingService $silverSmithingService;
    private TwuWuvCraftingService $twuWuvCraftingService;
    private IRandom $squirrel3;
    private HouseSimService $houseSimService;
    private Clock $clock;
    private EntityManagerInterface $em;

    public function __construct(
        InventoryService $inventoryService, PetExperienceService $petExperienceService,
        GoldSmithingService $goldSmithingService, SilverSmithingService $silverSmithingService, IRandom $squirrel3,
        IronSmithingService $ironSmithingService, MeteoriteSmithingService $meteoriteSmithingService,
        HalloweenSmithingService $halloweenSmithingService, Clock $clock, EntityManagerInterface $em,
        TwuWuvCraftingService $twuWuvCraftingService, HouseSimService $houseSimService
    )
    {
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
        $this->goldSmithingService = $goldSmithingService;
        $this->ironSmithingService = $ironSmithingService;
        $this->meteoriteSmithingService = $meteoriteSmithingService;
        $this->halloweenSmithingService = $halloweenSmithingService;
        $this->silverSmithingService = $silverSmithingService;
        $this->twuWuvCraftingService = $twuWuvCraftingService;
        $this->squirrel3 = $squirrel3;
        $this->houseSimService = $houseSimService;
        $this->em = $em;
        $this->clock = $clock;
    }

    public function getCraftingPossibilities(ComputedPetSkills $petWithSkills): array
    {
        $pet = $petWithSkills->getPet();
        $weight = ($pet->getSafety() > 0 || $pet->isInGuild(GuildEnum::DWARFCRAFT)) ? 10 : 1;

        $possibilities = [];

        if($this->houseSimService->hasInventory('Twu Wuv'))
        {
            if($this->houseSimService->hasInventory('String') && $this->houseSimService->hasInventory('Silver Bar'))
                $possibilities[] = new ActivityCallback($this->twuWuvCraftingService, 'createCupid', 15);
        }

        if($this->houseSimService->hasInventory('Charcoal'))
            $possibilities[] = new ActivityCallback($this, 'createCoke', $weight);

        if($this->houseSimService->hasInventory('Iron Ore'))
        {
            if($this->houseSimService->hasInventory('Moon Pearl') && $this->houseSimService->hasInventory('Gravitational Waves'))
                $possibilities[] = new ActivityCallback($this, 'createHighTide', 10);
            else
                $possibilities[] = new ActivityCallback($this, 'createIronBar', $weight);
        }

        if($this->houseSimService->hasInventory('Silver Ore'))
            $possibilities[] = new ActivityCallback($this, 'createSilverBar', $weight);

        if($this->houseSimService->hasInventory('Gold Ore'))
            $possibilities[] = new ActivityCallback($this, 'createGoldBar', $weight);

        if($this->houseSimService->hasInventory('Silica Grounds') && $this->houseSimService->hasInventory('Limestone'))
            $possibilities[] = new ActivityCallback($this, 'createGlass', $weight);

        if($this->houseSimService->hasInventory('Glass'))
        {
            $possibilities[] = new ActivityCallback($this, 'createCrystalBall', max(1, $weight - 2));

            if($this->houseSimService->hasInventory('Plastic'))
                $possibilities[] = new ActivityCallback($this, 'createFiberglass', $weight);

            if($this->houseSimService->hasInventory('Silver Bar') || $this->houseSimService->hasInventory('Dark Matter'))
                $possibilities[] = new ActivityCallback($this, 'createMirror', $weight);
        }

        if($this->houseSimService->hasInventory('Fiberglass'))
        {
            if($this->houseSimService->hasInventory('String'))
                $possibilities[] = new ActivityCallback($this, 'createFiberglassBow', 10);

            if($this->houseSimService->hasInventory('Shiny Pail'))
                $possibilities[] = new ActivityCallback($this, 'createShinyNanerPicker', 10);
        }

        if($this->houseSimService->hasInventory('Iron Bar'))
        {
            $possibilities[] = new ActivityCallback($this->ironSmithingService, 'createIronKey', $weight);
            $possibilities[] = new ActivityCallback($this->ironSmithingService, 'createBasicIronCraft', $weight);

            if($this->houseSimService->hasInventory('Plastic'))
            {
                if($this->houseSimService->hasInventory('Yellow Dye'))
                    $possibilities[] = new ActivityCallback($this->ironSmithingService, 'createYellowScissors', 10);

                if($this->houseSimService->hasInventory('Green Dye'))
                    $possibilities[] = new ActivityCallback($this->ironSmithingService, 'createGreenScissors', 10);

                $possibilities[] = new ActivityCallback($this->ironSmithingService, 'createSaucepan', 7);
            }

            if($this->houseSimService->hasInventory('Wings') && $this->houseSimService->hasInventory('Laser-guided Sword'))
                $possibilities[] = new ActivityCallback($this->ironSmithingService, 'createMeatSeekingClaymore', $weight);

            if($this->houseSimService->hasInventory('Crooked Stick'))
                $possibilities[] = new ActivityCallback($this->ironSmithingService, 'createScythe', 10);

            if($this->houseSimService->hasInventory('String'))
                $possibilities[] = new ActivityCallback($this->ironSmithingService, 'createGrapplingHook', 10);

            if($this->houseSimService->hasInventory('Dark Matter'))
                $possibilities[] = new ActivityCallback($this->ironSmithingService, 'createHeavyTool', $petWithSkills->getStrength()->getTotal() >= 3 ? $weight : ceil($weight / 2));

            if($this->houseSimService->hasInventory('Mirror'))
                $possibilities[] = new ActivityCallback($this->ironSmithingService, 'createMirrorShield', $weight);

            if($this->houseSimService->hasInventory('Toadstool'))
                $possibilities[] = new ActivityCallback($this->ironSmithingService, 'createMushketeer', $weight);

            if($this->houseSimService->hasInventory('Green Dye') && $this->houseSimService->hasInventory('Bug-catcher\'s Net'))
                $possibilities[] = new ActivityCallback($this->ironSmithingService, 'createWaterStrider', 10);
        }

        if($this->houseSimService->hasInventory('Saucepan') || $this->houseSimService->hasInventory('Upside-down Saucepan'))
        {
            if($this->houseSimService->hasInventory('Rice'))
                $possibilities[] = new ActivityCallback($this, 'createRiceFryingPan', 8);

            if($this->houseSimService->hasInventory('Scales'))
                $possibilities[] = new ActivityCallback($this, 'createFishFryingPan', 10);
        }

        if($this->houseSimService->hasInventory('Yellow Scissors') && $this->houseSimService->hasInventory('Green Scissors') && $this->houseSimService->hasInventory('Quinacridone Magenta Dye'))
            $possibilities[] = new ActivityCallback($this, 'createTriColorScissors', 10);

        if($this->houseSimService->hasInventory('Firestone'))
        {
            if($this->houseSimService->hasInventory('Tri-color Scissors'))
                $possibilities[] = new ActivityCallback($this, 'createPapersBane', $weight);

            if($this->houseSimService->hasInventory('Warping Wand'))
                $possibilities[] = new ActivityCallback($this, 'createRedWarpingWand', 10);

            if($this->houseSimService->hasInventory('Dragonstick'))
                $possibilities[] = new ActivityCallback($this, 'createDragonbreath', 10);
        }

        if($this->houseSimService->hasInventory('Silver Bar'))
        {
            $possibilities[] = new ActivityCallback($this->silverSmithingService, 'createSilverKey', $weight);
            $possibilities[] = new ActivityCallback($this->silverSmithingService, 'createBasicSilverCraft', $weight);

            if($this->houseSimService->hasInventory('Mericarp'))
                $possibilities[] = new ActivityCallback($this->silverSmithingService, 'createSilveredMericarp', 10);

            if($this->houseSimService->hasInventory('Crown Coral'))
                $possibilities[] = new ActivityCallback($this->silverSmithingService, 'createCoralTrident', 10);

            if($this->houseSimService->hasInventory('"Rustic" Magnifying Glass'))
                $possibilities[] = new ActivityCallback($this->silverSmithingService, 'createElvishMagnifyingGlass', 10);

            if($this->houseSimService->hasInventory('Leaf Spear'))
                $possibilities[] = new ActivityCallback($this->silverSmithingService, 'createSylvanFishingRod', 10);

            if($this->houseSimService->hasInventory('Glass'))
            {
                if($this->houseSimService->hasInventory('Silica Grounds'))
                    $possibilities[] = new ActivityCallback($this->silverSmithingService, 'createHourglass', $weight);
            }

            if($this->houseSimService->hasInventory('Gold Key') && $this->houseSimService->hasInventory('White Cloth'))
                $possibilities[] = new ActivityCallback($this->silverSmithingService, 'createGoldKeyblade', 10);

            if($this->houseSimService->hasInventory('Wand of Lightning'))
                $possibilities[] = new ActivityCallback($this->silverSmithingService, 'createLightningAxe', $weight);

            if(
                $this->houseSimService->hasInventory('Iron Axe') &&
                $this->houseSimService->hasInventory('String') &&
                $this->houseSimService->hasInventory('Talon')
            )
            {
                $possibilities[] = new ActivityCallback($this->silverSmithingService, 'createSharktoothAxe', $weight);
            }
        }

        if($this->houseSimService->hasInventory('Gold Bar'))
        {
            $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createGoldKey', $weight);

            $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createGoldTuningFork', ceil($weight / 2));

            if($this->houseSimService->hasInventory('Mericarp'))
                $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createGildedMericarp', 10);

            if($this->houseSimService->hasInventory('Culinary Knife') && $this->houseSimService->hasInventory('Firestone'))
                $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createNoWhiskNoReward', $weight);

            if($this->houseSimService->hasInventory('Eggplant'))
                $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createAubergineScepter', 8);

            if($this->houseSimService->hasInventory('Blackonite') && $this->houseSimService->hasInventory('White Cloth'))
                $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createVicious', 10);

            if($this->houseSimService->hasInventory('Fiberglass') && $this->houseSimService->hasInventory('Moon Pearl'))
                $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createMoonhammer', 10);

            if($this->houseSimService->hasInventory('Dark Scales') && $this->houseSimService->hasInventory('Dragon Flag'))
                $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createKundravsStandard', 10);

            if($this->houseSimService->hasInventory('String'))
                $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createGoldTriangle', 10);

            if($this->houseSimService->hasInventory('Chanterelle') && $this->houseSimService->hasInventory('Flute'))
                $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createFungalClarinet', 10);

            if($this->houseSimService->hasInventory('Glass'))
                $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createGoldTelescope', $weight);

            if($this->houseSimService->hasInventory('Plastic Shovel') && $this->houseSimService->hasInventory('Green Dye'))
                $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createCoreopsis', 10);

            if($this->houseSimService->hasInventory('Plastic') && $this->houseSimService->hasInventory('3D Printer'))
                $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createGoldRod', 10);

            if($this->houseSimService->hasInventory('Enchanted Compass'))
                $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createGoldCompass', $weight);

            if($this->houseSimService->hasInventory('Silver Key') && $this->houseSimService->hasInventory('White Cloth'))
                $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createSilverKeyblade', 10);

            if($this->houseSimService->hasInventory('Leaf Spear') && $this->houseSimService->hasInventory('Iron Bar'))
                $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createSiderealLeafSpear', 10);
        }

        if($this->houseSimService->hasInventory('Silver Bar') && $this->houseSimService->hasInventory('Gold Bar') && $this->houseSimService->hasInventory('White Cloth'))
            $possibilities[] = new ActivityCallback($this, 'createCeremonialTrident', 10);

        if($this->houseSimService->hasInventory('Iron Sword'))
        {
            if($this->houseSimService->hasInventory('Whisk'))
                $possibilities[] = new ActivityCallback($this, 'createCulinaryKnife', 10);

            if($this->houseSimService->hasInventory('Wooden Sword'))
                $possibilities[] = new ActivityCallback($this, 'createWoodsMetal', 10);

            if($this->houseSimService->hasInventory('Scales') && $this->houseSimService->hasInventory('Fluff'))
                $possibilities[] = new ActivityCallback($this, 'createDragonscale', 10);

            if($this->houseSimService->hasInventory('Dark Scales') && $this->houseSimService->hasInventory('Fluff'))
                $possibilities[] = new ActivityCallback($this, 'createDrakkonscale', 10);

            if($this->houseSimService->hasInventory('Everice') && $this->houseSimService->hasInventory('Firestone'))
                $possibilities[] = new ActivityCallback($this, 'createAntipode', 10);
        }

        if($this->houseSimService->hasInventory('Antipode') && $this->houseSimService->hasInventory('Lightning Sword'))
            $possibilities[] = new ActivityCallback($this, 'createTrinityBlade', 10);

        if($this->houseSimService->hasInventory('Everice'))
        {
            if($this->houseSimService->hasInventory('Poker'))
                $possibilities[] = new ActivityCallback($this, 'createWandOfIce', 10);

            if($this->houseSimService->hasInventory('Crooked Fishing Rod'))
                $possibilities[] = new ActivityCallback($this, 'createIceFishing', 10);
        }

        if($this->houseSimService->hasInventory('Poker') && $this->houseSimService->hasInventory('Gypsum Dragon'))
            $possibilities[] = new ActivityCallback($this, 'createDragonstick', 10);

        if($this->houseSimService->hasInventory('Meteorite'))
        {
            if($this->houseSimService->hasInventory('Iron Bar') && $this->houseSimService->hasInventory('Gold Bar'))
                $possibilities[] = new ActivityCallback($this->meteoriteSmithingService, 'createIlumetsa', 10);

            if($this->houseSimService->hasInventory('Moon Pearl') && $this->houseSimService->hasInventory('Dark Mirror'))
                $possibilities[] = new ActivityCallback($this->meteoriteSmithingService, 'createHorizonMirror', 10);
        }

        if(CalendarFunctions::isHalloweenCrafting($this->clock->now))
        {
            if($this->houseSimService->hasInventory('Small, Yellow Plastic Bucket') || $this->houseSimService->hasInventory('Upside-down, Yellow Plastic Bucket'))
                $possibilities[] = new ActivityCallback($this->halloweenSmithingService, 'createPumpkinBucket', 10);
        }

        return $possibilities;
    }

    public function createTriColorScissors(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + max($petWithSkills->getDexterity()->getTotal(), $petWithSkills->getIntelligence()->getTotal()) + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Yellow Scissors', 1);
            $this->houseSimService->getState()->loseItem('Green Scissors', 1);
            $this->houseSimService->getState()->loseItem('Quinacridone Magenta Dye', 1);
            $pet->increaseEsteem(4);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% combined two pairs of scissors, creating Tri-color Scissors!')
                ->setIcon('items/tool/scissors/tri-color')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Tri-color Scissors', $pet, $pet->getName() . ' made this by combining two pairs of scissors!', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make Tri-color Scissors, but got confused just thinking about what it would even look like...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createPapersBane(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + max($petWithSkills->getDexterity()->getTotal(), $petWithSkills->getIntelligence()->getTotal()) + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 20)
        {
            $this->houseSimService->getState()->loseItem('Tri-color Scissors', 1);
            $this->houseSimService->getState()->loseItem('Firestone', 1);

            $pet->increaseEsteem(4);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% infused Tri-color Scissors with the eternal heat of Firestone!')
                ->setIcon('items/tool/scissors/papersbane')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Paper\'s Bane', $pet, $pet->getName() . ' made this by infusing Tri-color Scissors with the eternal heat of Firestone!', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make Paper\'s Bane, but almost burned themselves on the Firestone...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createRedWarpingWand(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 26)
        {
            $this->houseSimService->getState()->loseItem('Warping Wand', 1);
            $this->houseSimService->getState()->loseItem('Firestone', 1);

            $pet->increaseEsteem(6);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% infused a Warping Wand with the eternal heat of Firestone!')
                ->setIcon('items/tool/scissors/papersbane')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 25)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Red Warping Wand', $pet, $pet->getName() . ' made this by infusing a Warping Wand with the eternal heat of Firestone!', $activityLog);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
        }
        else
        {
            if($this->squirrel3->rngNextInt(1, 2) === 1)
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Red Warping Wand, but almost burned themselves on the Firestone...')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
                ;
            }
            else
            {
                $location = $this->squirrel3->rngNextFromArray([ 'on the roof', 'in the bathtub', 'in the dishwasher', 'under your bed', 'in your closet', 'in the mailbox' ]);
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Red Warping Wand, but accidentally warped the Firestone away. They looked around for a while, and finally found it ' . $location . '.')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    /**
     * note: THIS method should be private, but most methods here must be public!
     */
    private function createFryingPan(ComputedPetSkills $petWithSkills, string $otherMaterial, string $makes): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2)
        {
            $this->houseSimService->getState()->loseItem($otherMaterial, 1);

            $pet->increaseEsteem(-2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to smith a ' . $makes . ', but accidentally _obliterated_ the ' . $otherMaterial . '! :(')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::SMITH, false);
        }
        else if($roll < 17)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to smith a ' . $makes . ', but kept having trouble working the ' . $otherMaterial . ' in...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::SMITH, false);
        }
        else // success!
        {
            $this->houseSimService->getState()->loseOneOf($this->squirrel3, [ 'Saucepan', 'Upside-down Saucepan' ]);
            $this->houseSimService->getState()->loseItem($otherMaterial, 1);

            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% smithed a ' . $makes . '!')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 17)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->petCollectsItem($makes, $pet, $pet->getName() . ' made this.', $activityLog);
        }

        return $activityLog;
    }

    public function createFishFryingPan(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        return $this->createFryingPan($petWithSkills, 'Scales', 'Fish Frying Pan');
    }

    public function createRiceFryingPan(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        return $this->createFryingPan($petWithSkills, 'Rice', 'Rice Frying Pan');
    }

    public function createMirror(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 3)
        {
            $pet->increaseSafety(-4);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried silvering some Glass, but accidentally cut themselves! :(')
                ->setIcon('icons/activity-logs/wounded')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);

            return $activityLog;
        }
        else if($roll >= 13)
        {
            $mirrorBacking = $this->houseSimService->getState()->loseOneOf($this->squirrel3, [ 'Silver Bar', 'Dark Matter' ]);
            $this->houseSimService->getState()->loseItem('Glass', 1);
            $pet->increaseEsteem(2);

            if($mirrorBacking === 'Dark Matter')
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Dark Mirror.')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
                ;
                $this->inventoryService->petCollectsItem('Dark Mirror', $pet, $pet->getName() . ' created this.', $activityLog);
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Mirror.')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
                    ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
                ;
                $this->inventoryService->petCollectsItem('Mirror', $pet, $pet->getName() . ' created this.', $activityLog);
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Mirror, but couldn\'t get the Glass smoooooth enough.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createHighTide(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + min($petWithSkills->getScience()->getTotal(), $petWithSkills->getCrafts()->getTotal()) + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 3)
        {
            $this->houseSimService->getState()->loseItem('Gravitational Waves', 1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried smithing a High Tide, but the Gravitational Waves got away! :(')
                ->setIcon('icons/activity-logs/wounded')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing', 'Physics' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }
        else if($roll >= 18)
        {
            $this->houseSimService->getState()->loseItem('Iron Ore', 1);
            $this->houseSimService->getState()->loseItem('Moon Pearl', 1);
            $this->houseSimService->getState()->loseItem('Gravitational Waves', 1);
            $pet->increaseEsteem(4);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a High Tide.')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing', 'Physics' ]))
            ;
            $this->inventoryService->petCollectsItem('High Tide', $pet, $pet->getName() . ' created this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, true);
        }
        else
        {
            if($this->squirrel3->rngNextBool())
            {
                $but = 'started pulverizing the Iron Ore, and almost immediately felt exhausted';
                $pet->increaseFood(-1);
            }
            else
            {
                $but = 'kept getting stumped by the math for Gravitational Waves';
            }

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a High Tide, but ' . $but . '.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing', 'Physics' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createGlass(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2)
        {
            $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 12));
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make Glass, but got burned while trying! :(')
                ->setIcon('icons/activity-logs/burn')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }
        else if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Silica Grounds', 1);

            if($this->squirrel3->rngNextInt(1, 3) === 1)
            {
                $this->houseSimService->getState()->loseItem('Limestone', 1);

                if($this->squirrel3->rngNextInt(1, 3) === 1)
                {
                    $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% melted Silica Grounds and Limestone into TWO Glass!')
                        ->setIcon('items/mineral/silica-glass');

                    $this->inventoryService->petCollectsItem('Glass', $pet, $pet->getName() . ' created this from Silica Grounds and Limestone.', $activityLog);
                }
                else
                {
                    $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% melted Silica Grounds and Limestone into Glass.')
                        ->setIcon('items/mineral/silica-glass');
                }
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% melted Silica Grounds and Limestone into Glass. (There\'s plenty of Limestone left over, though!)')
                    ->setIcon('items/mineral/silica-glass');
            }

            $activityLog
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->inventoryService->petCollectsItem('Glass', $pet, $pet->getName() . ' created this from Silica Grounds and Limestone.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $pet->increaseEsteem(1);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make Glass, but couldn\'t figure it out.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createCrystalBall(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2)
        {
            $pet->increaseSafety(-4);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Crystal Ball, but accidentally cut themselves on the Glass! :(')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }
        else if($roll >= 20 && $this->squirrel3->rngNextInt(1, 3) === 1)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Glass', 1);

            $pet->increaseEsteem($this->squirrel3->rngNextInt(2, 4));

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% made TWO Crystal Balls out of Glass!')
                ->setIcon('items/mineral/silica-glass-ball')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Crystal Ball', $pet, $pet->getName() . ' created this from Glass.', $activityLog);
            $this->inventoryService->petCollectsItem('Crystal Ball', $pet, $pet->getName() . ' created this from Glass.', $activityLog);

            $this->maybeMakeARainbowToo($petWithSkills, 2);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Glass', 1);

            $pet->increaseEsteem(1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% made a Crystal Ball out of Glass.')
                ->setIcon('items/mineral/silica-glass-ball')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 12)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Crystal Ball', $pet, $pet->getName() . ' created this from Glass.', $activityLog);

            $this->maybeMakeARainbowToo($petWithSkills, 1);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Crystal Ball, but making a perfect sphere was proving difficult!')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createFiberglass(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2)
        {

            $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 12));
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make Fiberglass, but got burned while trying! :(')
                ->setIcon('icons/activity-logs/burn')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }
        else if($roll >= 25 && $this->squirrel3->rngNextInt(1, 3) === 1)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Glass', 1);
            $this->houseSimService->getState()->loseItem('Plastic', 1);

            $pet->increaseEsteem($this->squirrel3->rngNextInt(3, 6));

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% made TWO bundles of Fiberglass from Glass and Plastic!')
                ->setIcon('items/resource/fiberglass')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 25)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Fiberglass', $pet, $pet->getName() . ' created this from Glass and Plastic.', $activityLog);
            $this->inventoryService->petCollectsItem('Fiberglass', $pet, $pet->getName() . ' created this from Glass and Plastic.', $activityLog);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Glass', 1);
            $this->houseSimService->getState()->loseItem('Plastic', 1);

            $pet->increaseEsteem(1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% made a bundle of Fiberglass from Glass and Plastic.')
                ->setIcon('items/resource/fiberglass')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Fiberglass', $pet, $pet->getName() . ' created this from Glass and Plastic.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make Fiberglass, but couldn\'t figure it out.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createFiberglassBow(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Fiberglass', 1);
            $this->houseSimService->getState()->loseItem('String', 1);

            $pet->increaseEsteem(2);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% made a Fiberglass Bow.')
                ->setIcon('items/tool/bow/fiberglass')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Fiberglass Bow', $pet, $pet->getName() . ' created this from Fiberglass, and String.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);

            return $activityLog;
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Fiberglass Bow, but couldn\'t figure it out.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);

            return $activityLog;
        }
    }

    public function createShinyNanerPicker(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 18)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Fiberglass', 1);
            $this->houseSimService->getState()->loseItem('Shiny Pail', 1);

            $pet->increaseEsteem(2);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% made a Shiny Naner-picker.')
                ->setIcon('items/tool/bow/fiberglass')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Shiny Naner-picker', $pet, $pet->getName() . ' created this from Fiberglass, and a Shiny Pail.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Shiny Naner-picker, but the Fiberglass was proving difficult to work with...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createCeremonialTrident(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($pet->hasMerit(MeritEnum::SILVERBLOOD))
            $roll += 5;

        if($roll <= 3)
        {
            $moneys = $this->squirrel3->rngNextInt(10, 30);

            $this->houseSimService->getState()->loseItem('Silver Bar', 1);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Ceremonial Trident, but melted the heck out of the Silver Bar! :( ' . $pet->getName() . ' decided to make some coins out of it, instead, and got ' . $moneys . '~~m~~.')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
        }
        else if($roll >= 17)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Silver Bar', 1);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('White Cloth', 1);

            $pet->increaseEsteem(4);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% made a Ceremonial Trident!')
                ->setIcon('items/tool/spear/trident-ceremonial')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 17)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Ceremonial Trident', $pet, $pet->getName() . ' created this from gold, silver, and cloth.', $activityLog);

            $this->petExperienceService->gainExp($pet, $this->squirrel3->rngNextInt(2, 3), [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $message = $pet->hasMerit(MeritEnum::EIDETIC_MEMORY)
                ? '%pet:' . $pet->getId() . '.name% tried to make a Ceremonial Trident, but couldn\'t get the shape just right...'
                : '%pet:' . $pet->getId() . '.name% started making a Ceremonial Spear, but then remembered there\'s no such thing... >_>'
            ;

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $message)
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, $this->squirrel3->rngNextInt(1, 2), [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createAntipode(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + max($petWithSkills->getDexterity()->getTotal(), $petWithSkills->getIntelligence()->getTotal()) + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll == 1)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Iron Sword', 1);
            $pet->increaseEsteem(-3);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make Antipode, but accidentally warped the Iron Sword into an unrecognizable shape :|')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->inventoryService->petCollectsItem('Iron Bar', $pet, $pet->getName() . ' accidentally broke an Iron Sword; this was all that remained!', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else if($roll >= 22)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Iron Sword', 1);
            $this->houseSimService->getState()->loseItem('Everice', 1);
            $this->houseSimService->getState()->loseItem('Firestone', 1);

            $pet->increaseEsteem(6);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% smithed Antipode!')
                ->setIcon('items/tool/sword/antipode')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 22)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Antipode', $pet, $pet->getName() . ' created this by hammering Everice and Firestone into an Iron Sword!', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make Antipode, but the ' . $this->squirrel3->rngNextFromArray([ 'Everice', 'Firestone' ]) . ' was being uncooperative :|')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createWoodsMetal(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + max($petWithSkills->getDexterity()->getTotal(), $petWithSkills->getIntelligence()->getTotal()) + $petWithSkills->getStamina()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getScience()->getTotal()) + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
            $pet->increaseSafety(-4);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to create Wood\'s Metal, but accidentally burnt themselves trying! :(')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing', 'Physics' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else if($roll >= 17)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Iron Sword', 1);
            $this->houseSimService->getState()->loseItem('Wooden Sword', 1);

            $pet->increaseEsteem(4);

            $message = $pet->getName() . ' created Wood\'s Metal by alloying an Iron Sword and a Wooden Sword.';

            if($this->squirrel3->rngNextInt(1, 4) === 1)
                $message .= ' (That\'s probably how that works, right?)';

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $message)
                ->setIcon('items/tool/sword/woodsmetal')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 17)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing', 'Physics' ]))
            ;
            $this->inventoryService->petCollectsItem('Wood\'s Metal', $pet, $pet->getName() . ' made this!', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make Wood\'s Metal, but couldn\'t figure it out...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing', 'Physics' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createCulinaryKnife(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Iron Sword', 1);
            $this->houseSimService->getState()->loseItem('Whisk', 1);

            $pet->increaseEsteem(2);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $pet->getName() . ' created a Culinary Knife.')
                ->setIcon('items/tool/whisk/dagger')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Culinary Knife', $pet, $pet->getName() . ' made this!', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Culinary Knife, but couldn\'t figure it out...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createDragonscale(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + max($petWithSkills->getDexterity()->getTotal(), $petWithSkills->getIntelligence()->getTotal()) + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 16)
        {
            $this->houseSimService->getState()->loseItem('Iron Sword', 1);
            $this->houseSimService->getState()->loseItem('Fluff', 1);
            $this->houseSimService->getState()->loseItem('Scales', 1);

            $pet->increaseEsteem(4);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% upgraded an Iron Sword into Dragonscale!')
                ->setIcon('items/tool/sword/dragon-scale')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Dragonscale', $pet, $pet->getName() . ' made this by inlaying Scales into an Iron Sword!', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make Dragonscale, but got intimidated by all the detailing work that would be required!')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createDrakkonscale(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + max($petWithSkills->getDexterity()->getTotal(), $petWithSkills->getIntelligence()->getTotal()) + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 17)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Iron Sword', 1);
            $this->houseSimService->getState()->loseItem('Fluff', 1);
            $this->houseSimService->getState()->loseItem('Dark Scales', 1);

            $pet->increaseEsteem(4);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% upgraded an Iron Sword into Drakkonscale!')
                ->setIcon('items/tool/sword/drakkon-scale')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Drakkonscale', $pet, $pet->getName() . ' made this by inlaying Dark Scales into an Iron Sword!', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make Drakkonscale, but got intimidated by all the detailing work that would be required!')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createTrinityBlade(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + max($petWithSkills->getDexterity()->getTotal(), $petWithSkills->getIntelligence()->getTotal()) + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 25)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Antipode', 1);
            $this->houseSimService->getState()->loseItem('Lightning Sword', 1);

            $pet->increaseEsteem(6);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% smithed a Trinity Blade!')
                ->setIcon('items/tool/sword/elemental')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 25)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Trinity Blade', $pet, $pet->getName() . ' created this by hammering a Lightning Sword and Antipode together!', $activityLog);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% wanted to make a Trinity Blade, but wasn\'t sure where to begin...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createWandOfIce(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + max($petWithSkills->getDexterity()->getTotal(), $petWithSkills->getIntelligence()->getTotal()) + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll == 1)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Poker', 1);
            $pet->increaseEsteem(-3);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Wand of Ice, but accidentally warped the Poker into an unrecognizable shape :|')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->inventoryService->petCollectsItem('Iron Bar', $pet, $pet->getName() . ' accidentally broke a Poker; this was all that remained!', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else if($roll >= 19)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Poker', 1);
            $this->houseSimService->getState()->loseItem('Everice', 1);

            $pet->increaseEsteem(4);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% smithed a Wand of Ice!')
                ->setIcon('items/tool/wand/ice')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 19)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Wand of Ice', $pet, $pet->getName() . ' created this by hammering Everice into a Poker!', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Wand of Ice, but the Everice was being uncooperative :|')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createIceFishing(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + max($petWithSkills->getDexterity()->getTotal(), $petWithSkills->getIntelligence()->getTotal()) + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Crooked Fishing Rod', 1);
            $this->houseSimService->getState()->loseItem('Everice', 1);

            $pet->increaseEsteem(2);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% smithed Ice Fishing!')
                ->setIcon('items/tool/fishing-rod/ice')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 19)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Ice Fishing', $pet, $pet->getName() . ' created this by hammering Everice into a Crooked Fishing Rod!', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make Ice Fishing, but the Everice was being uncooperative :|')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createDragonstick(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Gypsum Dragon', 1);
            $this->houseSimService->getState()->loseItem('Poker', 1);

            $pet->increaseEsteem(2);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Dragonstick!')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Dragonstick', $pet, $pet->getName() . ' created this by affixing the head of a Gypsum Dragon to a Poker!', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% started to make a dragon-inspired staff, but almost broke the Gypsum Dragon they were working with!')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createDragonbreath(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + max($petWithSkills->getDexterity()->getTotal(), $petWithSkills->getIntelligence()->getTotal()) + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 22)
        {
            $this->houseSimService->getState()->loseItem('Dragonstick', 1);
            $this->houseSimService->getState()->loseItem('Firestone', 1);

            $pet->increaseEsteem(4);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% infused a Dragonstick with the eternal heat of Firestone!')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 22)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Dragonbreath', $pet, $pet->getName() . ' made this by infusing a Dragonstick with the eternal heat of Firestone!', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a dragon-themed staff, but almost burned themselves on the Firestone...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createCoke(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll === 1)
        {
            $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 24));
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to refine some Charcoal, but got burned while trying! :(')
                ->setIcon('icons/activity-logs/burn')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }
        else if($roll >= 12)
        {
            $getRareStone = $this->squirrel3->rngNextInt(1, $pet->hasMerit(MeritEnum::LUCKY) ? 50 : 100) === 1;
            $rareStone = $this->squirrel3->rngNextFromArray([ 'Blackonite', 'Firestone' ]);
            $attributeLuckiness = false;

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Charcoal', 1);

            $pet->increaseEsteem($getRareStone ? 8 : 1);

            if($getRareStone)
            {
                $attributeLuckiness = $pet->hasMerit(MeritEnum::LUCKY) && $this->squirrel3->rngNextInt(1, 4) > 1;

                if($attributeLuckiness)
                {
                    $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% refined some Charcoal into Coke, and what\'s this?! There was a piece of ' . $rareStone . ' inside! Lucky~!')
                        ->setIcon('items/resource/coke')
                        ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                        ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Lucky~!' ]))
                    ;
                }
                else
                {
                    $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% refined some Charcoal into Coke, and what\'s this?! There was a piece of ' . $rareStone . ' inside!')
                        ->setIcon('items/resource/coke');
                }
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% refined some Charcoal into Coke.')
                    ->setIcon('items/resource/coke');
            }

            $activityLog->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]));

            $this->inventoryService->petCollectsItem('Coke', $pet, $pet->getName() . ' refined this from Charcoal.', $activityLog);

            if($getRareStone)
            {
                if($attributeLuckiness)
                {
                    $this->inventoryService->petCollectsItem($rareStone, $pet, $pet->getName() . ' found this while refining Charcoal into Coke! Lucky~!', $activityLog);
                    $activityLog->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Lucky~!' ]));
                }
                else
                    $this->inventoryService->petCollectsItem($rareStone, $pet, $pet->getName() . ' found this while refining Charcoal into Coke!', $activityLog);
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to refine Coke from Charcoal, but couldn\'t figure it out.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createIronBar(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2)
        {
            $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 24));

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to refine some Iron Ore, but got burned while trying! :(')
                ->setIcon('icons/activity-logs/burn')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }
        else if($roll >= 12)
        {
            $this->houseSimService->getState()->loseItem('Iron Ore', 1);
            $pet->increaseEsteem(1);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% refined some Iron Ore into an Iron Bar.')
                ->setIcon('items/element/iron-pure')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Iron Bar', $pet, $pet->getName() . ' refined this from Iron Ore.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to refine Iron Ore into an Iron Bar, but couldn\'t figure it out.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createSilverBar(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($pet->hasMerit(MeritEnum::SILVERBLOOD))
            $roll += 5;

        if($roll <= 2)
        {
            $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 12));
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to refine some Silver Ore, but got burned while trying! :(')
                ->setIcon('icons/activity-logs/burn')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Silver Ore', 1);
            $pet->increaseEsteem(1);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% refined some Silver Ore into a Silver Bar.')
                ->setIcon('items/element/silver-pure')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Silver Bar', $pet, $pet->getName() . ' refined this from Silver Ore.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to refine Silver Ore into a Silver Bar, but couldn\'t figure it out.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createGoldBar(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2)
        {
            $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 8));
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to refine some Gold Ore, but got burned while trying! :(')
                ->setIcon('icons/activity-logs/burn')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Gold Ore', 1);
            $pet->increaseEsteem(1);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% refined some Gold Ore into a Gold Bar.')
                ->setIcon('items/element/gold-pure')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Gold Bar', $pet, $pet->getName() . ' refined this from Gold Ore.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to refine Gold Ore into a Gold Bar, but couldn\'t figure it out.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    private function maybeMakeARainbowToo(ComputedPetSkills $petWithSkills, int $numberOfCrystalBalls): ?PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $lucky = $pet->hasMerit(MeritEnum::LUCKY) && $this->squirrel3->rngNextInt(1, 60) === 1;

        if($this->squirrel3->rngNextInt(1, 60) <= floor(sqrt(max(1, $petWithSkills->getScience()->getTotal()))) || $lucky)
        {
            $luckySuffix = $lucky ? ' Lucky~!' : '';

            $pet->increaseEsteem($this->squirrel3->rngNextInt(4, 8));

            $message = $numberOfCrystalBalls === 1
                ? 'While %pet:' . $pet->getId() . '.name% was making a Crystal Ball, they happened to catch the light just right, and caught a Rainbow, too!'
                : 'While %pet:' . $pet->getId() . '.name% was making some Crystal Balls, they happened to catch the light just right, and caught a Rainbow, too!'
            ;

            $tags = [ 'Physics' ];
            if($lucky) $tags[] = 'Lucky~!';

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $message . $luckySuffix)
                ->addInterestingness($lucky ? PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT : PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, $tags))
            ;

            $this->inventoryService->petCollectsItem('Rainbow', $pet, $pet->getName() . ' captured this while making a Crystal Ball!' . $luckySuffix, $activityLog);

            return $activityLog;
        }

        return null;
    }
}
