<?php
namespace App\Service\PetActivity;

use App\Entity\PetActivityLog;
use App\Enum\EnumInvalidValueException;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Model\ActivityCallback;
use App\Model\ComputedPetSkills;
use App\Model\ItemQuantity;
use App\Model\PetChanges;
use App\Repository\ItemRepository;
use App\Service\CalendarService;
use App\Service\InventoryService;
use App\Service\PetActivity\Crafting\EventLanternService;
use App\Service\PetActivity\Crafting\Helpers\TwuWuvCraftingService;
use App\Service\PetActivity\Crafting\PlasticPrinterService;
use App\Service\PetActivity\Crafting\SmithingService;
use App\Service\PetActivity\Crafting\MagicBindingService;
use App\Service\PetActivity\Crafting\Helpers\StickCraftingService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;

class CraftingService
{
    private $responseService;
    private $inventoryService;
    private $petExperienceService;
    private $smithingService;
    private $magicBindingService;
    private $plasticPrinterService;
    private $stickCraftingService;
    private $itemRepository;
    private $eventLanternService;
    private $twuWuvCraftingService;
    private $squirrel3;
    private $calendarService;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, SmithingService $smithingService,
        MagicBindingService $magicBindingService, PlasticPrinterService $plasticPrinterService,
        PetExperienceService $petExperienceService, StickCraftingService $stickCraftingService,
        ItemRepository $itemRepository, EventLanternService $eventLanternService, TwuWuvCraftingService $twuWuvCraftingService,
        Squirrel3 $squirrel3, CalendarService $calendarService
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->smithingService = $smithingService;
        $this->magicBindingService = $magicBindingService;
        $this->plasticPrinterService = $plasticPrinterService;
        $this->petExperienceService = $petExperienceService;
        $this->stickCraftingService = $stickCraftingService;
        $this->itemRepository = $itemRepository;
        $this->eventLanternService = $eventLanternService;
        $this->twuWuvCraftingService = $twuWuvCraftingService;
        $this->squirrel3 = $squirrel3;
        $this->calendarService = $calendarService;
    }

    /**
     * @param ItemQuantity[] $quantities
     * @return ActivityCallback[]
     */
    public function getCraftingPossibilities(ComputedPetSkills $petWithSkills, array $quantities): array
    {
        $possibilities = [];

        if(array_key_exists('Twu Wuv', $quantities) && array_key_exists('Red Balloon', $quantities))
        {
            $possibilities[] = new ActivityCallback($this->twuWuvCraftingService, 'createWedBawwoon', 15);
        }

        if(array_key_exists('Chocolate Bar', $quantities))
        {
            $weight = $this->calendarService->isValentinesOrAdjacent() ? 80 : 8;

            $possibilities[] = new ActivityCallback($this, 'makeChocolateTool', $weight);
        }

        if(array_key_exists('Fluff', $quantities) || array_key_exists('Cobweb', $quantities))
        {
            $possibilities[] = new ActivityCallback($this, 'spinFluffOrCobweb', 10);
        }

        if(array_key_exists('White Cloth', $quantities))
        {
            if(array_key_exists('Quinacridone Magenta Dye', $quantities))
            {
                if(array_key_exists('Fluff', $quantities) || array_key_exists('Beans', $quantities))
                    $possibilities[] = new ActivityCallback($this, 'createPeacockPlushy', 10);
            }

            if(array_key_exists('String', $quantities) && array_key_exists('Ruby Feather', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createFeatheredHat', 10);

            if(array_key_exists('Glass Pendulum', $quantities) && array_key_exists('Flute', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createDecoratedFlute', 10);

            if(array_key_exists('Stereotypical Bone', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createTorchFromBone', 5);
        }

        if(array_key_exists('Gold Telescope', $quantities) && array_key_exists('Flying Grappling Hook', $quantities))
            $possibilities[] = new ActivityCallback($this, 'createLassoscope', 10);

        if(array_key_exists('Tea Leaves', $quantities))
        {
            $possibilities[] = new ActivityCallback($this, 'createYellowDyeFromTeaLeaves', 10);

            if(array_key_exists('Trowel', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createTeaTrowel', 10);
        }

        if(array_key_exists('Scales', $quantities))
        {
            $possibilities[] = new ActivityCallback($this, 'extractFromScales', 10);

            if(array_key_exists('Talon', $quantities) && array_key_exists('Wooden Sword', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createSnakebite', 10);
        }

        if(array_key_exists('Crooked Stick', $quantities))
        {
            if(array_key_exists('Sunflower', $quantities))
                $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createSunflowerStick', 10);

            if(array_key_exists('Glue', $quantities) || array_key_exists('String', $quantities))
            {
                $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createWoodenSword', 10);
            }

            if(array_key_exists('String', $quantities))
            {
                $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createCrookedFishingRod', 10);

                if(array_key_exists('Talon', $quantities))
                    $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createHuntingSpear', 10);

                if(array_key_exists('Hunting Spear', $quantities))
                    $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createVeryLongSpear', 10);

                if(array_key_exists('Overly-long Spear', $quantities))
                    $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createRidiculouslyLongSpear', 10);

                if(array_key_exists('Corn', $quantities) && array_key_exists('Rice', $quantities))
                    $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createHarvestStaff', 10);
            }

            if(array_key_exists('Cobweb', $quantities))
                $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createBugCatchersNet', 10);

            if(array_key_exists('Glue', $quantities) && (array_key_exists('Wheat', $quantities) || array_key_exists('Rice', $quantities)))
                $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createStrawBroom', 10);

            if(array_key_exists('White Cloth', $quantities))
                $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createTorchOrFlag', 10);

            if(array_key_exists('Toadstool', $quantities) && array_key_exists('Quintessence', $quantities))
                $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createChampignon', 10);

            if(array_key_exists('Glass', $quantities))
                $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createRusticMagnifyingGlass', 10);

            if(array_key_exists('Sweet Beet', $quantities) && array_key_exists('Glue', $quantities))
                $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createSweetBeat', 10);
        }

        if(array_key_exists('Glue', $quantities))
        {
            if(array_key_exists('White Cloth', $quantities))
            {
                if(array_key_exists('Fiberglass Flute', $quantities))
                    $possibilities[] = new ActivityCallback($this, 'createFiberglassPanFlute', 11);

                $possibilities[] = new ActivityCallback($this, 'createFabricMache', 7);
            }

            if(array_key_exists('Gold Triangle', $quantities) && $quantities['Gold Triangle']->quantity >= 3)
                $possibilities[] = new ActivityCallback($this, 'createGoldTrifecta', 10);

            if(array_key_exists('Ruler', $quantities) && $quantities['Ruler']->quantity >= 2)
                $possibilities[] = new ActivityCallback($this, 'createLSquare', 10);

            if(array_key_exists('Cooking Buddy', $quantities) && array_key_exists('Antenna', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createAlienCookingBuddy', 10);

            if(array_key_exists('Iron Sword', $quantities) && array_key_exists('Laser Pointer', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createLaserGuidedSword', 10);
        }

        if(array_key_exists('Antenna', $quantities) && array_key_exists('Cobweb', $quantities) && array_key_exists('Fiberglass Bow', $quantities))
            $possibilities[] = new ActivityCallback($this, 'createBugBow', 10);

        if(array_key_exists('String', $quantities))
        {
            if(array_key_exists('Naner', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createBownaner', 10);

            if(array_key_exists('Glass', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createGlassPendulum', 10);

            if(array_key_exists('Paper', $quantities) && array_key_exists('Silver Key', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createBenjaminFranklin', 10);

            if(array_key_exists('Really Big Leaf', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createLeafSpear', 10);

            if(array_key_exists('L-Square', $quantities) && array_key_exists('Green Dye', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createRibbelysComposite', 10);
        }

        if(array_key_exists('Bownaner', $quantities) && array_key_exists('Carrot', $quantities))
            $possibilities[] = new ActivityCallback($this, 'createEatYourFruitsAndVeggies', 10);

        if(array_key_exists('Feathers', $quantities))
        {
            if(array_key_exists('Hunting Spear', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createDecoratedSpear', 10);

            if(array_key_exists('Yellow Dye', $quantities))
            {
                if(array_key_exists('Fiberglass Pan Flute', $quantities))
                    $possibilities[] = new ActivityCallback($this, 'createOrnatePanFlute', 10);

                if(array_key_exists('Tea Trowel', $quantities))
                    $possibilities[] = new ActivityCallback($this, 'createOwlTrowel', 10);
            }
        }

        if(array_key_exists('White Feathers', $quantities) && array_key_exists('Leaf Spear', $quantities))
        {
            $possibilities[] = new ActivityCallback($this, 'createFishingRecorder', 10);
        }

        if(array_key_exists('Decorated Spear', $quantities))
        {
            if(array_key_exists('Dark Scales', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createNagatooth', 10);

            if(array_key_exists('Quintessence', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createVeilPiercer', 10);
        }

        if(array_key_exists('Crooked Fishing Rod', $quantities) && array_key_exists('Yellow Dye', $quantities) && array_key_exists('Green Dye', $quantities))
            $possibilities[] = new ActivityCallback($this, 'createPaintedFishingRod', 10);

        if(array_key_exists('Plastic Boomerang', $quantities) && array_key_exists('Quinacridone Magenta Dye', $quantities))
            $possibilities[] = new ActivityCallback($this, 'createPaintedBoomerang', 10);

        if(array_key_exists('Yellow Dye', $quantities))
        {
            if(array_key_exists('Plastic Idol', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createGoldIdol', 10);
            else
            {
                if(array_key_exists('Small Plastic Bucket', $quantities))
                    $possibilities[] = new ActivityCallback($this, 'createYellowBucket', 10);

                if(array_key_exists('Dumbbell', $quantities))
                    $possibilities[] = new ActivityCallback($this, 'createPaintedDumbbell', 10);
            }
        }

        if(array_key_exists('Fiberglass', $quantities))
            $possibilities[] = new ActivityCallback($this, 'createSimpleFiberglassItem', 10);

        if(array_key_exists('Scythe', $quantities))
        {
            if($quantities['Scythe']->quantity >= 2)
                $possibilities[] = new ActivityCallback($this, 'createDoubleScythe', 10);

            if(array_key_exists('Garden Shovel', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createFarmersMultiTool', 10);
        }

        if(array_key_exists('White Flag', $quantities))
        {
            if(array_key_exists('Yellow Dye', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createSunFlag', 10);

            if(array_key_exists('Green Dye', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createDragonFlag', 10);

            if(array_key_exists('String', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createBindle', 10);
        }

        if(array_key_exists('Sun Flag', $quantities) && array_key_exists('Sunflower Stick', $quantities))
            $possibilities[] = new ActivityCallback($this, 'createSunSunFlag', 10);

        $possibilities = array_merge($possibilities, $this->smithingService->getCraftingPossibilities($petWithSkills, $quantities));

        if(array_key_exists('Plastic', $quantities))
        {
            if(array_key_exists('3D Printer', $quantities))
                $possibilities = array_merge($possibilities, $this->plasticPrinterService->getCraftingPossibilities($petWithSkills, $quantities));

            if(array_key_exists('Smallish Pumpkin', $quantities) && array_key_exists('Crooked Stick', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createDrumpkin', 10);
        }

        if(array_key_exists('Rice Flour', $quantities) && array_key_exists('Potato', $quantities))
            $possibilities[] = new ActivityCallback($this, 'createRicePaper', 10);

        $repairWeight = ($petWithSkills->getSmithingBonus()->getTotal() >= 3 || $petWithSkills->getCrafts()->getTotal() >= 5) ? 10 : 1;

        if(array_key_exists('Rusty Blunderbuss', $quantities))
            $possibilities[] = new ActivityCallback($this, 'repairRustyBlunderbuss', $repairWeight);

        if(array_key_exists('Rusty Rapier', $quantities))
            $possibilities[] = new ActivityCallback($this, 'repairRustyRapier', $repairWeight);

        if(array_key_exists('Sun-sun Flag', $quantities) && $quantities['Sun-sun Flag']->quantity >= 2)
            $possibilities[] = new ActivityCallback($this, 'createSunSunFlagFlagSon', 10);

        if(array_key_exists('Moon Pearl', $quantities))
        {
            if(array_key_exists('Plastic Fishing Rod', $quantities) && array_key_exists('Talon', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createPaleFlail', 10);
        }

        $possibilities = array_merge($possibilities, $this->magicBindingService->getCraftingPossibilities($petWithSkills, $quantities));
        $possibilities = array_merge($possibilities, $this->eventLanternService->getCraftingPossibilities($petWithSkills, $quantities));

        return $possibilities;
    }

    /**
     * @param ActivityCallback[] $possibilities
     */
    public function adventure(ComputedPetSkills $petWithSkills, array $possibilities): PetActivityLog
    {
        if(count($possibilities) === 0)
            throw new \InvalidArgumentException('possibilities must contain at least one item.');

        /** @var ActivityCallback $method */
        $method = $this->squirrel3->rngNextFromArray($possibilities);

        $activityLog = null;
        $changes = new PetChanges($petWithSkills->getPet());

        /** @var PetActivityLog $activityLog */
        $activityLog = ($method->callable)($petWithSkills);

        if($activityLog)
            $activityLog->setChanges($changes->compare($petWithSkills->getPet()));

        return $activityLog;
    }

    public function createTorchFromBone(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 30), PetActivityStatEnum::CRAFT, false);

            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Stereotypical Torch, but accidentally tore the White Cloth into useless shapes :(', 'icons/activity-logs/torn-to-bits');
        }
        else if($roll >= 8)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Stereotypical Bone', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Stereotypical Torch.', '');
            $this->inventoryService->petCollectsItem('Stereotypical Torch', $pet, $pet->getName() . ' created this from White Cloth and a Stereotypical Bone.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 45), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Stereotypical Torch, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createTeaTrowel(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Tea Leaves', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Trowel', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Tea Trowel.', '');
            $this->inventoryService->petCollectsItem('Tea Trowel', $pet, $pet->getName() . ' made this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 45), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Tea Trowel... or was it a Tea Towel? It\'s all very confusing.', 'icons/activity-logs/confused');
        }
    }

    public function createOwlTrowel(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 30), PetActivityStatEnum::CRAFT, false);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-1);

            if($this->squirrel3->rngNextInt(1, 2) === 1)
            {
                $this->inventoryService->loseItem('Feathers', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make an Owl Trowel, but accidentally tore the Feathers to shreds :(', '');
            }
            else
            {
                $this->inventoryService->loseItem('Yellow Dye', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make an Owl Trowel, but accidentally spilled the Yellow Dye :(', '');
            }
        }
        else if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Tea Trowel', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Feathers', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Yellow Dye', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem($this->squirrel3->rngNextInt(2, 4));
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created an Owl Trowel.', '');
            $this->inventoryService->petCollectsItem('Owl Trowel', $pet, $pet->getName() . ' made this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 45), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make an Owl Trowel, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createSimpleFiberglassItem(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        $item = $this->squirrel3->rngNextFromArray([ 'Fiberglass Flute' ]);

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);

            $this->inventoryService->loseItem('Fiberglass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a ' . $item . ', but shattered the Fiberglass! :(', '');
        }
        else if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Fiberglass', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a ' . $item . ' from Fiberglass.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
            ;
            $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' created this from Fiberglass.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a ' . $item . ', but the Fiberglass wasn\'t cooperating.', 'icons/activity-logs/confused');
        }
    }

    private function createDecoratedFlute(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Decorated Flute, but tore the White Cloth :(', '');
        }
        else if($roll >= 18)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::CRAFT, true);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ]);
            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Flute', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Glass Pendulum', $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Decorated Flute.', 'items/tool/instrument/flute-decorated')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
            ;
            $this->inventoryService->petCollectsItem('Decorated Flute', $pet, $pet->getName() . ' created this by tying a Glass Pendulum to a Flute.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% thought it might be cool to decorate a Flute, but couldn\'t think of something stylish enough.', 'icons/activity-logs/confused');
        }
    }

    private function createDrumpkin(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            if($this->squirrel3->rngNextInt(1, 2) === 1)
            {
                $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Drumpkin, but burnt the Plastic while trying to soften it :(', '');
            }
            else
            {
                $this->inventoryService->loseItem('Smallish Pumpkin', $pet->getOwner(), LocationEnum::HOME, 1);
                $pet->increaseFood(5);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Drumpkin, but broke the Smallish Pumpkin :( Not wanting to waste it, %pet:' . $pet->getId() . '.name% ate the remains...)', '');
            }
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::CRAFT, true);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Smallish Pumpkin', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Drumpkin!', 'items/tool/instrument/drumpkin')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem('Drumpkin', $pet, $pet->getName() . ' created this!', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Drumpkin, but couldn\'t get the Plastic thin enough...', 'icons/activity-logs/confused');
        }
    }

    private function createRicePaper(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            if($this->squirrel3->rngNextInt(1, 2) === 1)
            {
                $this->inventoryService->loseItem('Rice Flour', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make Paper, but messed up the Rice Flour :(', '');
            }
            else
            {
                $this->inventoryService->loseItem('Potato', $pet->getOwner(), LocationEnum::HOME, 1);
                $pet->increaseFood(4);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make Paper, but messed up the Potato :( (Not wanting to waste it, %pet:' . $pet->getId() . '.name% ate the remains...)', '');
            }
        }
        else if($roll >= 15)
        {
            $paperCount = 2;

            if($roll >= 20) $paperCount++;
            if($roll >= 25) $paperCount++;

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 55 + $paperCount * 5), PetActivityStatEnum::CRAFT, true);
            $this->petExperienceService->gainExp($pet, $paperCount, [ PetSkillEnum::CRAFTS ]);
            $this->inventoryService->loseItem('Rice Flour', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Potato', $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem($paperCount * 2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created ' . $paperCount . ' Paper!', 'items/resource/paper')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 5 + $paperCount * 5)
            ;

            for($i = 0; $i < $paperCount; $i++)
                $this->inventoryService->petCollectsItem('Paper', $pet, $pet->getName() . ' created this!', $activityLog);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make Paper, but almost wasted the Rice Flour...', 'icons/activity-logs/confused');
        }
    }

    public function createDecoratedSpear(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 30), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Feathers', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Hunting Spear', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Decorated Spear.', '');
            $this->inventoryService->petCollectsItem('Decorated Spear', $pet, $pet->getName() . ' decorated a Hunting Spear with Feathers to make this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 30), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to decorate a Hunting Spear with Feathers, but couldn\'t get the look just right.', 'icons/activity-logs/confused');
        }
    }

    public function createFishingRecorder(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getMusic()->getTotal());

        if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Leaf Spear', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('White Feathers', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::MUSIC ]);
            $pet->increaseEsteem(4);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Fishing Recorder.', '');

            if($this->squirrel3->rngNextInt(1, 5) === 1)
                $this->inventoryService->petCollectsItem('Fishing Recorder', $pet, $pet->getName() . ' made this. (The White Feathers are a nice touch, don\'t you think?)', $activityLog);
            else
                $this->inventoryService->petCollectsItem('Fishing Recorder', $pet, $pet->getName() . ' made this.', $activityLog);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::MUSIC ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to make a Fishing Recorder, but couldn\'t figure out where all the holes should go...', 'icons/activity-logs/confused');
        }
    }

    private function createDoubleScythe(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            $this->inventoryService->loseItem('Scythe', $pet->getOwner(), LocationEnum::HOME, 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Double Scythe, but split the blade on one of the Scythes :|', '');
            $pet->increaseEsteem(-2);

            $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' "made" this by accidentally breaking the blade of a Scythe while trying to make a Double Scythe.', $activityLog);

            return $activityLog;
        }
        else if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $this->inventoryService->loseItem('Scythe', $pet->getOwner(), LocationEnum::HOME, 2);

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

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Double Scythe; ' . $and, 'items/tool/scythe/double')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
            ;

            $this->inventoryService->petCollectsItem('Double Scythe', $pet, $pet->getName() . ' created this by taking the blade from one Scythe and attaching it to another.', $activityLog);
            $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' "made" this by taking the blade off of a Scythe (to make a Double Scythe).', $activityLog);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% thought it might be cool to make a Double Scythe, but then doubted themself...', 'icons/activity-logs/confused');
        }
    }

    private function createFarmersMultiTool(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            // scythes can already break when making double scythes, so let's at least skew towards garden shovels here
            $lostItem = $this->squirrel3->rngNextFromArray([ 'Scythe', 'Garden Shovel', 'Garden Shovel' ]);

            $this->inventoryService->loseItem($lostItem, $pet->getOwner(), LocationEnum::HOME, 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Farmer\'s Multi-tool, but split the blade of the ' . $lostItem . ' while trying to take it apart :|', '');
            $pet->increaseEsteem(-2);

            $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' "made" this by accidentally breaking the blade of a ' . $lostItem . ' while trying to make a Farmer\'s Multi-tool.', $activityLog);

            return $activityLog;
        }
        else if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $this->inventoryService->loseItem('Scythe', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Garden Shovel', $pet->getOwner(), LocationEnum::HOME, 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Farmer\'s Multi-tool!', 'items/tool/shovel/multi-tool')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
            ;

            $this->inventoryService->petCollectsItem('Farmer\'s Multi-tool', $pet, $pet->getName() . ' created this by combining the best parts of a Scythe and a Garden Shovel.', $activityLog);
            $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' had this left over after making a Farmer\'s Multi-tool.', $activityLog);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to combine a Scythe and a Garden Shovel, but couldn\'t decide which of the two tools to start with?', 'icons/activity-logs/confused');
        }
    }

    private function spinFluffOrCobweb(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $making = $this->itemRepository->findOneByName($this->squirrel3->rngNextFromArray([
            'String',
            'White Cloth'
        ]));

        $difficulty = $making->getName() === 'String' ? 10 : 13;

        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());
        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $spunWhat = $this->inventoryService->loseOneOf([ 'Fluff', 'Cobweb' ], $pet->getOwner(), LocationEnum::HOME);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to spin some ' . $spunWhat . ' into ' . $making->getName() . ', but messed it up; the ' . $spunWhat . ' was wasted :(', '');
        }
        else if($roll >= $difficulty)
        {
            $spunWhat = $this->inventoryService->loseOneOf([ 'Fluff', 'Cobweb' ], $pet->getOwner(), LocationEnum::HOME);

            if($roll >= $difficulty + 12)
            {
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);

                $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseEsteem(3);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% spun some ' . $spunWhat . ' into TWO ' . $making->getName() . '!', 'items/' . $making->getImage())
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + $difficulty + 12)
                ;

                $this->inventoryService->petCollectsItem($making, $pet, $pet->getName() . ' spun this from ' . $spunWhat . '.', $activityLog);
            }
            else
            {
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseEsteem(1);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% spun some ' . $spunWhat . ' into ' . $making->getName() . '.', 'items/' . $making->getImage())
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + $difficulty)
                ;
            }

            $this->inventoryService->petCollectsItem($making, $pet, $pet->getName() . ' spun this from ' . $spunWhat . '.', $activityLog);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to spin some ' . $making->getName() . ', but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    private function makeChocolateTool(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($this->calendarService->isValentinesOrAdjacent())
            $making = $this->itemRepository->findOneByName('Chocolate Key');
        else
        {
            $making = $this->itemRepository->findOneByName($this->squirrel3->rngNextFromArray([
                'Chocolate Sword',
                'Chocolate Sword',
                'Chocolate Hammer',
                'Chocolate Hammer',
                'Chocolate Key'
            ]));
        }

        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        $botchChance = $pet->getFood() <= 0 ? 3 : 1;

        if($roll <= $botchChance)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            $this->inventoryService->loseItem('Chocolate Bar', $pet->getOwner(), LocationEnum::HOME, 1);

            $pet->increaseFood($this->squirrel3->rngNextInt(2, 4));
            $pet->increaseEsteem(-2);

            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started making ' . $making->getNameWithArticle() . ', but ended up eating the Chocolate Bar, instead >_>', '');
        }
        else if($roll >= 10)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Chocolate Bar', $pet->getOwner(), LocationEnum::HOME, 1);

            $makeTwo = $roll >= 20 && $making->getName() === 'Chocolate Key';

            if($makeTwo)
            {
                $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseEsteem(4);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% molded a Chocolate Bar into TWO ' . $making->getName() . 's!', 'items/' . $making->getImage())
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                ;

                $this->inventoryService->petCollectsItem($making, $pet, $pet->getName() . ' made this out of a Chocolate Bar.', $activityLog);
            }
            else
            {
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% molded a Chocolate Bar into ' . $making->getNameWithArticle() . '.', 'items/' . $making->getImage())
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
                ;
            }

            $this->inventoryService->petCollectsItem($making, $pet, $pet->getName() . ' made this out of a Chocolate Bar.', $activityLog);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make ' . $making->getNameWithArticle() . ', but couldn\'t get the mold right...', '');
        }
    }

    private function createYellowDyeFromTeaLeaves(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 5)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, false);
            $this->inventoryService->loseItem('Tea Leaves', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ]);

            if($this->squirrel3->rngNextInt(1, 3) === 1)
            {
                $message = $pet->getName() . ' tried to extract Yellow Dye from Tea Leaves, but accidentally made Black Tea, instead!';

                if($this->squirrel3->rngNextInt(1, 10) === 1)
                    $message .= ' (Aren\'t the leaves themselves green? Where are all these colors coming from?!)';

                $activityLog = $this->responseService->createActivityLog($pet, $message, '');
                $this->inventoryService->petCollectsItem('Black Tea', $pet, $pet->getName() . ' accidentally made this while trying to extract Yellow Dye from Tea Leaves.', $activityLog);
                return $activityLog;
            }
            else
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to extract Yellow Dye from Tea Leaves, but messed it up, ruining the Tea Leaves :(', '');
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Tea Leaves', $pet->getOwner(), LocationEnum::HOME);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% extracted Yellow Dye from some Tea Leaves.', 'items/resource/dye-yellow');
            $this->inventoryService->petCollectsItem('Yellow Dye', $pet, $pet->getName() . ' extracted this from Tea Leaves.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to extract Yellow Dye from some Tea Leaves, but wasn\'t sure how to start.', 'icons/activity-logs/confused');
        }
    }

    private function extractFromScales(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getCrafts()->getTotal());
        $itemName = $this->squirrel3->rngNextInt(1, 2) === 1 ? 'Green Dye' : 'Glue';

        if($roll <= 5)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, false);
            $this->inventoryService->loseItem('Scales', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog(
                $pet,
                $pet->getName() . ' tried to extract ' . $itemName . ' from Scales, but messed it up, ruining the Scales :(',
                ''
            );
        }
        else if($roll >= 20)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 90), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Scales', $pet->getOwner(), LocationEnum::HOME);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% extracted Green Dye _and_ Glue from some Scales!', 'items/animal/scales');
            $this->inventoryService->petCollectsItem('Green Dye', $pet, $pet->getName() . ' extracted this from Scales.', $activityLog);
            $this->inventoryService->petCollectsItem('Glue', $pet, $pet->getName() . ' extracted this from Scales.', $activityLog);
            return $activityLog;
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Scales', $pet->getOwner(), LocationEnum::HOME);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% extracted ' . $itemName . ' from some Scales.', 'items/animal/scales');
            $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' extracted this from Scales.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to extract ' . $itemName . ' from some Scales, but wasn\'t sure how to start.', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function createFabricMache(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-2);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make some Fabric Mâché, but messed it all up, ruining the White Cloth and wasting the Glue :(', 'icons/activity-logs/torn-to-bits');
        }
        else if($roll >= 14)
        {
            $possibleItems = [
                'Fabric Mâché Basket'
            ];

            $item = $this->squirrel3->rngNextFromArray($possibleItems);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);

            if($item === 'Fabric Mâché Basket' && $this->squirrel3->rngNextInt(1, 10) === 1)
            {
                $transformation = $this->squirrel3->rngNextFromArray([
                    [ 'item' => 'Flower Basket', 'goodies' => 'flowers' ],
                    [ 'item' => 'Fruit Basket', 'goodies' => 'fruit' ],
                ]);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Fabric Mâché Basket. Once they were done, a fairy appeared out of nowhere, and filled the basket up with ' . $transformation['goodies'] . '!', '');
                $this->inventoryService->petCollectsItem($transformation['item'], $pet, $pet->getName() . ' created a Fabric Mâché Basket; once they were done, a fairy appeared and filled it with ' . $transformation['goodies'] . '!', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a ' . $item . '.', '');
                $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' created this from White Cloth and Glue.', $activityLog);
            }

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make some Fabric Mâché, but couldn\'t come up with a good pattern.', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function createGoldTrifecta(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-2);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Gold Trifecta, but messed up and wasted the Glue :(', '');
        }
        else if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Gold Triangle', $pet->getOwner(), LocationEnum::HOME, 3);
            $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Gold Trifecta.', '');
            $this->inventoryService->petCollectsItem('Gold Trifecta', $pet, $pet->getName() . ' created by gluing together three Gold Triangles.', $activityLog);

            if($this->squirrel3->rngNextInt(1, 2) === 1) $this->inventoryService->petCollectsItem('String', $pet, $pet->getName() . ' recovered this when creating a Gold Trifecta.', $activityLog);
            if($this->squirrel3->rngNextInt(1, 2) === 1) $this->inventoryService->petCollectsItem('String', $pet, $pet->getName() . ' recovered this when creating a Gold Trifecta.', $activityLog);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to make a Gold Trifecta, but wasn\'t sure how to begin...', 'icons/activity-logs/confused');
        }
    }

    private function createLSquare(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            if($this->squirrel3->rngNextInt(1, 2) === 1)
            {
                $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
                $pet->increaseEsteem(-2);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make an L-Square, but messed up and wasted the Glue :(', '');
            }
            else
            {
                $this->inventoryService->loseItem('Ruler', $pet->getOwner(), LocationEnum::HOME, 1);
                $pet->increaseSafety(-2);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make an L-Square, but accidentally snapped one of the Rulers in half! :(', '');
            }
        }
        else if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Ruler', $pet->getOwner(), LocationEnum::HOME, 2);
            $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created an L-Square.', '');
            $this->inventoryService->petCollectsItem('L-Square', $pet, $pet->getName() . ' created by gluing together a couple Rulers.', $activityLog);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to make an L-Square, but spent forever trying to make it _exactly_ 90 degrees, and eventually gave up...', 'icons/activity-logs/confused');
        }
    }

    private function createAlienCookingBuddy(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-2);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to glue some Antennae onto a Cooking Buddy, but messed up and wasted the Glue :(', '');
        }
        else if($roll >= 10)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Cooking Buddy', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Antenna', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet
                ->increaseEsteem(4)
                ->increaseSafety(2)
            ;
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% cracked themselves up by creating a Cooking "Alien".', '');
            $this->inventoryService->petCollectsItem('Cooking "Alien"', $pet, $pet->getName() . ' created by gluing some Antennae on a Cooking Buddy.', $activityLog);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to do something silly to a Cooking Buddy, but couldn\'t decide what...', 'icons/activity-logs/confused');
        }
    }

    private function createBugBow(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getNature()->getTotal(), $petWithSkills->getCrafts()->getTotal()));

        if($roll <= 2)
        {
            $lostItem = $this->squirrel3->rngNextInt(1, 2) === 1 ? 'Cobweb' : 'Antenna';

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);

            $this->inventoryService->loseItem($lostItem, $pet->getOwner(), LocationEnum::HOME, 1);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% starting making a Bug Bow, but accidentally tore the ' . $lostItem . ' :(', '');
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Fiberglass Bow', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Cobweb', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Antenna', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% turned a boring ol\' Fiberglass Bow into a Bug Bow!', '');
            $this->inventoryService->petCollectsItem('Bug Bow', $pet, $pet->getName() . ' created this out of a Fiberglass Bow and some bug bits.', $activityLog);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started making a Bug Bow, but kept getting stuck to the Cobweb.', 'icons/activity-logs/confused');
        }
    }

    private function createFiberglassPanFlute(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getMusic()->getTotal(), $petWithSkills->getCrafts()->getTotal()));

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);

            $lostItem = $this->squirrel3->rngNextFromArray([ 'Glue', 'White Cloth', 'Fiberglass Flute' ]);
            $this->inventoryService->loseItem($lostItem, $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-2);

            switch($lostItem)
            {
                case 'Fiberglass Flute':
                    return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Fiberglass Pan Flute, but shattered the Fiberglass Flutes while trying to cut it up :|', '');

                case 'Glue':
                    return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Fiberglass Pan Flute, but accidentally spilled the Glue everywhere :|', '');

                case 'White Cloth':
                    return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Fiberglass Pan Flute, but tore the White Cloth while trying to cut it into shape :|', '');

                default:
                    throw new \Exception('Ben done fucked up: a pet was going to accidentally break a ' . $lostItem . ' while crafting, but that wasn\'t accounted for by the Fiberglass Pan Flute event code...');
            }
        }
        else if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);

            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Fiberglass Flute', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Fiberglass Pan Flute.', '');
            $this->inventoryService->petCollectsItem('Fiberglass Pan Flute', $pet, $pet->getName() . ' created this by hacking a Fiberglass Flute into several pieces, and gluing them together with a ribbon of cloth.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to make a Fiberglass Pan Flute, but didn\'t feel confident about cutting the Fiberglass Flute in half.', 'icons/activity-logs/confused');
        }
    }

    private function createGlassPendulum(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);

            if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStamina()->getTotal()) >= 18)
            {
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseEsteem(2);
                $pet->increaseSafety(-4);

                if($this->squirrel3->rngNextInt(1, 4) === 1)
                {
                    $pet->increaseEsteem(2);
                    return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to cut a piece of glass, but cut themselves, instead! :( They managed to save the glass, though! ' . $pet->getName() . ' is kind of proud of that.', 'icons/activity-logs/wounded');
                }
                else
                    return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to cut a piece of glass, but cut themselves, instead! :( They managed to save the glass, though!', 'icons/activity-logs/wounded');
            }
            else
            {
                $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseEsteem(-2);
                $pet->increaseSafety(-4);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to cut a piece of glass, but cut themselves, instead, and dropped the glass :(', 'icons/activity-logs/wounded');
            }
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% cut some Glass to look like a gem, and made a Glass Pendulum.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem('Glass Pendulum', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $pet->increaseSafety(-1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Glass Pendulum, but almost cut themselves on the glass, and gave up.', 'icons/activity-logs/confused');
        }
    }

    private function createBownaner(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() * 2 + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);

            if($pet->getFood() + $pet->getJunk() < 0)
            {
                $this->inventoryService->loseItem('Naner', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseFood(4);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make a bow out of a Naner, but was feeling hungry, so... they ate the Naner.', '');
            }
            else
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseFood(4);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make a bow out of a Naner, but broke the String.', 'icons/activity-logs/broke-string');
            }
        }
        else if($roll >= 11)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Naner', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a makeshift bow... out of a Naner.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 11)
            ;
            $this->inventoryService->petCollectsItem('Bownaner', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Bownaner, but the String kept getting all tangled.', 'icons/activity-logs/confused');
        }
    }

    private function createEatYourFruitsAndVeggies(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() * 2 + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);

            if($pet->getFood() + $pet->getJunk() < 0)
            {
                $this->inventoryService->loseItem('Carrot', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseFood(2);
                $pet->increaseJunk(-2);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make an Eat Your Fruits And Veggies, but was feeling hungry, so... they ate the Carrot.', '');
            }
            else
            {
                $this->inventoryService->loseItem('Bownaner', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make an Eat Your Fruits And Veggies, but broke the Bownaner\'s String...', 'icons/activity-logs/broke-string');
                $this->inventoryService->petCollectsItem('Naner', $pet, $pet->getName() . ' accidentally broke a Bownaner. Now it\'s just this Naner.', $activityLog);
                return $activityLog;
            }
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Carrot', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Bownaner', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% loaded a Bownaner with a Carrot...', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 12)
            ;
            $this->inventoryService->petCollectsItem('Eat Your Fruits And Veggies', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to load a Bownaner with a Carrot, but the String kept getting all tangled.', 'icons/activity-logs/confused');
        }
    }

    private function createLeafSpear(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getStrength()->getTotal() * 2 + $petWithSkills->getDexterity()->getTotal());

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);

            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Leaf Spear, but the String couldn\'t hold the Really Big Leaf, and broke under the stress!', 'icons/activity-logs/broke-string');
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Really Big Leaf', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);

            $message = $pet->getName() . ' rolled up a Really Big Leaf, and tied it, creating a Leaf Spear!';

            if($this->squirrel3->rngNextInt(1, 5) > $petWithSkills->getStrength()->getTotal())
                $message .= ' (It\'s harder than it looks!)';

            $activityLog = $this->responseService->createActivityLog($pet, $message, '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem('Leaf Spear', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $pet->increaseSafety(-1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Leaf Spear, but the Really Big Leaf is surprisingly strong! ' . $pet->getName() . ' couldn\'t get it to roll up...', 'icons/activity-logs/confused');
        }
    }

    private function createBenjaminFranklin(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getScience()->getTotal()));

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ]);

            if($this->squirrel3->rngNextInt(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a kite, but broke the String :(', 'icons/activity-logs/broke-string');
            }
            else
            {
                $this->inventoryService->loseItem('Paper', $pet->getOwner(), LocationEnum::HOME, 1);
                $pet->increaseEsteem(-2);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a kite, but tore the Paper :(', '');
            }
        }
        else if($roll >= 17)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Paper', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Silver Key', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created Benjamin Franklin. (A kite, not the person.)', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 17)
            ;
            $this->inventoryService->petCollectsItem('Benjamin Franklin', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a kite, but couldn\'t come up with a good design...', 'icons/activity-logs/confused');
        }
    }

    private function createRibbelysComposite(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            if($this->squirrel3->rngNextInt(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a bow out of an L-Square, but broke the String :(', 'icons/activity-logs/broke-string');
            }
            else
            {
                $this->inventoryService->loseItem('Green Dye', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a bow out of an L-Square, but spilled the Green Dye :(', '');
            }
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Green Dye', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('L-Square', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created Ribbely\'s Composite.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem('Ribbely\'s Composite', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a bow out of an L-Square, but, ironically, couldn\'t get the measurements right.', 'icons/activity-logs/confused');
        }
    }

    private function createFeatheredHat(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);

            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a hat, but tore the cloth :(', '');
        }
        else if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Ruby Feather', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(5);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made an Afternoon Hat by shaping some White Cloth, and tying a Ruby Feather to it!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 17)
            ;
            $this->inventoryService->petCollectsItem('Afternoon Hat', $pet, $pet->getName() . ' created this!', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to make a hat, but couldn\'t come up with a good design...', 'icons/activity-logs/confused');
        }
    }

    private function createOrnatePanFlute(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getMusic()->getTotal()));

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            if($this->squirrel3->rngNextInt(1, 2) === 1)
            {
                $this->inventoryService->loseItem('Feathers', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::MUSIC ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to decorate a Fiberglass Pan Flute, but misruffled the feathers :(', '');
            }
            else
            {
                $this->inventoryService->loseItem('Yellow Dye', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::MUSIC ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to decorate a Fiberglass Pan Flute, but spilled the Yellow Dye :(', '');
            }
        }
        else if($roll >= 18)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Fiberglass Pan Flute', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Yellow Dye', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Feathers', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS, PetSkillEnum::MUSIC ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created an Ornate Pan Flute.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
            ;
            $this->inventoryService->petCollectsItem('Ornate Pan Flute', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::MUSIC ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to decorate a Fiberglass Pan Flute, but couldn\'t come up with a good design.', 'icons/activity-logs/confused');
        }
    }

    private function createSnakebite(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-2);
            $pet->increaseSafety(-4);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to craft Snakebite, but cut themself on a Talon!', 'icons/activity-logs/wounded');
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Talon', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Scales', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Wooden Sword', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Snakebite sword.', '');
            $this->inventoryService->petCollectsItem('Snakebite', $pet, $pet->getName() . ' made this by improving a Wooden Sword.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to improve a Wooden Sword into Snakebite, but failed.', 'icons/activity-logs/confused');
        }
    }

    private function createVeilPiercer(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getUmbra()->getTotal() + $petWithSkills->getIntelligence()->getTotal());
        $craftsCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($umbraCheck <= 3)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchanted a Decorated Spear, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($craftsCheck < 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried enchant a Decorated Spear, but couldn\'t get an enchantment to stick.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $veilPiercer = $this->itemRepository->findOneByName('Veil-piercer');

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Decorated Spear', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% enchanted a Decorated Spear to be ' . $veilPiercer->getNameWithArticle() . '.', 'items/' . $veilPiercer->getImage())
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem($veilPiercer, $pet, $pet->getName() . ' made this by enchanting a Decorated Spear.', $activityLog);
            return $activityLog;
        }
    }

    private function createNagatooth(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $craftsCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($craftsCheck < 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried decorate a Decorated Spear _even more_, but couldn\'t get the pattern just right...', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Dark Scales', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Decorated Spear', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% further decorated a Decorated Spear; now it\'s a Nagatooth!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem('Nagatooth', $pet, $pet->getName() . ' made this by further decorating a Decorated Spear.', $activityLog);
            return $activityLog;
        }
    }

    private function createLassoscope(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $craftsCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal());

        if($craftsCheck <= 3)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Lassoscope by roping a Gold Telescope with a Flying Grappling Hook, but the Wings broke off, and flew away!', '');

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Flying Grappling Hook', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->petCollectsItem('Grappling Hook', $pet, 'A Flying Grappling Hook lost its wings when ' . $pet->getName() . ' tried to make a Lassoscope. A plain Grappling Hook was all that remained...', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-1);
            return $activityLog;
        }
        else if($craftsCheck < 20)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to make a Lassoscope, but couldn\'t successfully lasso a Gold Telescope...', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Flying Grappling Hook', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Gold Telescope', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem($this->squirrel3->rngNextInt(4, 8));

            $safelyExamineLions = $this->squirrel3->rngNextInt(1, 10) === 1;

            $activityLogMessage = $safelyExamineLions
                ? $pet->getName() . ' made a Lassoscope! (Now they can safely examine lions!)'
                : $pet->getName() . ' made a Lassoscope!'
            ;

            $activityLog = $this->responseService->createActivityLog($pet, $activityLogMessage, '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
            ;

            $itemComment = $safelyExamineLions
                ? $pet->getName() . ' made this by lassoing a Gold Telescope with a Flying Grappling Hook. (Now they can safely examine lions!)'
                : $pet->getName() . ' made this by lassoing a Gold Telescope with a Flying Grappling Hook.'
            ;

            $this->inventoryService->petCollectsItem('Lassoscope', $pet, $itemComment, $activityLog);

            return $activityLog;
        }
    }

    private function createPaintedFishingRod(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 90), PetActivityStatEnum::CRAFT, true);
        $this->inventoryService->loseItem('Crooked Fishing Rod', $pet->getOwner(), LocationEnum::HOME, 1);
        $this->inventoryService->loseItem('Yellow Dye', $pet->getOwner(), LocationEnum::HOME, 1);
        $this->inventoryService->loseItem('Green Dye', $pet->getOwner(), LocationEnum::HOME, 1);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
        $pet->increaseEsteem(1);
        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Painted Fishing Rod.', '');
        $this->inventoryService->petCollectsItem('Painted Fishing Rod', $pet, $pet->getName() . ' painted this, using Yellow and Green Dye.', $activityLog);
        return $activityLog;
    }

    private function createPaintedBoomerang(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 90), PetActivityStatEnum::CRAFT, true);
        $this->inventoryService->loseItem('Plastic Boomerang', $pet->getOwner(), LocationEnum::HOME, 1);
        $this->inventoryService->loseItem('Quinacridone Magenta Dye', $pet->getOwner(), LocationEnum::HOME, 1);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
        $pet->increaseEsteem(3);
        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Painted Boomerang.', '');
        $this->inventoryService->petCollectsItem('Painted Boomerang', $pet, $pet->getName() . ' painted this, using Quinacridone Magenta Dye.', $activityLog);
        return $activityLog;
    }

    private function createGoldIdol(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 90), PetActivityStatEnum::CRAFT, true);
        $this->inventoryService->loseItem('Plastic Idol', $pet->getOwner(), LocationEnum::HOME, 1);
        $this->inventoryService->loseItem('Yellow Dye', $pet->getOwner(), LocationEnum::HOME, 1);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
        $pet->increaseEsteem(1);
        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a "Gold" Idol.', '');
        $this->inventoryService->petCollectsItem('"Gold" Idol', $pet, $pet->getName() . ' painted this, using Yellow Dye.', $activityLog);
        return $activityLog;
    }

    private function createYellowBucket(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 30), PetActivityStatEnum::CRAFT, true);
        $this->inventoryService->loseItem('Small Plastic Bucket', $pet->getOwner(), LocationEnum::HOME, 1);
        $this->inventoryService->loseItem('Yellow Dye', $pet->getOwner(), LocationEnum::HOME, 1);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
        $pet->increaseEsteem(1);
        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% dunked a Small Plastic Bucket into some Yellow Dye.', '');
        $this->inventoryService->petCollectsItem('Small, Yellow Plastic Bucket', $pet, $pet->getName() . ' "painted" this, using Yellow Dye.', $activityLog);
        return $activityLog;
    }

    private function createPaintedDumbbell(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::CRAFT, true);
        $this->inventoryService->loseItem('Dumbbell', $pet->getOwner(), LocationEnum::HOME, 1);
        $this->inventoryService->loseItem('Yellow Dye', $pet->getOwner(), LocationEnum::HOME, 1);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
        $pet->increaseEsteem(1);

        if($this->squirrel3->rngNextInt(1, 10) === 1)
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% painted emojis on a Dumbbell. (That makes them better, right?)', '');
        else
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% painted emojis on a Dumbbell.', '');

        $this->inventoryService->petCollectsItem('Painted Dumbbell', $pet, $pet->getName() . ' painted this, using Yellow Dye.', $activityLog);

        return $activityLog;
    }

    private function repairRustyBlunderbuss(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getSmithingBonus()->getTotal()));

        if($roll === 1 && !$pet->hasMerit(MeritEnum::LUCKY))
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, false);
            $this->inventoryService->loseItem('Rusty Blunderbuss', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-4);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to repair a Rusty Blunderbuss, but accidentally broke it beyond repair :(', '');
        }
        else if($roll >= 18)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Rusty Blunderbuss', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% repaired a Rusty Blunderbuss. It\'s WAY less rusty now!', 'items/tool/gun/blunderbuss')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
            ;
            $this->inventoryService->petCollectsItem('Blunderbuss', $pet, $pet->getName() . ' repaired this Rusty Blunderbuss.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% spent a while trying to repair a Rusty Blunderbuss, but wasn\'t able to make any progress.', 'icons/activity-logs/confused');
        }
    }

    private function repairRustyRapier(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getSmithingBonus()->getTotal()));

        if($roll === 1 && !$pet->hasMerit(MeritEnum::LUCKY))
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, false);
            $this->inventoryService->loseItem('Rusty Rapier', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            $pet->increaseEsteem(-4);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to repair a Rusty Rapier, but accidentally broke it beyond repair :(', '');
        }
        else if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Rusty Rapier', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% repaired a Rusty Rapier. It\'s WAY less rusty now!', 'items/tool/sword/rapier')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
            ;
            $this->inventoryService->petCollectsItem('Rapier', $pet, $pet->getName() . ' repaired this Rapier.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% spent a while trying to repair a Rusty Rapier, but wasn\'t able to make any progress.', 'icons/activity-logs/confused');
        }
    }

    private function createLaserGuidedSword(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getScience()->getTotal(), $petWithSkills->getCrafts()->getTotal()));

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(-2);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Laser-guided Sword, but messed up and wasted the Glue :(', '');
        }
        else if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Iron Sword', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Laser Pointer', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Laser-guided Sword.', '');

            if($this->squirrel3->rngNextInt(1, 4) === 1)
                $this->inventoryService->petCollectsItem('Laser-guided Sword', $pet, $pet->getName() . ' created by gluing a Laser Pointer to a sword. Naturally.', $activityLog);
            else
                $this->inventoryService->petCollectsItem('Laser-guided Sword', $pet, $pet->getName() . ' created by gluing a Laser Pointer to a sword.', $activityLog);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to improve an Iron Sword, but wasn\'t sure how to begin...', 'icons/activity-logs/confused');
        }
    }

    private function createSunFlag(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        return $this->createFlag($petWithSkills, 'Yellow Dye', 'Sun Flag');
    }

    private function createDragonFlag(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        return $this->createFlag($petWithSkills, 'Green Dye', 'Dragon Flag');
    }

    private function createFlag(ComputedPetSkills $petWithSkills, string $dye, string $making)
    {
        $pet = $petWithSkills->getPet();
        $makingItem = $this->itemRepository->findOneByName($making);

        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 30), PetActivityStatEnum::CRAFT, false);

            $this->inventoryService->loseItem($dye, $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make ' . $makingItem->getNameWithArticle() . ', but accidentally spilt the ' . $dye . ' :(', '');
        }
        else if($roll >= 10)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('White Flag', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem($dye, $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% dyed ' . $makingItem->getNameWithArticle() . '.', 'items/' . $makingItem->getImage())
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
            ;
            $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' dyed this flag.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 45), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to dye a flag, but couldn\'t come up with a good design.', 'icons/activity-logs/confused');
        }
    }

    private function createSunSunFlag(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 30), PetActivityStatEnum::CRAFT, false);

            $this->inventoryService->loseItem('Sunflower Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Sun-sun Flag, but accidentally broke the flower on the Sunflower Stick, leaving only a stick :(', '');
            $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' ruined the flower on a Sunflower Stick, leaving only this...', $activityLog);
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Sun Flag', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Sunflower Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseSafety($this->squirrel3->rngNextInt(2, 4));
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Sun-sun Flag!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
            ;
            $this->inventoryService->petCollectsItem('Sun-sun Flag', $pet, $pet->getName() . ' made this.', $activityLog);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 45), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to make a Sun Flag even sunnier, but wasn\'t sure how...', 'icons/activity-logs/confused');
        }

        return $activityLog;
    }

    private function createSunSunFlagFlagSon(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 30), PetActivityStatEnum::CRAFT, false);

            $this->inventoryService->loseItem('Sun-sun Flag', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to combine two Sun-sun Flags, but accidentally tore one of them; all that\'s left is the stick :(', '');

            if($this->squirrel3->rngNextInt(1, 10) === 1)
                $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' tore a Sun-sun Flag, leaving only this. (Dang, son!)', $activityLog);
            else
                $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' tore a Sun-sun Flag, leaving only this...', $activityLog);
        }
        else if($roll >= 20)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Sun-sun Flag', $pet->getOwner(), LocationEnum::HOME, 2);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem($this->squirrel3->rngNextInt(2, 4));
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Sun-sun Flag-flag, Son!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
            ;
            $this->inventoryService->petCollectsItem('Sun-sun Flag-flag, Son', $pet, $pet->getName() . ' made this.', $activityLog);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 45), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to combine two Sun-sun Flags, but couldn\'t figure it out...', 'icons/activity-logs/confused');
        }

        return $activityLog;
    }

    private function createPaleFlail(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);

            $this->inventoryService->loseItem('Talon', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Pail Flail, but broke the Talon :(', '');
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Plastic Fishing Rod', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Moon Pearl', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Talon', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Pale Flail!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem('Pale Flail', $pet, $pet->getName() . ' made this.', $activityLog);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to make a flail, but had trouble shaping the Plastic Fishing Rod.', 'icons/activity-logs/confused');
        }

        return $activityLog;
    }

    private function createBindle(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::CRAFT, false);

            $this->inventoryService->loseItem('White Flag', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Bindle, but accidentally tore the White Flag :(', '');
            $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' accidentally tore a White Flag; this remains.', $activityLog);
        }
        else if($roll >= 10 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('White Flag', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Bindle from a White Flag.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
            ;
            $this->inventoryService->petCollectsItem('Bindle', $pet, $pet->getName() . ' made this.', $activityLog);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 45), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to tie a Bindle, but couldn\'t remember their knots...', 'icons/activity-logs/confused');
        }

        return $activityLog;
    }

    public function createPeacockPlushy(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $craftsCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($craftsCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            if($this->squirrel3->rngNextInt(1, 2) === 1)
            {
                $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a plushy, but cut the cloth wrong :(', '');
            }
            else
            {
                $this->inventoryService->loseItem('Quinacridone Magenta Dye', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a plushy, but spilled the dye :(', '');
            }
        }
        else if($craftsCheck >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Quinacridone Magenta Dye', $pet->getOwner(), LocationEnum::HOME, 1);

            $stuffing = $this->inventoryService->loseOneOf([ 'Beans', 'Fluff' ], $pet->getOwner(), LocationEnum::HOME);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Peacock Plushy stuffed with ' . $stuffing . '!', '');
            $this->inventoryService->petCollectsItem('Peacock Plushy', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a plushy, but couldn\'t come up with a good pattern...', 'icons/activity-logs/confused');
        }
    }
}
