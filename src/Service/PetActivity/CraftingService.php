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


namespace App\Service\PetActivity;

use App\Entity\PetActivityLog;
use App\Enum\EnumInvalidValueException;
use App\Enum\HolidayEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityLogTagEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\CalendarFunctions;
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\SpiceRepository;
use App\Model\ActivityCallback;
use App\Model\ComputedPetSkills;
use App\Model\IActivityCallback;
use App\Model\PetChanges;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetActivity\Crafting\EventLanternService;
use App\Service\PetActivity\Crafting\Helpers\StickCraftingService;
use App\Service\PetActivity\Crafting\Helpers\TwuWuvCraftingService;
use App\Service\PetExperienceService;
use App\Service\WeatherService;
use Doctrine\ORM\EntityManagerInterface;

class CraftingService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly PetExperienceService $petExperienceService,
        private readonly StickCraftingService $stickCraftingService,
        private readonly EventLanternService $eventLanternService,
        private readonly TwuWuvCraftingService $twuWuvCraftingService,
        private readonly IRandom $rng,
        private readonly HouseSimService $houseSimService,
        private readonly EntityManagerInterface $em
    )
    {
    }

    /**
     * @return IActivityCallback[]
     */
    public function getCraftingPossibilities(ComputedPetSkills $petWithSkills): array
    {
        $possibilities = [];
        $now = new \DateTimeImmutable();

        if($this->houseSimService->hasInventory('Twu Wuv') && $this->houseSimService->hasInventory('Red Balloon'))
        {
            $possibilities[] = new ActivityCallback($this->twuWuvCraftingService->createWedBawwoon(...), 15);
        }

        if($this->houseSimService->hasInventory('Chocolate Bar'))
        {
            $weight = CalendarFunctions::isValentinesOrAdjacent($now) ? 80 : 8;

            $possibilities[] = new ActivityCallback($this->makeChocolateTool(...), $weight);
        }

        if($this->houseSimService->hasInventory('Fluff') || $this->houseSimService->hasInventory('Cobweb'))
        {
            $possibilities[] = new ActivityCallback($this->spinFluffOrCobweb(...), 10);
        }

        if($this->houseSimService->hasInventory('White Cloth'))
        {
            if($this->houseSimService->hasInventory('Quinacridone Magenta Dye'))
            {
                if($this->houseSimService->hasInventory('Fluff') || $this->houseSimService->hasInventory('Beans'))
                    $possibilities[] = new ActivityCallback($this->createPeacockPlushy(...), 10);
            }

            if($this->houseSimService->hasInventory('String') && $this->houseSimService->hasInventory('Ruby Feather'))
                $possibilities[] = new ActivityCallback($this->createFeatheredHat(...), 10);

            if($this->houseSimService->hasInventory('Glass Pendulum') && $this->houseSimService->hasInventory('Flute'))
                $possibilities[] = new ActivityCallback($this->createDecoratedFlute(...), 10);

            if($this->houseSimService->hasInventory('Stereotypical Bone'))
                $possibilities[] = new ActivityCallback($this->createTorchFromBone(...), 5);
        }

        if($this->houseSimService->hasInventory('Gold Telescope') && $this->houseSimService->hasInventory('Flying Grappling Hook'))
            $possibilities[] = new ActivityCallback($this->createLassoscope(...), 10);

        if($this->houseSimService->hasInventory('Tea Leaves'))
        {
            $possibilities[] = new ActivityCallback($this->createYellowDyeFromTeaLeaves(...), 10);

            if($this->houseSimService->hasInventory('Trowel'))
                $possibilities[] = new ActivityCallback($this->createTeaTrowel(...), 10);
        }

        if($this->houseSimService->hasInventory('Scales'))
        {
            $possibilities[] = new ActivityCallback($this->extractFromScales(...), 10);

            if($this->houseSimService->hasInventory('Talon') && $this->houseSimService->hasInventory('Wooden Sword'))
                $possibilities[] = new ActivityCallback($this->createSnakebite(...), 10);
        }

        if($this->houseSimService->hasInventory('Crooked Stick'))
        {
            if($this->houseSimService->hasInventory('Small, Yellow Plastic Bucket'))
                $possibilities[] = new ActivityCallback($this->stickCraftingService->createNanerPicker(...), 10);

            if($this->houseSimService->hasInventory('Sunflower'))
                $possibilities[] = new ActivityCallback($this->stickCraftingService->createSunflowerStick(...), 10);

            if($this->houseSimService->hasInventory('Glue') || $this->houseSimService->hasInventory('String'))
                $possibilities[] = new ActivityCallback($this->stickCraftingService->createWoodenSword(...), 10);

            if($this->houseSimService->hasInventory('Marshmallows'))
                $possibilities[] = new ActivityCallback($this->stickCraftingService->createSkeweredMarshmallow(...), 10);

            if($this->houseSimService->hasInventory('String'))
            {
                $possibilities[] = new ActivityCallback($this->stickCraftingService->createCrookedFishingRod(...), 10);

                if($this->houseSimService->hasInventory('Talon'))
                    $possibilities[] = new ActivityCallback($this->stickCraftingService->createHuntingSpear(...), 10);

                if($this->houseSimService->hasInventory('Hunting Spear'))
                    $possibilities[] = new ActivityCallback($this->stickCraftingService->createVeryLongSpear(...), 10);

                if($this->houseSimService->hasInventory('Overly-long Spear'))
                    $possibilities[] = new ActivityCallback($this->stickCraftingService->createRidiculouslyLongSpear(...), 10);

                if($this->houseSimService->hasInventory('Corn') && $this->houseSimService->hasInventory('Rice'))
                    $possibilities[] = new ActivityCallback($this->stickCraftingService->createHarvestStaff(...), 10);

                if($this->houseSimService->hasInventory('Red'))
                    $possibilities[] = new ActivityCallback($this->stickCraftingService->createRedFlail(...), 10);
            }

            if($this->houseSimService->hasInventory('Cobweb'))
                $possibilities[] = new ActivityCallback($this->stickCraftingService->createBugCatchersNet(...), 10);

            if($this->houseSimService->hasInventory('Glue') && ($this->houseSimService->hasInventory('Wheat') || $this->houseSimService->hasInventory('Rice')))
                $possibilities[] = new ActivityCallback($this->stickCraftingService->createStrawBroom(...), 10);

            if($this->houseSimService->hasInventory('White Cloth'))
                $possibilities[] = new ActivityCallback($this->stickCraftingService->createTorchOrFlag(...), 10);

            if($this->houseSimService->hasInventory('Toadstool') && $this->houseSimService->hasInventory('Quintessence'))
                $possibilities[] = new ActivityCallback($this->stickCraftingService->createChampignon(...), 10);

            if($this->houseSimService->hasInventory('Glass'))
                $possibilities[] = new ActivityCallback($this->stickCraftingService->createRusticMagnifyingGlass(...), 10);

            if($this->houseSimService->hasInventory('Sweet Beet') && $this->houseSimService->hasInventory('Glue'))
                $possibilities[] = new ActivityCallback($this->stickCraftingService->createSweetBeat(...), 10);

            if($this->houseSimService->hasInventory('Snail Shell') && $this->houseSimService->hasInventory('Glue'))
                $possibilities[] = new ActivityCallback($this->stickCraftingService->createWhorlStaff(...), 10);
        }

        if($this->houseSimService->hasInventory('Whorl Staff') && $this->houseSimService->hasInventory('Quinacridone Magenta Dye'))
            $possibilities[] = new ActivityCallback($this->createPaintedWhorlStaff(...), 10);

        if($this->houseSimService->hasInventory('Glue'))
        {
            if($this->houseSimService->hasInventory('White Cloth'))
            {
                if($this->houseSimService->hasInventory('Fiberglass Flute'))
                    $possibilities[] = new ActivityCallback($this->createFiberglassPanFlute(...), 11);

                $possibilities[] = new ActivityCallback($this->createFabricMache(...), 7);
            }

            if($this->houseSimService->hasInventory('Gold Triangle', 3))
                $possibilities[] = new ActivityCallback($this->createGoldTrifecta(...), 10);

            if($this->houseSimService->hasInventory('Ruler', 2))
                $possibilities[] = new ActivityCallback($this->createLSquare(...), 10);

            if($this->houseSimService->hasInventory('Cooking Buddy') && $this->houseSimService->hasInventory('Antenna'))
                $possibilities[] = new ActivityCallback($this->createAlienCookingBuddy(...), 10);

            if($this->houseSimService->hasInventory('Painted Camera') && $this->houseSimService->hasInventory('Antenna'))
                $possibilities[] = new ActivityCallback($this->createAlienCamera(...), 10);

            if($this->houseSimService->hasInventory('Bleached Turkey Head') && $this->houseSimService->hasInventory('Green Dye') && $this->houseSimService->hasInventory('Antenna'))
                $possibilities[] = new ActivityCallback($this->createChartrurkey(...), 20);

            if($this->houseSimService->hasInventory('Iron Sword') && $this->houseSimService->hasInventory('Laser Pointer'))
                $possibilities[] = new ActivityCallback($this->createLaserGuidedSword(...), 10);
        }

        if($this->houseSimService->hasInventory('Antenna'))
        {
            if($this->houseSimService->hasInventory('Cobweb') && $this->houseSimService->hasInventory('Fiberglass Bow'))
                $possibilities[] = new ActivityCallback($this->createBugBow(...), 10);

            if($this->houseSimService->hasInventory('Alien Tissue'))
                $possibilities[] = new ActivityCallback($this->createProboscis(...), 10);
        }

        if($this->houseSimService->hasInventory('String'))
        {
            if($this->houseSimService->hasInventory('Naner'))
                $possibilities[] = new ActivityCallback($this->createBownaner(...), 10);

            if($this->houseSimService->hasInventory('Glass'))
                $possibilities[] = new ActivityCallback($this->createGlassPendulum(...), 10);

            if($this->houseSimService->hasInventory('Paper') && $this->houseSimService->hasInventory('Silver Key'))
                $possibilities[] = new ActivityCallback($this->createBenjaminFranklin(...), 10);

            if($this->houseSimService->hasInventory('Really Big Leaf'))
                $possibilities[] = new ActivityCallback($this->createLeafSpear(...), 10);

            if($this->houseSimService->hasInventory('L-Square') && $this->houseSimService->hasInventory('Green Dye'))
                $possibilities[] = new ActivityCallback($this->createRibbelysComposite(...), 10);

            if(
                $this->houseSimService->hasInventory('Small Plastic Bucket') ||
                $this->houseSimService->hasInventory('Small, Yellow Plastic Bucket')
            )
            {
                $possibilities[] = new ActivityCallback($this->createShortRangeTelephone(...), 10);
            }

            if($this->houseSimService->hasInventory('"Rustic" Magnifying Glass') && $this->houseSimService->hasInventory('Black Feathers'))
                $possibilities[] = new ActivityCallback($this->createCrowsEye(...), 10);
        }

        if($this->houseSimService->hasInventory('Gypsum') && $this->houseSimService->hasInventory('Green Dye'))
            $possibilities[] = new ActivityCallback($this->createGypsumDragon(...), 9);

        if($this->houseSimService->hasInventory('No Right Turns') && $this->houseSimService->hasInventory('Green Dye'))
            $possibilities[] = new ActivityCallback($this->createWoherCuanNaniNani(...), 9);

        if($this->houseSimService->hasInventory('Bownaner') && $this->houseSimService->hasInventory('Carrot'))
            $possibilities[] = new ActivityCallback($this->createEatYourFruitsAndVeggies(...), 10);

        if($this->houseSimService->hasInventory('Feathers'))
        {
            if($this->houseSimService->hasInventory('Hunting Spear'))
                $possibilities[] = new ActivityCallback($this->createDecoratedSpear(...), 10);

            if($this->houseSimService->hasInventory('Yellow Dye'))
            {
                if($this->houseSimService->hasInventory('Fiberglass Pan Flute'))
                    $possibilities[] = new ActivityCallback($this->createOrnatePanFlute(...), 10);

                if($this->houseSimService->hasInventory('Tea Trowel'))
                    $possibilities[] = new ActivityCallback($this->createOwlTrowel(...), 10);
            }
        }

        if($this->houseSimService->hasInventory('White Feathers') && $this->houseSimService->hasInventory('Leaf Spear'))
            $possibilities[] = new ActivityCallback($this->createFishingRecorder(...), 10);

        if($this->houseSimService->hasInventory('Decorated Spear'))
        {
            if($this->houseSimService->hasInventory('Dark Scales'))
                $possibilities[] = new ActivityCallback($this->createNagatooth(...), 10);

            if($this->houseSimService->hasInventory('Quintessence'))
                $possibilities[] = new ActivityCallback($this->createVeilPiercer(...), 10);
        }

        if($this->houseSimService->hasInventory('Crooked Fishing Rod'))
        {
            if($this->houseSimService->hasInventory('Yellow Dye') && $this->houseSimService->hasInventory('Green Dye'))
                $possibilities[] = new ActivityCallback($this->createPaintedFishingRod(...), 10);

            if($this->houseSimService->hasInventory('Carrot'))
                $possibilities[] = new ActivityCallback($this->createCaroteneStick(...), 10);
        }

        if($this->houseSimService->hasInventory('Plastic Boomerang') && $this->houseSimService->hasInventory('Quinacridone Magenta Dye'))
            $possibilities[] = new ActivityCallback($this->createPaintedBoomerang(...), 10);

        if($this->houseSimService->hasInventory('Yellow Dye'))
        {
            if($this->houseSimService->hasInventory('Plastic Idol'))
                $possibilities[] = new ActivityCallback($this->createGoldIdol(...), 10);

            if($this->houseSimService->hasInventory('Small Plastic Bucket'))
                $possibilities[] = new ActivityCallback($this->createYellowBucket(...), 10);

            if($this->houseSimService->hasInventory('Dumbbell'))
                $possibilities[] = new ActivityCallback($this->createPaintedDumbbell(...), 10);

            if($this->houseSimService->hasInventory('Digital Camera'))
                $possibilities[] = new ActivityCallback($this->createPaintedCamera(...), 10);
        }

        if($this->houseSimService->hasInventory('Fiberglass'))
            $possibilities[] = new ActivityCallback($this->createSimpleFiberglassItem(...), 10);

        if($this->houseSimService->hasInventory('Scythe'))
        {
            if($this->houseSimService->hasInventory('Scythe', 2))
                $possibilities[] = new ActivityCallback($this->createDoubleScythe(...), 10);

            if($this->houseSimService->hasInventory('Garden Shovel'))
                $possibilities[] = new ActivityCallback($this->createFarmersMultiTool(...), 10);
        }

        if($this->houseSimService->hasInventory('Garden Shovel') && $this->houseSimService->hasInventory('Fish Bones'))
            $possibilities[] = new ActivityCallback($this->createFishHeadShovel(...), 10);

        if($this->houseSimService->hasInventory('White Flag'))
        {
            if($this->houseSimService->hasInventory('Yellow Dye'))
                $possibilities[] = new ActivityCallback($this->createSunFlag(...), 10);

            if($this->houseSimService->hasInventory('Green Dye'))
                $possibilities[] = new ActivityCallback($this->createDragonFlag(...), 10);

            if($this->houseSimService->hasInventory('String') && $this->houseSimService->hasInventory('Crooked Stick'))
                $possibilities[] = new ActivityCallback($this->createBindle(...), 10);

            if($this->houseSimService->hasInventory('Crooked Fishing Rod'))
                $possibilities[] = new ActivityCallback($this->createBindle2(...), 10);
        }

        if($this->houseSimService->hasInventory('Sun Flag') && $this->houseSimService->hasInventory('Sunflower Stick'))
            $possibilities[] = new ActivityCallback($this->createSunSunFlag(...), 10);

        if($this->houseSimService->hasInventory('Plastic'))
        {
            if($this->houseSimService->hasInventory('Smallish Pumpkin') && $this->houseSimService->hasInventory('Crooked Stick'))
                $possibilities[] = new ActivityCallback($this->createDrumpkin(...), 10);

            if($this->houseSimService->hasInventory('Iron Bar'))
                $possibilities[] = new ActivityCallback($this->createGrabbyArm(...), 10);
        }

        if($this->houseSimService->hasInventory('Rice Flour') && $this->houseSimService->hasInventory('Potato'))
            $possibilities[] = new ActivityCallback($this->createRicePaper(...), 10);

        $repairWeight = ($petWithSkills->getSmithingBonus()->getTotal() >= 3 || $petWithSkills->getCrafts()->getTotal() >= 5) ? 10 : 1;

        if($this->houseSimService->hasInventory('Rusty Blunderbuss'))
            $possibilities[] = new ActivityCallback($this->repairRustyBlunderbuss(...), $repairWeight);

        if($this->houseSimService->hasInventory('Rusty Rapier'))
            $possibilities[] = new ActivityCallback($this->repairRustyRapier(...), $repairWeight);

        if($this->houseSimService->hasInventory('Rusted, Busted Mechanism'))
            $possibilities[] = new ActivityCallback($this->repairOldMechanism(...), $repairWeight);

        if($this->houseSimService->hasInventory('Sun-sun Flag', 2))
            $possibilities[] = new ActivityCallback($this->createSunSunFlagFlagSon(...), 10);

        if($this->houseSimService->hasInventory('Moon Pearl'))
        {
            if($this->houseSimService->hasInventory('Plastic Fishing Rod') && $this->houseSimService->hasInventory('Talon'))
                $possibilities[] = new ActivityCallback($this->createPaleFlail(...), 10);
        }

        if($this->houseSimService->hasInventory('Blue Balloon') && $this->houseSimService->hasInventory('Gold Telescope'))
            $possibilities[] = new ActivityCallback($this->makeSpyBalloon(...), 10);

        return array_merge($possibilities, $this->eventLanternService->getCraftingPossibilities($petWithSkills));
    }

    /**
     * @param IActivityCallback[] $possibilities
     */
    public function adventure(ComputedPetSkills $petWithSkills, array $possibilities): PetActivityLog
    {
        if(count($possibilities) === 0)
            throw new \InvalidArgumentException('possibilities must contain at least one item.');

        /** @var IActivityCallback $method */
        $method = $this->rng->rngNextFromArray($possibilities);

        $changes = new PetChanges($petWithSkills->getPet());

        /** @var PetActivityLog $activityLog */
        $activityLog = $method->getCallable()($petWithSkills);

        $activityLog->setChanges($changes->compare($petWithSkills->getPet()));

        return $activityLog;
    }

    public function createTorchFromBone(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 8)
        {
            $this->houseSimService->getState()->loseItem('White Cloth', 1);
            $this->houseSimService->getState()->loseItem('Stereotypical Bone', 1);
            $pet->increaseEsteem(2);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Stereotypical Torch.')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 90), PetActivityStatEnum::CRAFT, true);

            $this->inventoryService->petCollectsItem('Stereotypical Torch', $pet, $pet->getName() . ' created this from White Cloth and a Stereotypical Bone.', $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Stereotypical Torch, but couldn\'t figure it out.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 90), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createTeaTrowel(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 12)
        {
            $this->houseSimService->getState()->loseItem('Tea Leaves', 1);
            $this->houseSimService->getState()->loseItem('Trowel', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Tea Trowel.')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;
            $this->inventoryService->petCollectsItem('Tea Trowel', $pet, $pet->getName() . ' made this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 90), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Tea Trowel... or was it a Tea _Towel?_ It\'s all very confusing.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 90), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createOwlTrowel(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 16)
        {
            $this->houseSimService->getState()->loseItem('Tea Trowel', 1);
            $this->houseSimService->getState()->loseItem('Feathers', 1);
            $this->houseSimService->getState()->loseItem('Yellow Dye', 1);
            $pet->increaseEsteem($this->rng->rngNextInt(2, 4));
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created an Owl Trowel.')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;
            $this->inventoryService->petCollectsItem('Owl Trowel', $pet, $pet->getName() . ' made this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 90), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make an Owl Trowel, but couldn\'t figure it out.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 90), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createSimpleFiberglassItem(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        $item = $this->rng->rngNextFromArray([
            'Fiberglass Flute'
        ]);

        if($roll <= 2)
        {
            $this->houseSimService->getState()->loseItem('Fiberglass', 1);
            $pet->increaseSafety(-4);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a ' . $item . ', but accidentally cut themselves on the Fiberglass! :(')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 14)
        {
            $this->houseSimService->getState()->loseItem('Fiberglass', 1);

            $pet->increaseEsteem(2);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% made a ' . $item . ' from Fiberglass.')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;
            $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' created this from Fiberglass.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(120, 150), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a ' . $item . ', but the Fiberglass wasn\'t cooperating.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 150), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createDecoratedFlute(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $this->houseSimService->getState()->loseItem('White Cloth', 1);
            $pet->increaseEsteem(-2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried decorate a Flute, but while trying to make ribbons, accidentally tore the White Cloth to shreds...')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 18)
        {
            $this->houseSimService->getState()->loseItem('White Cloth', 1);
            $this->houseSimService->getState()->loseItem('Flute', 1);
            $this->houseSimService->getState()->loseItem('Glass Pendulum', 1);
            $pet->increaseEsteem(3);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Decorated Flute.')
                ->setIcon('items/tool/instrument/flute-decorated')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;
            $this->inventoryService->petCollectsItem('Decorated Flute', $pet, $pet->getName() . ' created this by tying a Glass Pendulum to a Flute.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 150), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% thought it might be cool to decorate a Flute, but couldn\'t think of something stylish enough.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createDrumpkin(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2 && $pet->getFood() < 4)
        {
            $this->houseSimService->getState()->loseItem('Smallish Pumpkin', 1);
            $pet->increaseFood(6);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Drumpkin, but broke the Smallish Pumpkin :( Not wanting to waste it, %pet:' . $pet->getId() . '.name% ate the remains...)')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home, 'Smithing', 'Eating' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 15)
        {
            $this->houseSimService->getState()->loseItem('Plastic', 1);
            $this->houseSimService->getState()->loseItem('Smallish Pumpkin', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $pet->increaseEsteem(3);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Drumpkin!')
                ->setIcon('items/tool/instrument/drumpkin')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home, 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Drumpkin', $pet, $pet->getName() . ' created this!', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 150), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Drumpkin, but couldn\'t get the Plastic thin enough...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home, 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function createRicePaper(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2 && $pet->getFood() < 4)
        {
            $this->houseSimService->getState()->loseItem('Potato', 1);
            $pet->increaseFood(4);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make Paper, but messed up the Potato :( (Not wanting to waste it, %pet:' . $pet->getId() . '.name% ate the remains...)')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home, 'Eating' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 15)
        {
            $paperCount = 2;

            if($roll >= 20) $paperCount++;
            if($roll >= 25) $paperCount++;

            $this->houseSimService->getState()->loseItem('Rice Flour', 1);
            $this->houseSimService->getState()->loseItem('Potato', 1);
            $pet->increaseEsteem($paperCount * 2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created ' . $paperCount . ' Paper!')
                ->setIcon('items/resource/paper')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 5 + $paperCount * 5)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            for($i = 0; $i < $paperCount; $i++)
                $this->inventoryService->petCollectsItem('Paper', $pet, $pet->getName() . ' created this!', $activityLog);

            $this->petExperienceService->gainExp($pet, $paperCount, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 110 + $paperCount * 10), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make Paper, but almost wasted the Rice Flour...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createDecoratedSpear(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 12)
        {
            $this->houseSimService->getState()->loseItem('Feathers', 1);
            $this->houseSimService->getState()->loseItem('Hunting Spear', 1);
            $pet->increaseEsteem(1);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Decorated Spear.')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;
            $this->inventoryService->petCollectsItem('Decorated Spear', $pet, $pet->getName() . ' decorated a Hunting Spear with Feathers to make this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to decorate a Hunting Spear with Feathers, but couldn\'t get the look just right.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createFishingRecorder(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getMusic()->getTotal());

        if($roll >= 14)
        {
            $this->houseSimService->getState()->loseItem('Leaf Spear', 1);
            $this->houseSimService->getState()->loseItem('White Feathers', 1);
            $pet->increaseEsteem(4);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Fishing Recorder.')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            if($this->rng->rngNextInt(1, 5) === 1)
                $this->inventoryService->petCollectsItem('Fishing Recorder', $pet, $pet->getName() . ' made this. (The White Feathers are a nice touch, don\'t you think?)', $activityLog);
            else
                $this->inventoryService->petCollectsItem('Fishing Recorder', $pet, $pet->getName() . ' made this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::MUSIC ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% wanted to make a Fishing Recorder, but couldn\'t figure out where all the holes should go...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::MUSIC ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 90), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function createDoubleScythe(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 14)
        {
            $this->houseSimService->getState()->loseItem('Scythe', 2);

            // a bit of fixed PGC
            if((($pet->getId() % 29) + ($pet->getOwner()->getId() % 31)) % 3 === 1)
            {
                $and = 'honestly, it looks kind of silly...';
            }
            else
            {
                $pet->increaseEsteem(5);
                $and = 'it looks pretty bad-ass!';
            }

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Double Scythe; ' . $and)
                ->setIcon('items/tool/scythe/double')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->inventoryService->petCollectsItem('Double Scythe', $pet, $pet->getName() . ' created this by taking the blade from one Scythe and attaching it to another.', $activityLog);
            $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' "made" this by taking the blade off of a Scythe (to make a Double Scythe).', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% thought it might be cool to make a Double Scythe, but then doubted themself...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createFishHeadShovel(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 14)
        {
            $this->houseSimService->getState()->loseItem('Fish Bones', 1);
            $this->houseSimService->getState()->loseItem('Garden Shovel', 1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Fish Head Shovel!')
                ->setIcon('items/tool/shovel/fish-head')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->inventoryService->petCollectsItem('Fish Head Shovel', $pet, $pet->getName() . ' created this by adorning a Garden Shovel with some Fish Bones.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            if($this->rng->rngNextInt(1, 10) === 1)
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% wanted to make a Fish Head Shovel, but couldn\'t figure out how to arrange the bones #relatable')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
                ;
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% wanted to make a Fish Head Shovel, but couldn\'t figure out how to arrange the bones...')
                    ->setIcon('icons/activity-logs/confused')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createFarmersMultiTool(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 14)
        {
            $this->houseSimService->getState()->loseItem('Scythe', 1);
            $this->houseSimService->getState()->loseItem('Garden Shovel', 1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Farmer\'s Multi-tool!')
                ->setIcon('items/tool/shovel/multi-tool')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->inventoryService->petCollectsItem('Farmer\'s Multi-tool', $pet, $pet->getName() . ' created this by combining the best parts of a Scythe and a Garden Shovel.', $activityLog);
            $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' had this left over after making a Farmer\'s Multi-tool.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% wanted to combine a Scythe and a Garden Shovel, but couldn\'t decide which of the two tools to start with?')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function spinFluffOrCobweb(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $making = ItemRepository::findOneByName($this->em, $this->rng->rngNextFromArray([
            'String',
            'White Cloth'
        ]));

        $difficulty = $making->getName() === 'String' ? 10 : 13;

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= $difficulty)
        {
            $spunWhat = $this->houseSimService->getState()->loseOneOf($this->rng, [ 'Fluff', 'Cobweb' ]);

            if($roll >= $difficulty + 12)
            {
                $pet->increaseEsteem(3);

                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% spun some ' . $spunWhat . ' into TWO ' . $making->getName() . '!')
                    ->setIcon('items/' . $making->getImage())
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + $difficulty + 12)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
                ;

                $this->inventoryService->petCollectsItem($making, $pet, $pet->getName() . ' spun this from ' . $spunWhat . '.', $activityLog);

                $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(120, 150), PetActivityStatEnum::CRAFT, true);
            }
            else
            {
                $pet->increaseEsteem(1);

                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% spun some ' . $spunWhat . ' into ' . $making->getName() . '.')
                    ->setIcon('items/' . $making->getImage())
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + $difficulty)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
                ;

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
            }

            $this->inventoryService->petCollectsItem($making, $pet, $pet->getName() . ' spun this from ' . $spunWhat . '.', $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to spin some ' . $making->getName() . ', but couldn\'t figure it out.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function makeChocolateTool(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $now = new \DateTimeImmutable();

        if(CalendarFunctions::isValentinesOrAdjacent($now))
            $making = ItemRepository::findOneByName($this->em, 'Chocolate Key');
        else
        {
            $making = ItemRepository::findOneByName($this->em, $this->rng->rngNextFromArray([
                'Chocolate Sword',
                'Chocolate Sword',
                'Chocolate Hammer',
                'Chocolate Hammer',
                'Chocolate Key'
            ]));
        }

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2 && $pet->getFood() < 4)
        {
            $this->houseSimService->getState()->loseItem('Chocolate Bar', 1);

            $pet->increaseFood($this->rng->rngNextInt(2, 4));
            $pet->increaseEsteem(-2);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% started making ' . $making->getNameWithArticle() . ', but ended up eating the Chocolate Bar, instead >_>')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home, 'Eating' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 10)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Chocolate Bar', 1);

            $makeTwo = $roll >= 20 && $making->getName() === 'Chocolate Key';

            if($makeTwo)
            {
                $pet->increaseEsteem(4);

                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% molded a Chocolate Bar into TWO ' . $making->getName() . 's!')
                    ->setIcon('items/' . $making->getImage())
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                        PetActivityLogTagEnum::Crafting,
                        PetActivityLogTagEnum::Location_At_Home,
                    ]))
                ;

                $this->inventoryService->petCollectsItem($making, $pet, $pet->getName() . ' made this out of a Chocolate Bar.', $activityLog);

                $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ], $activityLog);
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% molded a Chocolate Bar into ' . $making->getNameWithArticle() . '.')
                    ->setIcon('items/' . $making->getImage())
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                        PetActivityLogTagEnum::Crafting,
                        PetActivityLogTagEnum::Location_At_Home,
                    ]))
                ;

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            }

            $this->inventoryService->petCollectsItem($making, $pet, $pet->getName() . ' made this out of a Chocolate Bar.', $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make ' . $making->getNameWithArticle() . ', but couldn\'t get the mold right...')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createYellowDyeFromTeaLeaves(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 4)
        {
            $this->houseSimService->getState()->loseItem('Tea Leaves', 1);

            $message = $pet->getName() . ' tried to extract Yellow Dye from Tea Leaves, but accidentally made Black Tea, instead!';

            if($this->rng->rngNextInt(1, 10) === 1)
                $message .= ' (Aren\'t the leaves themselves green? Where are all these colors coming from?!)';

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $message)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Black Tea', $pet, $pet->getName() . ' accidentally made this while trying to extract Yellow Dye from Tea Leaves.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(120, 150), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Tea Leaves', 1);
            $pet->increaseEsteem(1);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% extracted Yellow Dye from some Tea Leaves.')
                ->setIcon('items/resource/dye-yellow')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Yellow Dye', $pet, $pet->getName() . ' extracted this from Tea Leaves.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% wanted to extract Yellow Dye from some Tea Leaves, but wasn\'t sure how to start.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function extractFromScales(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getCrafts()->getTotal());
        $itemName = $this->rng->rngNextInt(1, 2) === 1 ? 'Green Dye' : 'Glue';

        if($roll >= 20)
        {
            $this->houseSimService->getState()->loseItem('Scales');
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% extracted Green Dye _and_ Glue from some Scales!')
                ->setIcon('items/animal/scales')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Green Dye', $pet, $pet->getName() . ' extracted this from Scales.', $activityLog);
            $this->inventoryService->petCollectsItem('Glue', $pet, $pet->getName() . ' extracted this from Scales.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(120, 180), PetActivityStatEnum::CRAFT, true);
        }
        else if($roll >= 12)
        {
            $this->houseSimService->getState()->loseItem('Scales');
            $pet->increaseEsteem(1);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% extracted ' . $itemName . ' from some Scales.')
                ->setIcon('items/animal/scales')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' extracted this from Scales.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(120, 150), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% wanted to extract ' . $itemName . ' from some Scales, but wasn\'t sure how to start.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function createFabricMache(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 14)
        {
            $possibleItems = [
                'Fabric Mâché Basket'
            ];

            $item = $this->rng->rngNextFromArray($possibleItems);

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(120, 150), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('White Cloth', 1);
            $this->houseSimService->getState()->loseItem('Glue', 1);
            $pet->increaseEsteem(2);

            if($item === 'Fabric Mâché Basket' && $this->rng->rngNextInt(1, 10) === 1)
            {
                $transformation = $this->rng->rngNextFromArray([
                    [ 'item' => 'Flower Basket', 'goodies' => 'flowers' ],
                    [ 'item' => 'Fruit Basket', 'goodies' => 'fruit' ],
                ]);

                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Fabric Mâché Basket. Once they were done, a fairy appeared out of nowhere, and filled the basket up with ' . $transformation['goodies'] . '!')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                        PetActivityLogTagEnum::Crafting,
                        PetActivityLogTagEnum::Location_At_Home,
                        PetActivityLogTagEnum::Fae_kind,
                    ]))
                ;
                $this->inventoryService->petCollectsItem($transformation['item'], $pet, $pet->getName() . ' created a Fabric Mâché Basket; once they were done, a fairy appeared and filled it with ' . $transformation['goodies'] . '!', $activityLog);
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a ' . $item . '.')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                        PetActivityLogTagEnum::Crafting,
                        PetActivityLogTagEnum::Location_At_Home,
                    ]))
                ;
                $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' created this from White Cloth and Glue.', $activityLog);
            }

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make some Fabric Mâché, but couldn\'t come up with a good pattern.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function createGoldTrifecta(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(120, 150), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Gold Triangle', 3);
            $this->houseSimService->getState()->loseItem('Glue', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Gold Trifecta.')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Gold Trifecta', $pet, $pet->getName() . ' created by gluing together three Gold Triangles.', $activityLog);

            if($this->rng->rngNextInt(1, 2) === 1) $this->inventoryService->petCollectsItem('String', $pet, $pet->getName() . ' recovered this when creating a Gold Trifecta.', $activityLog);
            if($this->rng->rngNextInt(1, 2) === 1) $this->inventoryService->petCollectsItem('String', $pet, $pet->getName() . ' recovered this when creating a Gold Trifecta.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog =  PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% wanted to make a Gold Trifecta, but wasn\'t sure how to begin...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createLSquare(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll == 1)
        {
            $this->houseSimService->getState()->loseItem('Ruler', 1);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% started to make an L-Square, but accidentally snapped one of the Rulers in two! :|')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Ruler', 2);
            $this->houseSimService->getState()->loseItem('Glue', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created an L-Square.')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('L-Square', $pet, $pet->getName() . ' created by gluing together a couple Rulers.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% wanted to make an L-Square, but spent forever trying to make it _exactly_ 90 degrees, and eventually gave up...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(120, 150), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createAlienCookingBuddy(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 10)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(120, 150), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Cooking Buddy', 1);
            $this->houseSimService->getState()->loseItem('Glue', 1);
            $this->houseSimService->getState()->loseItem('Antenna', 1);
            $pet
                ->increaseEsteem(4)
                ->increaseSafety(2)
            ;
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% cracked themselves up by creating a Cooking "Alien".')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Cooking "Alien"', $pet, $pet->getName() . ' created by gluing some Antennae on a Cooking Buddy.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% wanted to do something silly to a Cooking Buddy, but couldn\'t decide what...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createAlienCamera(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 10)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(120, 150), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Painted Camera', 1);
            $this->houseSimService->getState()->loseItem('Glue', 1);
            $this->houseSimService->getState()->loseItem('Antenna', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% put together an "Alien" Camera!')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('"Alien" Camera', $pet, $pet->getName() . ' created by gluing some Antennae to a Painted Camera.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% wanted to do something silly to a Painted Camera, but couldn\'t decide what...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createChartrurkey(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 20)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Bleached Turkey Head', 1);
            $this->houseSimService->getState()->loseItem('Glue', 1);
            $this->houseSimService->getState()->loseItem('Antenna', 1);
            $this->houseSimService->getState()->loseItem('Green Dye', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% gussied up a Bleached Turkey Head with Green Dye and some Antenna. You know: as you do.')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Chartrurkey', $pet, $pet->getName() . ' gussied up a Bleached Turkey Head. This is the result.', $activityLog);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% wanted to do something silly to a Bleached Turkey Head, but couldn\'t decide what...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 90), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createBugBow(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getNature()->getTotal(), $petWithSkills->getCrafts()->getTotal()));

        if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(120, 150), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Fiberglass Bow', 1);
            $this->houseSimService->getState()->loseItem('Cobweb', 1);
            $this->houseSimService->getState()->loseItem('Antenna', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% turned a boring ol\' Fiberglass Bow into a Bug Bow!')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Bug Bow', $pet, $pet->getName() . ' created this out of a Fiberglass Bow and some bug bits.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% started making a Bug Bow, but kept getting stuck to the Cobweb.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createProboscis(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 +
            $petWithSkills->getIntelligence()->getTotal() +
            $petWithSkills->getDexterity()->getTotal() +
            $petWithSkills->getCrafts()->getTotal()
        );

        if($roll >= 26)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(120, 150), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Antenna', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% spun a Proboscis from Alien Tissue and Antenna, and there was still plenty of Alien Tissue left over!')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Proboscis', $pet, $pet->getName() . ' created this out of Alien Tissue and some bug bits.', $activityLog);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(120, 150), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Alien Tissue', 1);
            $this->houseSimService->getState()->loseItem('Antenna', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% spun a Proboscis from Alien Tissue and Antenna!')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Proboscis', $pet, $pet->getName() . ' created this out of Alien Tissue and some bug bits.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Proboscis, but had trouble spinning the Alien Tissue.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createFiberglassPanFlute(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getMusic()->getTotal(), $petWithSkills->getCrafts()->getTotal()));

        if($roll == 1)
        {
            $this->houseSimService->getState()->loseItem('White Cloth', 1);
            $pet->increaseEsteem(-2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% wanted to make a Fiberglass Pan Flute, but while making ribbons, accidentally tore the White Cloth to shreds...')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(120, 150), PetActivityStatEnum::CRAFT, true);

            $this->houseSimService->getState()->loseItem('White Cloth', 1);
            $this->houseSimService->getState()->loseItem('Glue', 1);
            $this->houseSimService->getState()->loseItem('Fiberglass Flute', 1);

            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Fiberglass Pan Flute.')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Fiberglass Pan Flute', $pet, $pet->getName() . ' created this by hacking a Fiberglass Flute into several pieces, and gluing them together with a ribbon of cloth.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% wanted to make a Fiberglass Pan Flute, but didn\'t feel confident about cutting the Fiberglass Flute in half.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createGlassPendulum(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $pet->increaseSafety(-4);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% started to cut a piece of glass, but cut themselves, instead! :(')
                ->setIcon('icons/activity-logs/wounded')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 150), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Glass', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% cut some Glass to look like a gem, and made a Glass Pendulum.')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Glass Pendulum', $pet, $pet->getName() . ' created this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $pet->increaseSafety(-1);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Glass Pendulum, but almost cut themselves on the glass, and gave up.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createBownaner(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() * 2 + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2 && $pet->getFood() < 4)
        {
            $this->houseSimService->getState()->loseItem('Naner', 1);
            $pet->increaseFood(4);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% started to make a bow out of a Naner, but was feeling hungry, so... they ate the Naner.')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home, 'Eating' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 11)
        {
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Naner', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% made a makeshift bow... out of a Naner.')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 11)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Bownaner', $pet, $pet->getName() . ' created this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Bownaner, but the String kept getting all tangled.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createGypsumDragon(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 15)
        {
            $this->houseSimService->getState()->loseItem('Gypsum', 1);
            $this->houseSimService->getState()->loseItem('Green Dye', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% sculpted some Gypsum into the shape of a dragon!')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Gypsum Dragon', $pet, $pet->getName() . ' created this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to sculpt some Gypsum, but had trouble getting it to cooperate...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createEatYourFruitsAndVeggies(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $weather = WeatherService::getWeather(new \DateTimeImmutable(), $pet);
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() * 2 + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2 && $pet->getFood() < 4)
        {
            $this->houseSimService->getState()->loseItem('Carrot', 1);
            $pet->increaseFood(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% started to make an Eat Your Fruits And Veggies, but was feeling hungry, so... they ate the Carrot.')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home, 'Eating' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Carrot', 1);
            $this->houseSimService->getState()->loseItem('Bownaner', 1);
            $pet->increaseEsteem(2);

            if($roll >= 22 || $weather->isHoliday(HolidayEnum::EASTER))
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% made an Eat Your Fruits and Veggies, and even had enough Carrot left over to make a Carrot Key!')
                    ->addInterestingness($weather->isHoliday(HolidayEnum::EASTER) ? PetActivityLogInterestingnessEnum::HOLIDAY_OR_SPECIAL_EVENT : (PetActivityLogInterestingnessEnum::HO_HUM + 22))
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                        PetActivityLogTagEnum::Crafting,
                        PetActivityLogTagEnum::Location_At_Home,
                        'Special Event',
                        'Easter'
                    ]))
                ;

                $this->inventoryService->petCollectsItem('Carrot Key', $pet, $pet->getName() . ' made this.', $activityLog);
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% loaded a Bownaner with a Carrot...')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 12)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                        PetActivityLogTagEnum::Crafting,
                        PetActivityLogTagEnum::Location_At_Home,
                    ]))
                ;
            }

            $this->inventoryService->petCollectsItem('Eat Your Fruits and Veggies', $pet, $pet->getName() . ' created this.', $activityLog);

            $this->petExperienceService->gainExp($pet, $roll >= 22 ? 3 : 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to load a Bownaner with a Carrot, but the String kept getting all tangled.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createLeafSpear(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getStrength()->getTotal() * 2 + $petWithSkills->getDexterity()->getTotal());

        if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 150), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Really Big Leaf', 1);
            $pet->increaseEsteem(2);

            $message = $pet->getName() . ' rolled up a Really Big Leaf, and tied it, creating a Leaf Spear!';

            if($this->rng->rngNextInt(1, 5) > $petWithSkills->getStrength()->getTotal())
                $message .= ' (It\'s harder than it looks!)';

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $message)
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Leaf Spear', $pet, $pet->getName() . ' created this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $pet->increaseSafety(-1);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Leaf Spear, but the Really Big Leaf is surprisingly strong! ' . $pet->getName() . ' couldn\'t get it to roll up...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createBenjaminFranklin(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getScience()->getTotal()));

        if($roll >= 17)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 150), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Paper', 1);
            $this->houseSimService->getState()->loseItem('Silver Key', 1);
            $pet->increaseEsteem(3);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created Benjamin Franklin. (A kite, not the person.)')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 17)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_Neighborhood,
                    PetActivityLogTagEnum::Physics,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Benjamin Franklin', $pet, $pet->getName() . ' created this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a kite, but couldn\'t come up with a good design...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home, 'Physics' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createShortRangeTelephone(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $this->houseSimService->getState()->loseItem('String', 1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% started to make a Short-range Telephone, but accidentally broke the String! :(')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 90), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
            $bucketType = $this->houseSimService->getState()->loseOneOf($this->rng, [ 'Small Plastic Bucket', 'Small, Yellow Plastic Bucket' ]);
            $this->houseSimService->getState()->loseItem('String', 1);
            $pet->increaseEsteem(2);

            if($bucketType === 'Small, Yellow Plastic Bucket' && $roll >= 20)
            {
                $extra = $this->rng->rngNextInt(1, 10) === 1
                    ? ' (That\'s totally how dye works! Yep!)'
                    : ''
                ;

                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Short-range Telephone, and was even able to squeeze the Yellow Dye out of the Small, Yellow Plastic Bucket they used.' . $extra)
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                        PetActivityLogTagEnum::Crafting,
                        PetActivityLogTagEnum::Location_At_Home,
                    ]))
                ;

                $this->inventoryService->petCollectsItem('Yellow Dye', $pet, $pet->getName() . ' recovered this from a Small, Yellow Plastic Bucket while making a Short-range Telephone', $activityLog);
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Short-range Telephone. They tried to extract the Yellow Dye from the Small, Yellow Plastic Bucket they used, but wasn\'t able to recover a useful amount of it.')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                        PetActivityLogTagEnum::Crafting,
                        PetActivityLogTagEnum::Location_At_Home,
                    ]))
                ;
            }

            $this->inventoryService->petCollectsItem('Short-range Telephone', $pet, $pet->getName() . ' created this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to connect two plastic "cans", but the string kept getting tangled.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createCrowsEye(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $this->houseSimService->getState()->loseItem('String', 1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% started to make a Crow\'s Eye, but accidentally broke the String they were trying to use! :(')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 90), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 20)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('"Rustic" Magnifying Glass', 1);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Black Feathers', 1);
            $pet->increaseEsteem(3);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Crow\'s Eye... with Roadkill?!')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $roadkill = SpiceRepository::findOneByName($this->em, 'with Roadkill');

            $this->inventoryService->petCollectsEnhancedItem('Crow\'s Eye', null, $roadkill, $pet, $pet->getName() . ' created this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Crow\'s Eye, but felt they were missing a certain... je ne sais quoi.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createRibbelysComposite(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Green Dye', 1);
            $this->houseSimService->getState()->loseItem('L-Square', 1);
            $pet->increaseEsteem(3);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created Ribbely\'s Composite.')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Ribbely\'s Composite', $pet, $pet->getName() . ' created this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a bow out of an L-Square, but, ironically, couldn\'t get the measurements right.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function createFeatheredHat(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('White Cloth', 1);
            $this->houseSimService->getState()->loseItem('Ruby Feather', 1);
            $pet->increaseEsteem(5);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% made an Afternoon Hat by shaping some White Cloth, and tying a Ruby Feather to it!')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Afternoon Hat', $pet, $pet->getName() . ' created this!', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% wanted to make a hat, but couldn\'t come up with a good design...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createOrnatePanFlute(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getMusic()->getTotal()));

        if($roll >= 18)
        {
            $this->houseSimService->getState()->loseItem('Fiberglass Pan Flute', 1);
            $this->houseSimService->getState()->loseItem('Yellow Dye', 1);
            $this->houseSimService->getState()->loseItem('Feathers', 1);
            $pet->increaseEsteem(3);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created an Ornate Pan Flute.')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Ornate Pan Flute', $pet, $pet->getName() . ' created this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS, PetSkillEnum::MUSIC ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% wanted to decorate a Fiberglass Pan Flute, but couldn\'t come up with a good design.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::MUSIC ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createSnakebite(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $pet->increaseEsteem(-2);
            $pet->increaseSafety(-4);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to craft Snakebite, but cut themself on a Talon!')
                ->setIcon('icons/activity-logs/wounded')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 150), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Talon', 1);
            $this->houseSimService->getState()->loseItem('Scales', 1);
            $this->houseSimService->getState()->loseItem('Wooden Sword', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Snakebite sword.')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Snakebite', $pet, $pet->getName() . ' made this by improving a Wooden Sword.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to improve a Wooden Sword into Snakebite, but failed.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createVeilPiercer(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $umbraCheck = $this->rng->rngNextInt(1, 20 + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($umbraCheck < 15)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Decorated Spear, but couldn\'t get an enchantment to stick.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::ARCANA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }
        else // success!
        {
            $veilPiercer = ItemRepository::findOneByName($this->em, 'Veil-piercer');

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Decorated Spear', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% enchanted a Decorated Spear to be ' . $veilPiercer->getNameWithArticle() . '.')
                ->setIcon('items/' . $veilPiercer->getImage())
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Magic_binding,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem($veilPiercer, $pet, $pet->getName() . ' made this by enchanting a Decorated Spear.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::ARCANA ], $activityLog);
        }

        return $activityLog;
    }

    private function createNagatooth(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $craftsCheck = $this->rng->rngNextInt(1, 20 + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($craftsCheck < 15)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried decorate a Decorated Spear _even more_, but couldn\'t get the pattern just right...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Dark Scales', 1);
            $this->houseSimService->getState()->loseItem('Decorated Spear', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% further decorated a Decorated Spear; now it\'s a Nagatooth!')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Nagatooth', $pet, $pet->getName() . ' made this by further decorating a Decorated Spear.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }

        return $activityLog;
    }

    private function createLassoscope(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $craftsCheck = $this->rng->rngNextInt(1, 20 + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal());

        if($craftsCheck < 20)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% wanted to make a Lassoscope, but couldn\'t successfully lasso a Gold Telescope...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Flying Grappling Hook', 1);
            $this->houseSimService->getState()->loseItem('Gold Telescope', 1);
            $pet->increaseEsteem($this->rng->rngNextInt(4, 8));

            $safelyExamineLions = $this->rng->rngNextInt(1, 10) === 1;

            $activityLogMessage = $safelyExamineLions
                ? $pet->getName() . ' made a Lassoscope! (Now they can safely examine lions!)'
                : $pet->getName() . ' made a Lassoscope!'
            ;

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $activityLogMessage)
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $itemComment = $safelyExamineLions
                ? $pet->getName() . ' made this by lassoing a Gold Telescope with a Flying Grappling Hook. (Now they can safely examine lions!)'
                : $pet->getName() . ' made this by lassoing a Gold Telescope with a Flying Grappling Hook.'
            ;

            $this->inventoryService->petCollectsItem('Lassoscope', $pet, $itemComment, $activityLog);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::CRAFTS ], $activityLog);
        }

        return $activityLog;
    }

    private function createCaroteneStick(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $weather = WeatherService::getWeather(new \DateTimeImmutable(), $pet);
        $craftsCheck = $this->rng->rngNextInt(1, 20 + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal());

        if($craftsCheck < 14)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Carrot lure for a Crooked Fishing Rod, but couldn\'t figure out how best to go about it...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Crooked Fishing Rod', 1);
            $this->houseSimService->getState()->loseItem('Carrot', 1);

            if($craftsCheck >= 25 || $weather->isHoliday(HolidayEnum::EASTER))
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% made a Carotene Stick, and even had enough Carrot left over to make a Carrot Key!')
                    ->addInterestingness($weather->isHoliday(HolidayEnum::EASTER) ? PetActivityLogInterestingnessEnum::HOLIDAY_OR_SPECIAL_EVENT : (PetActivityLogInterestingnessEnum::HO_HUM + 25))
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                        PetActivityLogTagEnum::Crafting,
                        PetActivityLogTagEnum::Location_At_Home,
                        'Special Event',
                        'Easter'
                    ]))
                ;

                $this->inventoryService->petCollectsItem('Carrot Key', $pet, $pet->getName() . ' made this.', $activityLog);
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% made a Carrot Lure for a Crooked Fishing Rod; now it\'s a Carotene Stick!')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                        PetActivityLogTagEnum::Crafting,
                        PetActivityLogTagEnum::Location_At_Home,
                    ]))
                ;
            }

            $this->inventoryService->petCollectsItem('Carotene Stick', $pet, $pet->getName() . ' made this.', $activityLog);

            $this->petExperienceService->gainExp($pet, $craftsCheck >= 25 ? 3 : 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }

        return $activityLog;
    }

    private function createPaintedFishingRod(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 180), PetActivityStatEnum::CRAFT, true);
        $this->houseSimService->getState()->loseItem('Crooked Fishing Rod', 1);
        $this->houseSimService->getState()->loseItem('Yellow Dye', 1);
        $this->houseSimService->getState()->loseItem('Green Dye', 1);
        $pet->increaseEsteem(1);
        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Painted Fishing Rod.')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                PetActivityLogTagEnum::Painting,
                PetActivityLogTagEnum::Location_At_Home,
            ]))
        ;
        $this->inventoryService->petCollectsItem('Painted Fishing Rod', $pet, $pet->getName() . ' painted this, using Yellow and Green Dye.', $activityLog);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        return $activityLog;
    }

    private function createPaintedBoomerang(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 180), PetActivityStatEnum::CRAFT, true);
        $this->houseSimService->getState()->loseItem('Plastic Boomerang', 1);
        $this->houseSimService->getState()->loseItem('Quinacridone Magenta Dye', 1);
        $pet->increaseEsteem(3);
        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Painted Boomerang.')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                PetActivityLogTagEnum::Painting,
                PetActivityLogTagEnum::Location_At_Home,
            ]))
        ;
        $this->inventoryService->petCollectsItem('Painted Boomerang', $pet, $pet->getName() . ' painted this, using Quinacridone Magenta Dye.', $activityLog);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        return $activityLog;
    }

    private function createPaintedWhorlStaff(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 180), PetActivityStatEnum::CRAFT, true);
        $this->houseSimService->getState()->loseItem('Whorl Staff', 1);
        $this->houseSimService->getState()->loseItem('Quinacridone Magenta Dye', 1);
        $pet->increaseEsteem(1);
        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Painted Whorl Staff.')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                PetActivityLogTagEnum::Painting,
                PetActivityLogTagEnum::Location_At_Home,
            ]))
        ;
        $this->inventoryService->petCollectsItem('Painted Whorl Staff', $pet, $pet->getName() . ' painted this, using some Quinacridone Magenta Dye.', $activityLog);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        return $activityLog;
    }

    private function createWoherCuanNaniNani(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 180), PetActivityStatEnum::CRAFT, true);
        $this->houseSimService->getState()->loseItem('No Right Turns', 1);
        $this->houseSimService->getState()->loseItem('Green Dye', 1);
        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% painted over a No Right Turns sign to make their own... _unique_ sign.')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                PetActivityLogTagEnum::Painting,
                PetActivityLogTagEnum::Location_At_Home,
            ]))
        ;
        $this->inventoryService->petCollectsItem('Woher Cuán Nani-nani', $pet, $pet->getName() . ' painted this.', $activityLog);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        return $activityLog;
    }

    private function createGoldIdol(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 180), PetActivityStatEnum::CRAFT, true);
        $this->houseSimService->getState()->loseItem('Plastic Idol', 1);
        $this->houseSimService->getState()->loseItem('Yellow Dye', 1);
        $pet->increaseEsteem(1);
        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a "Gold" Idol.')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                PetActivityLogTagEnum::Painting,
                PetActivityLogTagEnum::Location_At_Home,
            ]))
        ;
        $this->inventoryService->petCollectsItem('"Gold" Idol', $pet, $pet->getName() . ' painted this, using Yellow Dye.', $activityLog);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        return $activityLog;
    }

    private function createYellowBucket(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, true);
        $this->houseSimService->getState()->loseItem('Small Plastic Bucket', 1);
        $this->houseSimService->getState()->loseItem('Yellow Dye', 1);
        $pet->increaseEsteem(1);
        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% dunked a Small Plastic Bucket into some Yellow Dye.')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                PetActivityLogTagEnum::Painting,
                PetActivityLogTagEnum::Location_At_Home,
            ]))
        ;
        $this->inventoryService->petCollectsItem('Small, Yellow Plastic Bucket', $pet, $pet->getName() . ' "painted" this, using Yellow Dye.', $activityLog);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        return $activityLog;
    }

    private function createPaintedDumbbell(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 150), PetActivityStatEnum::CRAFT, true);
        $this->houseSimService->getState()->loseItem('Dumbbell', 1);
        $this->houseSimService->getState()->loseItem('Yellow Dye', 1);
        $pet->increaseEsteem(1);

        if($this->rng->rngNextInt(1, 10) === 1)
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% painted emojis on a Dumbbell. (That makes them better, right?)');
        else
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% painted emojis on a Dumbbell.');

        $activityLog->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
            PetActivityLogTagEnum::Painting,
            PetActivityLogTagEnum::Location_At_Home,
        ]));

        $this->inventoryService->petCollectsItem('Painted Dumbbell', $pet, $pet->getName() . ' painted this, using Yellow Dye.', $activityLog);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);

        return $activityLog;
    }

    private function createPaintedCamera(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 150), PetActivityStatEnum::CRAFT, true);
        $this->houseSimService->getState()->loseItem('Digital Camera', 1);
        $this->houseSimService->getState()->loseItem('Yellow Dye', 1);
        $pet->increaseEsteem(1);

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% painted a face on a Digital Camera!');

        $activityLog->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
            PetActivityLogTagEnum::Painting,
            PetActivityLogTagEnum::Location_At_Home,
        ]));

        $this->inventoryService->petCollectsItem('Painted Camera', $pet, $pet->getName() . ' painted this, using Yellow Dye.', $activityLog);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);

        return $activityLog;
    }

    private function repairRustyBlunderbuss(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getSmithingBonus()->getTotal()));

        if($roll >= 18)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(120, 150), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Rusty Blunderbuss', 1);
            $pet->increaseEsteem(3);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% repaired a Rusty Blunderbuss. It\'s WAY less rusty now!')
                ->setIcon('items/tool/gun/blunderbuss')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home, 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Blunderbuss', $pet, $pet->getName() . ' repaired this Rusty Blunderbuss.', $activityLog);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% spent a while trying to repair a Rusty Blunderbuss, but wasn\'t able to make any progress.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home, 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(120, 150), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function repairRustyRapier(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getSmithingBonus()->getTotal()));

        if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(120, 150), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Rusty Rapier', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% repaired a Rusty Rapier. It\'s WAY less rusty now!')
                ->setIcon('items/tool/sword/rapier')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home, 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Rapier', $pet, $pet->getName() . ' repaired this Rapier.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ], $activityLog);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(120, 150), PetActivityStatEnum::CRAFT, false);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% spent a while trying to repair a Rusty Rapier, but wasn\'t able to make any progress.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home, 'Smithing' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ], $activityLog);
        }

        return $activityLog;
    }

    private function repairOldMechanism(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + min($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getScience()->getTotal()));

        if($roll >= 18)
        {
            $loot = $this->rng->rngNextFromArray([
                'Telluriscope', 'Seismustatus', 'Espophone', 'Ferroleuvorter', 'Saccharactum',
            ]);

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(120, 150), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Rusted, Busted Mechanism', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% repaired a Rusted, Busted Mechanism; it\'s now a fully-functional ' . $loot . '!')
                ->setIcon('items/old-mechanism/busted')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                    PetActivityLogTagEnum::Physics,
                ]))
            ;
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' repaired this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% spent a while trying to repair a Rusted, Busted Mechanism, but wasn\'t able to make any progress.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home, 'Physics' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(120, 150), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createLaserGuidedSword(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getScience()->getTotal(), $petWithSkills->getCrafts()->getTotal()));

        if($roll >= 14)
        {
            $this->houseSimService->getState()->loseItem('Iron Sword', 1);
            $this->houseSimService->getState()->loseItem('Glue', 1);
            $this->houseSimService->getState()->loseItem('Laser Pointer', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Laser-guided Sword.')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                    PetActivityLogTagEnum::Electronics,
                ]))
            ;

            if($this->rng->rngNextInt(1, 4) === 1)
                $this->inventoryService->petCollectsItem('Laser-guided Sword', $pet, $pet->getName() . ' created by gluing a Laser Pointer to a sword. Naturally.', $activityLog);
            else
                $this->inventoryService->petCollectsItem('Laser-guided Sword', $pet, $pet->getName() . ' created by gluing a Laser Pointer to a sword.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(120, 150), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% wanted to improve an Iron Sword, but wasn\'t sure how to begin...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home, 'Electronics' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createSunFlag(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        return $this->createFlag($petWithSkills, 'Yellow Dye', 'Sun Flag');
    }

    private function createDragonFlag(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        return $this->createFlag($petWithSkills, 'Green Dye', 'Dragon Flag');
    }

    private function createFlag(ComputedPetSkills $petWithSkills, string $dye, string $making): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $makingItem = ItemRepository::findOneByName($this->em, $making);

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 10)
        {
            $this->houseSimService->getState()->loseItem('White Flag', 1);
            $this->houseSimService->getState()->loseItem($dye, 1);
            $pet->increaseEsteem(3);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% dyed ' . $makingItem->getNameWithArticle() . '.')
                ->setIcon('items/' . $makingItem->getImage())
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Painting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' dyed this flag.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to dye a flag, but couldn\'t come up with a good design.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Painting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 90), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createSunSunFlag(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 12)
        {
            $this->houseSimService->getState()->loseItem('Sun Flag', 1);
            $this->houseSimService->getState()->loseItem('Sunflower Stick', 1);
            $pet->increaseSafety($this->rng->rngNextInt(2, 4));
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% made a Sun-sun Flag!')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Sun-sun Flag', $pet, $pet->getName() . ' made this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% wanted to make a Sun Flag even sunnier, but wasn\'t sure how...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 90), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createSunSunFlagFlagSon(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 20)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Sun-sun Flag', 2);
            $pet->increaseEsteem($this->rng->rngNextInt(2, 4));
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% made a Sun-sun Flag-flag, Son!')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Sun-sun Flag-flag, Son', $pet, $pet->getName() . ' made this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% wanted to combine two Sun-sun Flags, but couldn\'t figure it out...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 90), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createPaleFlail(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Plastic Fishing Rod', 1);
            $this->houseSimService->getState()->loseItem('Moon Pearl', 1);
            $this->houseSimService->getState()->loseItem('Talon', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% made a Pale Flail!')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Pale Flail', $pet, $pet->getName() . ' made this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% wanted to make a flail, but had trouble shaping the Plastic Fishing Rod.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createBindle(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('White Flag', 1);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick');
            $pet->increaseEsteem(3);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% used their Eidetic Memory to perfectly knot _two_ Bindles!')
                ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;
            $this->inventoryService->petCollectsItem('Bindle', $pet, $pet->getName() . ' made this, thanks to their Eidetic Memory.', $activityLog);
            $this->inventoryService->petCollectsItem('Bindle', $pet, $pet->getName() . ' made this, thanks to their Eidetic Memory.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else if($roll >= 10)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('White Flag', 1);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick');
            $pet->increaseEsteem(3);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% made a Bindle by tying a White Flag to a stick.')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Bindle', $pet, $pet->getName() . ' made this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to tie a Bindle, but couldn\'t remember their knots...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 90), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function createBindle2(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('White Flag', 1);
            $this->houseSimService->getState()->loseItem('Crooked Fishing Rod');
            $pet->increaseEsteem(3);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% used their Eidetic Memory to perfectly knot _two_ Bindles!')
                ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;
            $this->inventoryService->petCollectsItem('Bindle', $pet, $pet->getName() . ' made this, thanks to their Eidetic Memory.', $activityLog);
            $this->inventoryService->petCollectsItem('Bindle', $pet, $pet->getName() . ' made this, thanks to their Eidetic Memory.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else if($roll >= 10)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('White Flag', 1);
            $this->houseSimService->getState()->loseItem('Crooked Fishing Rod');
            $pet->increaseEsteem(3);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% made a Bindle by tying a White Flag to a Crooked Fishing Rod.')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Bindle', $pet, $pet->getName() . ' made this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to tie a Bindle, but couldn\'t remember their knots...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 90), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createPeacockPlushy(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $craftsCheck = $this->rng->rngNextInt(1, 20 + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($craftsCheck >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('White Cloth', 1);
            $this->houseSimService->getState()->loseItem('Quinacridone Magenta Dye', 1);

            $stuffing = $this->houseSimService->getState()->loseOneOf($this->rng, [ 'Beans', 'Fluff' ]);

            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Peacock Plushy stuffed with ' . $stuffing . '!')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Peacock Plushy', $pet, $pet->getName() . ' created this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a plushy, but couldn\'t come up with a good pattern...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createGrabbyArm(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $craftsCheck = $this->rng->rngNextInt(1, 20 + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($craftsCheck >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Plastic', 1);
            $this->houseSimService->getState()->loseItem('Iron Bar', 1);

            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Grabby Arm!')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Grabby Arm', $pet, $pet->getName() . ' created this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a grabby arm, but couldn\'t get it to grab...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function makeSpyBalloon(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $craftsCheck = $this->rng->rngNextInt(1, 20 + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($craftsCheck >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Blue Balloon', 1);
            $this->houseSimService->getState()->loseItem('Gold Telescope', 1);

            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% simply tied a Gold Telescope to a Blue Balloon! Easy!')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;
            $this->inventoryService->petCollectsItem('Spy Balloon', $pet, $pet->getName() . ' created this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to tie a Gold Telescope to a Blue Balloon, but their knot kept coming loose...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }
}
