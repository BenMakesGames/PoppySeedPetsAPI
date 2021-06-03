<?php
namespace App\Service\PetActivity\Crafting;

use App\Entity\PetActivityLog;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ArrayFunctions;
use App\Model\ActivityCallback;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\StatusEffectService;

class ProgrammingService
{
    private ResponseService $responseService;
    private InventoryService $inventoryService;
    private PetExperienceService $petExperienceService;
    private IRandom $squirrel3;
    private StatusEffectService $statusEffectService;
    private HouseSimService $houseSimService;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, Squirrel3 $squirrel3,
        PetExperienceService $petExperienceService, StatusEffectService $statusEffectService,
        HouseSimService $houseSimService
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
        $this->squirrel3 = $squirrel3;
        $this->statusEffectService = $statusEffectService;
        $this->houseSimService = $houseSimService;
    }

    /**
     * @return ActivityCallback[]
     */
    public function getCraftingPossibilities(ComputedPetSkills $petWithSkills): array
    {
        $pet = $petWithSkills->getPet();

        $possibilities = [];

        if($this->houseSimService->hasInventory('Tiny Black Hole') && $this->houseSimService->hasInventory('Worms'))
            $possibilities[] = new ActivityCallback($this, 'createWormhole', 10);

        if($this->houseSimService->hasInventory('Photon'))
            $possibilities[] = new ActivityCallback($this, 'createPoisson', 10);

        if($this->houseSimService->hasInventory('Macintosh'))
            $possibilities[] = new ActivityCallback($this, 'hackMacintosh', 10);

        if($this->houseSimService->hasInventory('3D Printer') && $this->houseSimService->hasInventory('Plastic'))
        {
            if($this->houseSimService->hasInventory('Glass') && ($this->houseSimService->hasInventory('Silver Bar') || $this->houseSimService->hasInventory('Gold Bar')))
                $possibilities[] = new ActivityCallback($this, 'createLaserPointer', 10);

            if(($this->houseSimService->hasInventory('Silver Bar') || $this->houseSimService->hasInventory('Iron Bar')) && $this->houseSimService->hasInventory('Magic Smoke'))
                $possibilities[] = new ActivityCallback($this, 'createMetalDetector', 10);
        }

        if($this->houseSimService->hasInventory('Metal Detector (Iron)') || $this->houseSimService->hasInventory('Metal Detector (Silver)') || $this->houseSimService->hasInventory('Metal Detector (Gold)'))
        {
            if($this->houseSimService->hasInventory('Gold Bar') && ($this->houseSimService->hasInventory('Fiberglass') || $this->houseSimService->hasInventory('Fiberglass Flute')))
                $possibilities[] = new ActivityCallback($this, 'createSeashellDetector', 10);
        }

        if($this->houseSimService->hasInventory('Painted Boomerang') && $this->houseSimService->hasInventory('Imaginary Number'))
            $possibilities[] = new ActivityCallback($this, 'createStrangeAttractor', 10);

        if($this->houseSimService->hasInventory('Pointer'))
        {
            $possibilities[] = new ActivityCallback($this, 'createStringFromPointer', 10);

            if($this->houseSimService->hasInventory('Finite State Machine'))
                $possibilities[] = new ActivityCallback($this, 'createRegex', 10);

            if($this->houseSimService->hasInventory('NUL'))
            {
                if($this->houseSimService->hasInventory('Plastic Fishing Rod'))
                    $possibilities[] = new ActivityCallback($this, 'createPhishingRod', 10);

                if($this->houseSimService->hasInventory('Gold Key'))
                    $possibilities[] = new ActivityCallback($this, 'createDiffieHKey', 10);
            }
        }

        if($this->houseSimService->hasInventory('Regex') && $this->houseSimService->hasInventory('Password'))
            $possibilities[] = new ActivityCallback($this, 'createBruteForce', 10);

        if($this->houseSimService->hasInventory('Brute Force') && $this->houseSimService->hasInventory('XOR') && $this->houseSimService->hasInventory('Gold Bar'))
            $possibilities[] = new ActivityCallback($this, 'createL33tH4xx0r', 10);

        if($this->houseSimService->hasInventory('Hash Table'))
        {
            if($this->houseSimService->hasInventory('Laser Pointer') && $this->houseSimService->hasInventory('Bass Guitar'))
                $possibilities[] = new ActivityCallback($this, 'createLaserGuitar', 10);

            if($this->houseSimService->hasInventory('Finite State Machine') && $this->houseSimService->hasInventory('String'))
                $possibilities[] = new ActivityCallback($this, 'createCompiler', 10);

            if($this->houseSimService->hasInventory('Elvish Magnifying Glass'))
                $possibilities[] = new ActivityCallback($this, 'createRijndael', 10);

            if($this->houseSimService->hasInventory('Ruler'))
                $possibilities[] = new ActivityCallback($this, 'createViswanathsConstant', 10);

            if($this->houseSimService->hasInventory('XOR') && $this->houseSimService->hasInventory('Fiberglass Bow'))
                $possibilities[] = new ActivityCallback($this, 'createResonatingBow', 10);
        }

        if($this->houseSimService->hasInventory('Lightning in a Bottle'))
        {
            if($this->houseSimService->hasInventory('Iron Sword'))
                $possibilities[] = new ActivityCallback($this, 'createLightningSword', 10);

            if($this->houseSimService->hasInventory('Gold Bar') && $this->houseSimService->hasInventory('Glass Pendulum'))
                $possibilities[] = new ActivityCallback($this, 'createLivewire', 10);

            if(
                $this->houseSimService->hasInventory('Weird Beetle') &&
                // there's a compiler in the room, or you're holding one:
                (
                    $this->houseSimService->hasInventory('Compiler') ||
                    ($pet->getTool() && $pet->getTool()->getItem()->getName() === 'Compiler')
                )
            )
            {
                $possibilities[] = new ActivityCallback($this, 'createSentientBeetle', 10);
            }

            if($this->houseSimService->hasInventory('Iron Bar') && $this->houseSimService->hasInventory('Plastic') && $this->houseSimService->hasInventory('Gravitational Waves'))
            {
                $possibilities[] = new ActivityCallback($this, 'createGravitonGun', 10);
            }
        }

        if($this->houseSimService->hasInventory('Magic Smoke'))
        {
            if($this->houseSimService->hasInventory('Laser Pointer') && $this->houseSimService->hasInventory('Toy Alien Gun'))
                $possibilities[] = new ActivityCallback($this, 'createAlienGun', 10);

            if($this->houseSimService->hasInventory('Lightning Sword') && $this->houseSimService->hasInventory('Alien Tissue'))
                $possibilities[] = new ActivityCallback($this, 'createDNA', 10);
        }

        if($this->houseSimService->hasInventory('Sylvan Fishing Rod') && $this->houseSimService->hasInventory('Laser Pointer') && $this->houseSimService->hasInventory('Alien Tissue'))
            $possibilities[] = new ActivityCallback($this, 'createAlienFishingRod', 10);

        if($this->houseSimService->hasInventory('Gold Triangle') && $this->houseSimService->hasInventory('Seaweed') && $this->houseSimService->hasInventory('Gravitational Waves'))
            $possibilities[] = new ActivityCallback($this, 'createBermudaTriangle', 10);

        return $possibilities;
    }

    /**
     * @param ActivityCallback[] $possibilities
     */
    public function adventure(ComputedPetSkills $petWithSkills, array $possibilities): PetActivityLog
    {
        if(count($possibilities) === 0)
            throw new \InvalidArgumentException('possibilities must contain at least one item.');

        $pet = $petWithSkills->getPet();

        /** @var ActivityCallback $method */
        $method = $this->squirrel3->rngNextFromArray($possibilities);

        $activityLog = null;
        $changes = new PetChanges($pet);

        /** @var PetActivityLog $activityLog */
        $activityLog = ($method->callable)($petWithSkills);

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));

        return $activityLog;
    }

    private function createPoisson(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getScience()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);

            $this->houseSimService->getState()->loseItem('Photon', 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            $pet->increasePoison(2);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to measure a Photon to confirm the Poisson Distribution, but a miscalculation produced a distribution of poison, instead! x_x', '');
        }
        else if($roll > 12)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, true);

            $this->houseSimService->getState()->loseItem('Photon', 1);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% measured a Photon, confirming the Poisson Distribution!', 'items/space/photon')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 12)
            ;
            $this->inventoryService->petCollectsItem('Fish', $pet, $pet->getName() . ' measured a Photon, confirming the _Poisson_ Distribution!', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to measure a Photon, but it kept zipping away before they could do so! >:(', 'icons/activity-logs/confused');
        }

    }

    private function createLaserPointer(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getScience()->getTotal(), $petWithSkills->getCrafts()->getTotal()));

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PLASTIC_PRINT, false);

            $this->houseSimService->getState()->loseItem('Plastic', 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Laser Pointer, but the base plate of the 3D Printer moved, jacking up the Plastic :(', '');
        }
        else if($roll > 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PLASTIC_PRINT, true);

            $this->houseSimService->getState()->loseItem('Plastic', 1);
            $this->houseSimService->getState()->loseItem('Glass', 1);

            $this->houseSimService->getState()->loseOneOf([ 'Silver Bar', 'Gold Bar' ]);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% 3D printed & wired a Laser Pointer.', 'items/resource/string')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem('Laser Pointer', $pet, $pet->getName() . ' 3D printed & wired this up.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PLASTIC_PRINT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Laser Pointer, but couldn\'t get the wiring straight.', 'icons/activity-logs/confused');
        }
    }

    private function createMetalDetector(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getScience()->getTotal(), $petWithSkills->getCrafts()->getTotal()));

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PLASTIC_PRINT, false);

            $this->houseSimService->getState()->loseItem('Plastic', 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Metal Detector, but the base plate of the 3D Printer moved, jacking up the Plastic :(', '');
        }
        else if($roll > 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);

            $this->houseSimService->getState()->loseItem('Plastic', 1);
            $this->houseSimService->getState()->loseItem('Magic Smoke', 1);

            $this->houseSimService->getState()->loseOneOf([ 'Silver Bar', 'Iron Bar' ]);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% 3D printed & wired up a Metal Detector.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;

            $metalDetector = $this->squirrel3->rngNextFromArray([
                'Metal Detector (Iron)',
                'Metal Detector (Silver)',
                'Metal Detector (Gold)'
            ]);

            $this->inventoryService->petCollectsItem($metalDetector, $pet, $pet->getName() . ' 3D printed & wired this up.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Metal Detector, but couldn\'t get the wiring straight.', 'icons/activity-logs/confused');
        }
    }

    private function createSeashellDetector(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getScience()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);

            $fiberglassItemBroken = $this->houseSimService->getState()->loseOneOf([ 'Fiberglass', 'Fiberglass Flute' ]);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to alter a Metal Detector to detect Secret Seashells, but accidentally shattered the ' . $fiberglassItemBroken . ' :(', '');

            return $activityLog;
        }
        else if($roll >= 18)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, false);

            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseOneOf([ 'Fiberglass', 'Fiberglass Flute' ]);
            $this->houseSimService->getState()->loseOneOf([ 'Metal Detector (Iron)', 'Metal Detector (Silver)', 'Metal Detector (Gold)' ]);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::SCIENCE ]);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% modified an ordinary Metal Detector, turning it into a Secret Seashell Detector!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
            ;
            $this->inventoryService->petCollectsItem('Secret Seashell Detector', $pet, $pet->getName() . ' made this out of an ordinary Metal Detector.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to alter a Metal Detector to detect Secret Seashells, but kept messing up the programming.', 'icons/activity-logs/confused');
        }
    }

    private function createStringFromPointer(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->houseSimService->getState()->loseItem('Pointer', 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to dereference a String from a Pointer, but encountered a null exception :(', 'icons/activity-logs/null');
            $this->inventoryService->petCollectsItem('NUL', $pet, $pet->getName() . ' encountered a null exception when trying to dereference a pointer.', $activityLog);
            return $activityLog;
        }
        else if($roll >= 10)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Pointer', 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% dereferenced a String from a Pointer.', 'items/resource/string')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
            ;
            $this->inventoryService->petCollectsItem('String', $pet, $pet->getName() . ' dereferenced this from a Pointer.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to dereference a Pointer, but couldn\'t figure out all the syntax errors.', 'icons/activity-logs/confused');
        }
    }

    private function createRegex(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);

            if($this->squirrel3->rngNextInt(1, 2) === 1)
            {
                $this->houseSimService->getState()->loseItem('Pointer', 1);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to create a Regex, but mis-scoped a Pointer :(', 'icons/activity-logs/null');
            }
            else
            {
                $this->houseSimService->getState()->loseItem('Finite State Machine', 1);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to create a Regex, but lost a Finite State Machine to a stack overflow :(', 'icons/activity-logs/null');
            }
        }
        else if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Pointer', 1);
            $this->houseSimService->getState()->loseItem('Finite State Machine', 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% upgraded a Finite State Machine into a Regex.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
            ;
            $this->inventoryService->petCollectsItem('Regex', $pet, $pet->getName() . ' built this from a Finite State Machine.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to implement a Regex, but it was taking forever. ' . $pet->getName() . ' saved and quit for now.', 'icons/activity-logs/confused');
        }
    }

    private function createWormhole(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);

            if($this->squirrel3->rngNextBool() || $pet->getFood() > 4)
            {
                $this->houseSimService->getState()->loseItem('Tiny Black Hole', 1);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to create a Wormhole, but accidentally reduced the Tiny Black Hole to Hawking radiation! ', '');
            }
            else
            {
                $this->houseSimService->getState()->loseItem('Worms', 1);
                $pet->increaseFood($this->squirrel3->rngNextInt(3, 6));
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to create a Wormhole, but absentmindedly ate the Worms, instead :(', '');
            }
        }
        else if($roll >= 17)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Tiny Black Hole', 1);
            $this->houseSimService->getState()->loseItem('Worms', 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem($this->squirrel3->rngNextInt(2, 4));
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Wormhole by inverting a Tiny Black Hole... and adding Worms?', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 17)
            ;
            $this->inventoryService->petCollectsItem('Wormhole', $pet, $pet->getName() . ' created this from a Tiny Black Hole, and also Worms.' . ($this->squirrel3->rngNextInt(1, 10) === 1 ? ' (SCIENCE.)' : ''), $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Wormhole, but the Worms kept crawling away, and %pet:' . $pet->getId() . '.name% wasted all their time gathering them back up again...', 'icons/activity-logs/confused');
        }
    }

    private function createCompiler(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);

            if($this->squirrel3->rngNextInt(1, 2) === 1)
            {
                $this->houseSimService->getState()->loseItem('String', 1);
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to bootstrap a Compiler, but accidentally de-allocated a String, leaving a useless Pointer behind :(', 'icons/activity-logs/null');
                $this->inventoryService->petCollectsItem('Pointer', $pet, $pet->getName() . ' accidentally de-allocated a String; all that remains is this Pointer.', $activityLog);
                return $activityLog;
            }
            else
            {
                $this->houseSimService->getState()->loseItem('Hash Table', 1);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to bootstrap a Compiler, but accidentally caused a runaway hash collision, and lost their Hash Table :(', 'icons/activity-logs/null');
            }
        }
        else if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Hash Table', 1);
            $this->houseSimService->getState()->loseItem('Finite State Machine', 1);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% bootstrapped a Compiler.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;
            $this->inventoryService->petCollectsItem('Compiler', $pet, $pet->getName() . ' bootstrapped this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to bootstrap a Compiler, but only got so far.', 'icons/activity-logs/confused');
        }
    }

    private function createLaserGuitar(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + min($petWithSkills->getPerception()->getTotal(), $petWithSkills->getMusic()->getTotal()) + $petWithSkills->getScience()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);

            $this->houseSimService->getState()->loseItem('Hash Table', 1);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to create a Laser Guitar, but accidentally caused a runaway hash collision, and lost their Hash Table :(', 'icons/activity-logs/null');
        }
        else if($roll >= 18)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Hash Table', 1);
            $this->houseSimService->getState()->loseItem('Laser Pointer', 1);
            $this->houseSimService->getState()->loseItem('Bass Guitar', 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::SCIENCE, PetSkillEnum::MUSIC ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Laser Guitar!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
            ;
            $this->inventoryService->petCollectsItem('Laser Guitar', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to create Laser Guitar, but only got so far.', 'icons/activity-logs/confused');
        }
    }

    private function createRijndael(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal());

        if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Hash Table', 1);
            $this->houseSimService->getState()->loseItem('Elvish Magnifying Glass', 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% implemented Rijndael.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;
            $this->inventoryService->petCollectsItem('Rijndael', $pet, $pet->getName() . ' implemented this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to implement Rijndael, but had trouble finding good documentation. ' . $pet->getName() . ' saved and quit for now.', 'icons/activity-logs/confused');
        }
    }

    private function createViswanathsConstant(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal());

        if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Hash Table', 1);
            $this->houseSimService->getState()->loseItem('Ruler', 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% calculated Viswanath\'s Constant.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;
            $this->inventoryService->petCollectsItem('Viswanath\'s Constant', $pet, $pet->getName() . ' calculated this.', $activityLog);
            return $activityLog;
        }
        else
        {
            if($this->squirrel3->rngNextInt(1, 3) === 1)
                return $this->fightInfinityImp($petWithSkills, 'computing Viswanath\'s Constant');
            else
            {
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, false);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to calculate Viswanath\'s Constant, but couldn\'t figure out any of the maths; not even a single one!', 'icons/activity-logs/confused');
            }
        }
    }

    private function createResonatingBow(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getMusic()->getTotal());

        if($roll >= 18)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Hash Table', 1);
            $this->houseSimService->getState()->loseItem('XOR', 1);
            $this->houseSimService->getState()->loseItem('Fiberglass Bow', 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::SCIENCE ]);

            if($pet->hasMerit(MeritEnum::SOOTHING_VOICE))
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% coded up a Resonating Bow. They sang a soothing song as they plucked the last string, producing a Music Note.', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ;

                $this->inventoryService->petCollectsItem('Music Note', $pet, $pet->getName() . ' produced this while coding up a Resonating Bow.', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% coded up a Resonating Bow.', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                ;
            }

            $this->inventoryService->petCollectsItem('Resonating Bow', $pet, $pet->getName() . ' coded this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to code up a Resonating Bow, but couldn\'t get the harmonics logic right...', 'icons/activity-logs/confused');
        }
    }

    private function createStrangeAttractor(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);

            $this->houseSimService->getState()->loseItem('Imaginary Number', 1);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to create a Strange Attractor, but accidentally squared the Imaginary Number, making it real :(', '');
        }
        else if($roll >= 17)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Imaginary Number', 1);
            $this->houseSimService->getState()->loseItem('Painted Boomerang', 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% computed a Strange Attractor.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 17)
            ;
            $this->inventoryService->petCollectsItem('Strange Attractor', $pet, $pet->getName() . ' computed this from a Painted Boomerang and Imaginary Number.', $activityLog);
            return $activityLog;
        }
        else
        {
            if($this->squirrel3->rngNextInt(1, 3) === 1)
                return $this->fightInfinityImp($petWithSkills, 'computing a Strange Attractor');
            else
            {
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% thought about computing a Strange Attractor, but kept getting infinities.', 'icons/activity-logs/confused');
            }
        }
    }

    private function fightInfinityImp(ComputedPetSkills $petWithSkills, string $actionInterrupted): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $scienceRoll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal());
        $brawlRoll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl()->getTotal());

        $loot = $this->squirrel3->rngNextFromArray([
            'Quintessence',
            'Pointer',
        ]);

        if($scienceRoll >= $brawlRoll)
        {
            if($scienceRoll >= 20)
            {
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, false);
                $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::SCIENCE ]);
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started ' . $actionInterrupted . ', but an Infinity Imp popped up, and started to attack! During the fight, ' . $pet->getName() . ' exploited a divergence in the imp\'s construction, and unraveled it, receiving ' . $loot . '!', 'icons/activity-logs/confused');
                $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' received this by unraveling an Infinity Imp.', $activityLog);
                return $activityLog;
            }
        }
        else
        {
            if($brawlRoll >= 20)
            {
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, false);
                $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::SCIENCE ]);
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started ' . $actionInterrupted . ', but an Infinity Imp popped up, and started to attack! ' . $pet->getName() . ' slew the creature outright, and claimed its ' . $loot . '!', 'icons/activity-logs/confused');
                $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' received this by slaying an Infinity Imp.', $activityLog);
                return $activityLog;
            }
        }

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, false);
        $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
        return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started ' . $actionInterrupted . ', but an Infinity Imp popped up, and started to attack! ' . $pet->getName() . ' ran away until the imp finally gave up and returned to the strange dimension from whence it came.', 'icons/activity-logs/confused');
    }

    private function createBruteForce(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);

            $this->houseSimService->getState()->loseItem('Password', 1);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to create Brute Force, but got the Password wrong too many times :(', '');
        }
        else if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Regex', 1);
            $this->houseSimService->getState()->loseItem('Password', 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created Brute Force.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
            ;
            $this->inventoryService->petCollectsItem('Brute Force', $pet, $pet->getName() . ' upgraded a Regex into this, with the help of a Password.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to become a l33t h4xx0r, but didn\'t have the right stuff. (Figuratively speaking.)', 'icons/activity-logs/confused');
        }
    }

    private function createL33tH4xx0r(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal());

        if($roll <= 2)
        {
            if($pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to become a l33t h4xx0r, but didn\'t have the right stuff. (At least they _remembered_ the difference between an XOR and an OR!)', 'icons/activity-logs/confused');
            }
            else
            {
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
                $this->petExperienceService->gainExp($pet, 1, [PetSkillEnum::SCIENCE]);

                $this->houseSimService->getState()->loseItem('XOR', 1);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to become a l33t h4xx0r, but confused an XOR for an OR; the XOR was lost forever :(', 'icons/activity-logs/null');
            }
        }
        else if($roll >= 17)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Brute Force', 1);
            $this->houseSimService->getState()->loseItem('XOR', 1);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% became a l33t h4xx0r.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 17)
            ;
            $this->inventoryService->petCollectsItem('l33t h4xx0r', $pet, $pet->getName() . ' made this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to become a l33t h4xx0r, but didn\'t have the right stuff. (Figuratively speaking.)', 'icons/activity-logs/confused');
        }
    }

    private function createPhishingRod(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);

            $this->houseSimService->getState()->loseItem('Pointer', 1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to create a Phishing Rod, but lost their Pointer to garbage collection :(', 'icons/activity-logs/null');
            return $activityLog;
        }
        else if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Plastic Fishing Rod', 1);
            $this->houseSimService->getState()->loseItem('Pointer', 1);
            $this->houseSimService->getState()->loseItem('NUL', 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Phishing Rod.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;
            $this->inventoryService->petCollectsItem('Phishing Rod', $pet, $pet->getName() . ' made this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% considered making a Phishing Rod, but ended up boondoggling.', 'icons/activity-logs/confused');
        }
    }

    private function createDiffieHKey(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);

            $this->houseSimService->getState()->loseItem('NUL', 1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to create a Diffie-H Key, but their NUL got reallocated :(', 'icons/activity-logs/null');
            return $activityLog;
        }
        else if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Gold Key', 1);
            $this->houseSimService->getState()->loseItem('Pointer', 1);
            $this->houseSimService->getState()->loseItem('NUL', 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Diffie-H Key.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
            ;
            $this->inventoryService->petCollectsItem('Diffie-H Key', $pet, $pet->getName() . ' made this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Diffie-H Key, but some passing qubits messed it all up.', 'icons/activity-logs/confused');
        }
    }

    private function createLightningSword(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);

            $this->houseSimService->getState()->loseItem('Lightning in a Bottle', 1);
            $pet->increaseSafety(-$this->squirrel3->rngNextInt(4, 8));
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to electrify an Iron Sword, but the lightning escaped, and ' . $pet->getName() . ' got shocked :(', '');
            return $activityLog;
        }
        else if($roll >= 18)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Lightning in a Bottle', 1);
            $this->houseSimService->getState()->loseItem('Iron Sword', 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% electrified an Iron Sword; now it\'s a _Lightning_ Sword!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
            ;
            $description = $this->squirrel3->rngNextInt(1, 10) === 1 ? ($pet->getName() . ' scienced this. With SCIENCE.') : ($pet->getName() . ' scienced this.');
            $this->inventoryService->petCollectsItem('Lightning Sword', $pet, $description, $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to electrify an Iron Sword, but the lightning started arcing and sparking rather violently, so ' . $pet->getName() . ' decided to wait a bit...', 'icons/activity-logs/confused');
        }
    }

    private function createLivewire(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);

            $this->houseSimService->getState()->loseItem('Lightning in a Bottle', 1);
            $pet->increaseSafety(-$this->squirrel3->rngNextInt(4, 8));
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to electrify a Glass Pendulum, but the lightning escaped, and ' . $pet->getName() . ' got shocked :(', '');
            return $activityLog;
        }
        else if($roll >= 18)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Lightning in a Bottle', 1);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Glass Pendulum', 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% electrified a Glass Pendulum laced with gold, creating a Livewire!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
            ;
            $description = $this->squirrel3->rngNextInt(1, 10) === 1 ? ($pet->getName() . ' scienced this. With SCIENCE.') : ($pet->getName() . ' scienced this.');
            $this->inventoryService->petCollectsItem('Livewire', $pet, $description, $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to electrify a Glass Pendulum, but the lightning started arcing and sparking rather violently, so ' . $pet->getName() . ' decided to wait a bit...', 'icons/activity-logs/confused');
        }
    }

    private function createSentientBeetle(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);

            $this->houseSimService->getState()->loseItem('Weird Beetle', 1);
            $pet->increaseEsteem(-$this->squirrel3->rngNextInt(4, 8));
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to upload an AI into a Weird Beetle\'s brain, but, uh... the beetle... did not survive...', '');
            return $activityLog;
        }
        else if($roll >= 24)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Lightning in a Bottle', 1);
            $this->houseSimService->getState()->loseItem('Weird Beetle', 1);
            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% uploaded an AI into a Weird Beetle\'s brain, granting it sentience!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 24)
            ;
            $this->inventoryService->petCollectsItem('Sentient Beetle', $pet, $pet->getName() . ' gave this beetle sentience by uploading an AI into its brain.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to program an AI, but couldn\'t get anywhere...', 'icons/activity-logs/confused');
        }
    }

    private function createAlienGun(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + ceil(($petWithSkills->getScience()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getUmbra()->getTotal()) / 2));

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);

            $this->houseSimService->getState()->loseItem('Magic Smoke', 1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to engineer an Alien Gun, but mishandled the Magic Smoke, which dissipated completely :(', '');
            return $activityLog;
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Magic Smoke', 1);
            $this->houseSimService->getState()->loseItem('Laser Pointer', 1);
            $this->houseSimService->getState()->loseItem('Toy Alien Gun', 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% rigged up a Toy Alien Gun to actually shoot lasers!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem('Alien Gun', $pet, $pet->getName() . ' engineered this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% had an idea for how to make an Alien Gun using a Laser Pointer, but couldn\'t quite figure out the wiring...', 'icons/activity-logs/confused');
        }
    }

    private function createAlienFishingRod(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + ceil(($petWithSkills->getScience()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getUmbra()->getTotal()) / 2));

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);

            $this->houseSimService->getState()->loseItem('Alien Tissue', 1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to integrate Alien Tissue with a Sylvan Fishing Rod, but the Alien Tissue\'s cells were destroyed by the leaf\'s :(', '');
            return $activityLog;
        }
        else if($roll >= 19)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Laser Pointer', 1);
            $this->houseSimService->getState()->loseItem('Alien Tissue', 1);
            $this->houseSimService->getState()->loseItem('Sylvan Fishing Rod', 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(5);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% integrated Alien Tissue into a Sylvan Fishing Rod using a Laser Pointer! (As you do!)', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 19)
            ;
            $this->inventoryService->petCollectsItem('Eridanus', $pet, $pet->getName() . ' scienced this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to integrate Alien Tissue with a Sylvan Fishing Rod, but the different forms of life kept rejecting one another...', 'icons/activity-logs/confused');
        }
    }

    private function createBermudaTriangle(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getScience()->getTotal());

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);

            $effect = $this->squirrel3->rngNextInt(1, 3);

            if($effect === 1)
            {
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::UMBRA ]);

                $pet->increaseSafety(-6);
                $this->statusEffectService->applyStatusEffect($pet, StatusEffectEnum::HEX_HEXED, 6 * 60);
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Bermuda Triangle, but accidentally hexed themselves, instead! :(', '');
            }
            else if($effect === 2)
            {
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);

                $this->houseSimService->getState()->loseItem('Gravitational Waves', 1);
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Bermuda Triangle, but the Gravitational Waves dissipated :(', '');
            }
            else
            {
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);

                $this->houseSimService->getState()->loseItem('Seaweed', 1);
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Bermuda Triangle, but the Seaweed was shredded by the intense gravitational forces :(', '');
            }

            return $activityLog;
        }
        else if($roll >= 19)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Gold Triangle', 1);
            $this->houseSimService->getState()->loseItem('Seaweed', 1);
            $this->houseSimService->getState()->loseItem('Gravitational Waves', 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(5);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Bermuda Triangle out of a Gold Triangle!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 19)
            ;
            $this->inventoryService->petCollectsItem('Bermuda Triangle', $pet, $pet->getName() . ' scienced this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Bermuda Triangle, but the Gold Triangle kept getting bent by the gravitational forces, and ' . $pet->getName() . ' spent all their time bending it back into shape!', 'icons/activity-logs/confused');
        }
    }

    private function createDNA(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getScience()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);

            $this->houseSimService->getState()->loseItem('Magic Smoke', 1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to improve a Lightning Sword, but mishandled the Magic Smoke, which dissipated completely :(', '');
            return $activityLog;
        }
        else if($roll >= 20)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Magic Smoke', 1);
            $this->houseSimService->getState()->loseItem('Lightning Sword', 1);
            $this->houseSimService->getState()->loseItem('Alien Tissue', 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% added alien tech to a Lightning Sword, creating DNA!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
            ;
            $this->inventoryService->petCollectsItem('DNA', $pet, $pet->getName() . ' engineered this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to enhance a Lightning Sword with alien tech, but kept running into compatibility issues...', 'icons/activity-logs/confused');
        }
    }

    private function hackMacintosh(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Macintosh', 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to hack a Macintosh, but ended up bricking it :(', '');
        }
        else if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Macintosh', 1);

            $loot = [
                $this->squirrel3->rngNextFromArray([ 'Magic Smoke', 'Quintessence', 'Hash Table' ]),
                $this->squirrel3->rngNextFromArray([ 'Pointer', 'NUL', 'String' ])
            ];

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% hacked a Macintosh, and got its ' . ArrayFunctions::list_nice($loot) . '.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;

            foreach($loot as $item)
                $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' got this by hacking a Macintosh.', $activityLog);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to hack a Macintosh, but couldn\'t get anywhere.', 'icons/activity-logs/confused');
        }
    }

    private function createGravitonGun(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll === 1)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);

            $this->houseSimService->getState()->loseItem('Lightning in a Bottle', 1);
            $pet->increaseSafety(-$this->squirrel3->rngNextInt(4, 8));
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to engineer a Graviton Gun, but the lightning escaped, and ' . $pet->getName() . ' got shocked :(', '');
            return $activityLog;
        }
        else if($roll === 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);

            $this->houseSimService->getState()->loseItem('Gravitational Waves', 1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to engineer a Graviton Gun, but the Gravitational Waves became too weak to detect :(', '');
            return $activityLog;
        }
        else if($roll >= 24)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Lightning in a Bottle', 1);
            $this->houseSimService->getState()->loseItem('Plastic', 1);
            $this->houseSimService->getState()->loseItem('Iron Bar', 1);
            $this->houseSimService->getState()->loseItem('Gravitational Waves', 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(4);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Graviton Gun! Neat!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 24)
            ;
            $this->inventoryService->petCollectsItem('Graviton Gun', $pet, $pet->getName() . ' engineered this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% had an idea for how to make a Graviton Gun, but couldn\'t quite figure out the physics...', 'icons/activity-logs/confused');
        }
    }
}
