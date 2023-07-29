<?php
namespace App\Service\PetActivity;

use App\Entity\PetActivityLog;
use App\Enum\EnumInvalidValueException;
use App\Enum\HolidayEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Model\ActivityCallback;
use App\Model\ComputedPetSkills;
use App\Model\ItemQuantity;
use App\Model\PetChanges;
use App\Repository\ItemRepository;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\SpiceRepository;
use App\Service\CalendarService;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetActivity\Crafting\EventLanternService;
use App\Service\PetActivity\Crafting\Helpers\TwuWuvCraftingService;
use App\Service\PetActivity\Crafting\Helpers\StickCraftingService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\WeatherService;

class CraftingService
{
    private ResponseService $responseService;
    private InventoryService $inventoryService;
    private PetExperienceService $petExperienceService;
    private StickCraftingService $stickCraftingService;
    private ItemRepository $itemRepository;
    private EventLanternService $eventLanternService;
    private TwuWuvCraftingService $twuWuvCraftingService;
    private IRandom $squirrel3;
    private CalendarService $calendarService;
    private WeatherService $weatherService;
    private HouseSimService $houseSimService;
    private PetActivityLogTagRepository $petActivityLogTagRepository;
    private SpiceRepository $spiceRepository;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, PetExperienceService $petExperienceService,
        StickCraftingService $stickCraftingService, ItemRepository $itemRepository, EventLanternService $eventLanternService,
        TwuWuvCraftingService $twuWuvCraftingService, Squirrel3 $squirrel3, CalendarService $calendarService,
        WeatherService $weatherService, HouseSimService $houseSimService, SpiceRepository $spiceRepository,
        PetActivityLogTagRepository $petActivityLogTagRepository
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
        $this->stickCraftingService = $stickCraftingService;
        $this->itemRepository = $itemRepository;
        $this->eventLanternService = $eventLanternService;
        $this->twuWuvCraftingService = $twuWuvCraftingService;
        $this->squirrel3 = $squirrel3;
        $this->calendarService = $calendarService;
        $this->weatherService = $weatherService;
        $this->houseSimService = $houseSimService;
        $this->petActivityLogTagRepository = $petActivityLogTagRepository;
        $this->spiceRepository = $spiceRepository;
    }

    /**
     * @param ItemQuantity[] $quantities
     * @return ActivityCallback[]
     */
    public function getCraftingPossibilities(ComputedPetSkills $petWithSkills): array
    {
        $possibilities = [];

        if($this->houseSimService->hasInventory('Twu Wuv') && $this->houseSimService->hasInventory('Red Balloon'))
        {
            $possibilities[] = new ActivityCallback($this->twuWuvCraftingService, 'createWedBawwoon', 15);
        }

        if($this->houseSimService->hasInventory('Chocolate Bar'))
        {
            $weight = $this->calendarService->isValentinesOrAdjacent() ? 80 : 8;

            $possibilities[] = new ActivityCallback($this, 'makeChocolateTool', $weight);
        }

        if($this->houseSimService->hasInventory('Fluff') || $this->houseSimService->hasInventory('Cobweb'))
        {
            $possibilities[] = new ActivityCallback($this, 'spinFluffOrCobweb', 10);
        }

        if($this->houseSimService->hasInventory('White Cloth'))
        {
            if($this->houseSimService->hasInventory('Quinacridone Magenta Dye'))
            {
                if($this->houseSimService->hasInventory('Fluff') || $this->houseSimService->hasInventory('Beans'))
                    $possibilities[] = new ActivityCallback($this, 'createPeacockPlushy', 10);
            }

            if($this->houseSimService->hasInventory('String') && $this->houseSimService->hasInventory('Ruby Feather'))
                $possibilities[] = new ActivityCallback($this, 'createFeatheredHat', 10);

            if($this->houseSimService->hasInventory('Glass Pendulum') && $this->houseSimService->hasInventory('Flute'))
                $possibilities[] = new ActivityCallback($this, 'createDecoratedFlute', 10);

            if($this->houseSimService->hasInventory('Stereotypical Bone'))
                $possibilities[] = new ActivityCallback($this, 'createTorchFromBone', 5);
        }

        if($this->houseSimService->hasInventory('Gold Telescope') && $this->houseSimService->hasInventory('Flying Grappling Hook'))
            $possibilities[] = new ActivityCallback($this, 'createLassoscope', 10);

        if($this->houseSimService->hasInventory('Tea Leaves'))
        {
            $possibilities[] = new ActivityCallback($this, 'createYellowDyeFromTeaLeaves', 10);

            if($this->houseSimService->hasInventory('Trowel'))
                $possibilities[] = new ActivityCallback($this, 'createTeaTrowel', 10);
        }

        if($this->houseSimService->hasInventory('Scales'))
        {
            $possibilities[] = new ActivityCallback($this, 'extractFromScales', 10);

            if($this->houseSimService->hasInventory('Talon') && $this->houseSimService->hasInventory('Wooden Sword'))
                $possibilities[] = new ActivityCallback($this, 'createSnakebite', 10);
        }

        if($this->houseSimService->hasInventory('Crooked Stick'))
        {
            if($this->houseSimService->hasInventory('Small, Yellow Plastic Bucket'))
                $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createNanerPicker', 10);

            if($this->houseSimService->hasInventory('Sunflower'))
                $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createSunflowerStick', 10);

            if($this->houseSimService->hasInventory('Glue') || $this->houseSimService->hasInventory('String'))
            {
                $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createWoodenSword', 10);
            }

            if($this->houseSimService->hasInventory('String'))
            {
                $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createCrookedFishingRod', 10);

                if($this->houseSimService->hasInventory('Talon'))
                    $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createHuntingSpear', 10);

                if($this->houseSimService->hasInventory('Hunting Spear'))
                    $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createVeryLongSpear', 10);

                if($this->houseSimService->hasInventory('Overly-long Spear'))
                    $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createRidiculouslyLongSpear', 10);

                if($this->houseSimService->hasInventory('Corn') && $this->houseSimService->hasInventory('Rice'))
                    $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createHarvestStaff', 10);

                if($this->houseSimService->hasInventory('Red'))
                    $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createRedFlail', 10);
            }

            if($this->houseSimService->hasInventory('Cobweb'))
                $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createBugCatchersNet', 10);

            if($this->houseSimService->hasInventory('Glue') && ($this->houseSimService->hasInventory('Wheat') || $this->houseSimService->hasInventory('Rice')))
                $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createStrawBroom', 10);

            if($this->houseSimService->hasInventory('White Cloth'))
                $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createTorchOrFlag', 10);

            if($this->houseSimService->hasInventory('Toadstool') && $this->houseSimService->hasInventory('Quintessence'))
                $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createChampignon', 10);

            if($this->houseSimService->hasInventory('Glass'))
                $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createRusticMagnifyingGlass', 10);

            if($this->houseSimService->hasInventory('Sweet Beet') && $this->houseSimService->hasInventory('Glue'))
                $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createSweetBeat', 10);
        }

        if($this->houseSimService->hasInventory('Glue'))
        {
            if($this->houseSimService->hasInventory('White Cloth'))
            {
                if($this->houseSimService->hasInventory('Fiberglass Flute'))
                    $possibilities[] = new ActivityCallback($this, 'createFiberglassPanFlute', 11);

                $possibilities[] = new ActivityCallback($this, 'createFabricMache', 7);
            }

            if($this->houseSimService->hasInventory('Gold Triangle', 3))
                $possibilities[] = new ActivityCallback($this, 'createGoldTrifecta', 10);

            if($this->houseSimService->hasInventory('Ruler', 2))
                $possibilities[] = new ActivityCallback($this, 'createLSquare', 10);

            if($this->houseSimService->hasInventory('Cooking Buddy') && $this->houseSimService->hasInventory('Antenna'))
                $possibilities[] = new ActivityCallback($this, 'createAlienCookingBuddy', 10);

            if($this->houseSimService->hasInventory('Painted Camera') && $this->houseSimService->hasInventory('Antenna'))
                $possibilities[] = new ActivityCallback($this, 'createAlienCamera', 10);

            if($this->houseSimService->hasInventory('Iron Sword') && $this->houseSimService->hasInventory('Laser Pointer'))
                $possibilities[] = new ActivityCallback($this, 'createLaserGuidedSword', 10);
        }

        if($this->houseSimService->hasInventory('Antenna'))
        {
            if($this->houseSimService->hasInventory('Cobweb') && $this->houseSimService->hasInventory('Fiberglass Bow'))
                $possibilities[] = new ActivityCallback($this, 'createBugBow', 10);

            if($this->houseSimService->hasInventory('Alien Tissue'))
                $possibilities[] = new ActivityCallback($this, 'createProboscis', 10);
        }

        if($this->houseSimService->hasInventory('String'))
        {
            if($this->houseSimService->hasInventory('Naner'))
                $possibilities[] = new ActivityCallback($this, 'createBownaner', 10);

            if($this->houseSimService->hasInventory('Glass'))
                $possibilities[] = new ActivityCallback($this, 'createGlassPendulum', 10);

            if($this->houseSimService->hasInventory('Paper') && $this->houseSimService->hasInventory('Silver Key'))
                $possibilities[] = new ActivityCallback($this, 'createBenjaminFranklin', 10);

            if($this->houseSimService->hasInventory('Really Big Leaf'))
                $possibilities[] = new ActivityCallback($this, 'createLeafSpear', 10);

            if($this->houseSimService->hasInventory('L-Square') && $this->houseSimService->hasInventory('Green Dye'))
                $possibilities[] = new ActivityCallback($this, 'createRibbelysComposite', 10);

            if(
                $this->houseSimService->hasInventory('Small Plastic Bucket') ||
                $this->houseSimService->hasInventory('Small, Yellow Plastic Bucket')
            )
            {
                $possibilities[] = new ActivityCallback($this, 'createShortRangeTelephone', 10);
            }

            if($this->houseSimService->hasInventory('"Rustic" Magnifying Glass') && $this->houseSimService->hasInventory('Black Feathers'))
                $possibilities[] = new ActivityCallback($this, 'createCrowsEye', 10);
        }

        if($this->houseSimService->hasInventory('Bownaner') && $this->houseSimService->hasInventory('Carrot'))
            $possibilities[] = new ActivityCallback($this, 'createEatYourFruitsAndVeggies', 10);

        if($this->houseSimService->hasInventory('Feathers'))
        {
            if($this->houseSimService->hasInventory('Hunting Spear'))
                $possibilities[] = new ActivityCallback($this, 'createDecoratedSpear', 10);

            if($this->houseSimService->hasInventory('Yellow Dye'))
            {
                if($this->houseSimService->hasInventory('Fiberglass Pan Flute'))
                    $possibilities[] = new ActivityCallback($this, 'createOrnatePanFlute', 10);

                if($this->houseSimService->hasInventory('Tea Trowel'))
                    $possibilities[] = new ActivityCallback($this, 'createOwlTrowel', 10);
            }
        }

        if($this->houseSimService->hasInventory('White Feathers') && $this->houseSimService->hasInventory('Leaf Spear'))
            $possibilities[] = new ActivityCallback($this, 'createFishingRecorder', 10);

        if($this->houseSimService->hasInventory('Decorated Spear'))
        {
            if($this->houseSimService->hasInventory('Dark Scales'))
                $possibilities[] = new ActivityCallback($this, 'createNagatooth', 10);

            if($this->houseSimService->hasInventory('Quintessence'))
                $possibilities[] = new ActivityCallback($this, 'createVeilPiercer', 10);
        }

        if($this->houseSimService->hasInventory('Crooked Fishing Rod'))
        {
            if($this->houseSimService->hasInventory('Yellow Dye') && $this->houseSimService->hasInventory('Green Dye'))
                $possibilities[] = new ActivityCallback($this, 'createPaintedFishingRod', 10);

            if($this->houseSimService->hasInventory('Carrot'))
                $possibilities[] = new ActivityCallback($this, 'createCaroteneStick', 10);
        }

        if($this->houseSimService->hasInventory('Plastic Boomerang') && $this->houseSimService->hasInventory('Quinacridone Magenta Dye'))
            $possibilities[] = new ActivityCallback($this, 'createPaintedBoomerang', 10);

        if($this->houseSimService->hasInventory('Yellow Dye'))
        {
            if($this->houseSimService->hasInventory('Plastic Idol'))
                $possibilities[] = new ActivityCallback($this, 'createGoldIdol', 10);
            else
            {
                if($this->houseSimService->hasInventory('Small Plastic Bucket'))
                    $possibilities[] = new ActivityCallback($this, 'createYellowBucket', 10);

                if($this->houseSimService->hasInventory('Dumbbell'))
                    $possibilities[] = new ActivityCallback($this, 'createPaintedDumbbell', 10);

                if($this->houseSimService->hasInventory('Digital Camera'))
                    $possibilities[] = new ActivityCallback($this, 'createPaintedCamera', 10);
            }
        }

        if($this->houseSimService->hasInventory('Fiberglass'))
            $possibilities[] = new ActivityCallback($this, 'createSimpleFiberglassItem', 10);

        if($this->houseSimService->hasInventory('Scythe'))
        {
            if($this->houseSimService->hasInventory('Scythe', 2))
                $possibilities[] = new ActivityCallback($this, 'createDoubleScythe', 10);

            if($this->houseSimService->hasInventory('Garden Shovel'))
                $possibilities[] = new ActivityCallback($this, 'createFarmersMultiTool', 10);
        }

        if($this->houseSimService->hasInventory('Garden Shovel') && $this->houseSimService->hasInventory('Fish Bones'))
            $possibilities[] = new ActivityCallback($this, 'createFishHeadShovel', 10);

        if($this->houseSimService->hasInventory('White Flag'))
        {
            if($this->houseSimService->hasInventory('Yellow Dye'))
                $possibilities[] = new ActivityCallback($this, 'createSunFlag', 10);

            if($this->houseSimService->hasInventory('Green Dye'))
                $possibilities[] = new ActivityCallback($this, 'createDragonFlag', 10);

            if($this->houseSimService->hasInventory('String') && $this->houseSimService->hasInventory('Crooked Stick'))
                $possibilities[] = new ActivityCallback($this, 'createBindle', 10);

            if($this->houseSimService->hasInventory('Crooked Fishing Rod'))
                $possibilities[] = new ActivityCallback($this, 'createBindle2', 10);
        }

        if($this->houseSimService->hasInventory('Sun Flag') && $this->houseSimService->hasInventory('Sunflower Stick'))
            $possibilities[] = new ActivityCallback($this, 'createSunSunFlag', 10);

        if($this->houseSimService->hasInventory('Plastic'))
        {
            if($this->houseSimService->hasInventory('Smallish Pumpkin') && $this->houseSimService->hasInventory('Crooked Stick'))
                $possibilities[] = new ActivityCallback($this, 'createDrumpkin', 10);

            if($this->houseSimService->hasInventory('Iron Bar'))
                $possibilities[] = new ActivityCallback($this, 'createGrabbyArm', 10);
        }

        if($this->houseSimService->hasInventory('Rice Flour') && $this->houseSimService->hasInventory('Potato'))
            $possibilities[] = new ActivityCallback($this, 'createRicePaper', 10);

        $repairWeight = ($petWithSkills->getSmithingBonus()->getTotal() >= 3 || $petWithSkills->getCrafts()->getTotal() >= 5) ? 10 : 1;

        if($this->houseSimService->hasInventory('Rusty Blunderbuss'))
            $possibilities[] = new ActivityCallback($this, 'repairRustyBlunderbuss', $repairWeight);

        if($this->houseSimService->hasInventory('Rusty Rapier'))
            $possibilities[] = new ActivityCallback($this, 'repairRustyRapier', $repairWeight);

        if($this->houseSimService->hasInventory('Rusted, Busted Mechanism'))
            $possibilities[] = new ActivityCallback($this, 'repairOldMechanism', $repairWeight);

        if($this->houseSimService->hasInventory('Sun-sun Flag', 2))
            $possibilities[] = new ActivityCallback($this, 'createSunSunFlagFlagSon', 10);

        if($this->houseSimService->hasInventory('Moon Pearl'))
        {
            if($this->houseSimService->hasInventory('Plastic Fishing Rod') && $this->houseSimService->hasInventory('Talon'))
                $possibilities[] = new ActivityCallback($this, 'createPaleFlail', 10);
        }

        if($this->houseSimService->hasInventory('Blue Balloon') && $this->houseSimService->hasInventory('Gold Telescope'))
            $possibilities[] = new ActivityCallback($this, 'makeSpyBalloon', 10);

        return array_merge($possibilities, $this->eventLanternService->getCraftingPossibilities($petWithSkills));
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

        $changes = new PetChanges($petWithSkills->getPet());

        /** @var PetActivityLog $activityLog */
        $activityLog = ($method->callable)($petWithSkills);

        if($activityLog)
        {
            $activityLog->setChanges($changes->compare($petWithSkills->getPet()));
        }

        return $activityLog;
    }

    public function createTorchFromBone(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 8)
        {
            $this->houseSimService->getState()->loseItem('White Cloth', 1);
            $this->houseSimService->getState()->loseItem('Stereotypical Bone', 1);
            $pet->increaseEsteem(2);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Stereotypical Torch.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::CRAFT, true);

            $this->inventoryService->petCollectsItem('Stereotypical Torch', $pet, $pet->getName() . ' created this from White Cloth and a Stereotypical Bone.', $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Stereotypical Torch, but couldn\'t figure it out.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 45), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createTeaTrowel(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 12)
        {
            $this->houseSimService->getState()->loseItem('Tea Leaves', 1);
            $this->houseSimService->getState()->loseItem('Trowel', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Tea Trowel.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Tea Trowel', $pet, $pet->getName() . ' made this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Tea Trowel... or was it a Tea _Towel?_ It\'s all very confusing.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 45), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createOwlTrowel(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 16)
        {
            $this->houseSimService->getState()->loseItem('Tea Trowel', 1);
            $this->houseSimService->getState()->loseItem('Feathers', 1);
            $this->houseSimService->getState()->loseItem('Yellow Dye', 1);
            $pet->increaseEsteem($this->squirrel3->rngNextInt(2, 4));
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created an Owl Trowel.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Owl Trowel', $pet, $pet->getName() . ' made this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make an Owl Trowel, but couldn\'t figure it out.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 45), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createSimpleFiberglassItem(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        $item = $this->squirrel3->rngNextFromArray([
            'Fiberglass Flute'
        ]);

        if($roll <= 2)
        {
            $this->houseSimService->getState()->loseItem('Fiberglass', 1);
            $pet->increaseSafety(-4);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a ' . $item . ', but accidentally cut themselves on the Fiberglass! :(', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 14)
        {
            $this->houseSimService->getState()->loseItem('Fiberglass', 1);

            $pet->increaseEsteem(2);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a ' . $item . ' from Fiberglass.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' created this from Fiberglass.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a ' . $item . ', but the Fiberglass wasn\'t cooperating.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createDecoratedFlute(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $this->houseSimService->getState()->loseItem('White Cloth', 1);
            $pet->increaseEsteem(-2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried decorate a Flute, but while trying to make ribbons, accidentally tore the White Cloth to shreds...', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 18)
        {
            $this->houseSimService->getState()->loseItem('White Cloth', 1);
            $this->houseSimService->getState()->loseItem('Flute', 1);
            $this->houseSimService->getState()->loseItem('Glass Pendulum', 1);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Decorated Flute.', 'items/tool/instrument/flute-decorated')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Decorated Flute', $pet, $pet->getName() . ' created this by tying a Glass Pendulum to a Flute.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% thought it might be cool to decorate a Flute, but couldn\'t think of something stylish enough.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createDrumpkin(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2 && $pet->getFood() < 4)
        {
            $this->houseSimService->getState()->loseItem('Smallish Pumpkin', 1);
            $pet->increaseFood(6);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Drumpkin, but broke the Smallish Pumpkin :( Not wanting to waste it, %pet:' . $pet->getId() . '.name% ate the remains...)', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting', 'Smithing', 'Eating' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 15)
        {
            $this->houseSimService->getState()->loseItem('Plastic', 1);
            $this->houseSimService->getState()->loseItem('Smallish Pumpkin', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Drumpkin!', 'items/tool/instrument/drumpkin')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting', 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Drumpkin', $pet, $pet->getName() . ' created this!', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Drumpkin, but couldn\'t get the Plastic thin enough...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting', 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createRicePaper(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2 && $pet->getFood() < 4)
        {
            $this->houseSimService->getState()->loseItem('Potato', 1);
            $pet->increaseFood(4);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make Paper, but messed up the Potato :( (Not wanting to waste it, %pet:' . $pet->getId() . '.name% ate the remains...)', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting', 'Eating' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 15)
        {
            $paperCount = 2;

            if($roll >= 20) $paperCount++;
            if($roll >= 25) $paperCount++;

            $this->houseSimService->getState()->loseItem('Rice Flour', 1);
            $this->houseSimService->getState()->loseItem('Potato', 1);
            $pet->increaseEsteem($paperCount * 2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created ' . $paperCount . ' Paper!', 'items/resource/paper')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 5 + $paperCount * 5)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            for($i = 0; $i < $paperCount; $i++)
                $this->inventoryService->petCollectsItem('Paper', $pet, $pet->getName() . ' created this!', $activityLog);

            $this->petExperienceService->gainExp($pet, $paperCount, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 55 + $paperCount * 5), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make Paper, but almost wasted the Rice Flour...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createDecoratedSpear(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 12)
        {
            $this->houseSimService->getState()->loseItem('Feathers', 1);
            $this->houseSimService->getState()->loseItem('Hunting Spear', 1);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Decorated Spear.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Decorated Spear', $pet, $pet->getName() . ' decorated a Hunting Spear with Feathers to make this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 30), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to decorate a Hunting Spear with Feathers, but couldn\'t get the look just right.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 30), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createFishingRecorder(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getMusic()->getTotal());

        if($roll >= 14)
        {
            $this->houseSimService->getState()->loseItem('Leaf Spear', 1);
            $this->houseSimService->getState()->loseItem('White Feathers', 1);
            $pet->increaseEsteem(4);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Fishing Recorder.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            if($this->squirrel3->rngNextInt(1, 5) === 1)
                $this->inventoryService->petCollectsItem('Fishing Recorder', $pet, $pet->getName() . ' made this. (The White Feathers are a nice touch, don\'t you think?)', $activityLog);
            else
                $this->inventoryService->petCollectsItem('Fishing Recorder', $pet, $pet->getName() . ' made this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::MUSIC ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to make a Fishing Recorder, but couldn\'t figure out where all the holes should go...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::MUSIC ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createDoubleScythe(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

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

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Double Scythe; ' . $and, 'items/tool/scythe/double')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->inventoryService->petCollectsItem('Double Scythe', $pet, $pet->getName() . ' created this by taking the blade from one Scythe and attaching it to another.', $activityLog);
            $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' "made" this by taking the blade off of a Scythe (to make a Double Scythe).', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% thought it might be cool to make a Double Scythe, but then doubted themself...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createFishHeadShovel(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 14)
        {
            $this->houseSimService->getState()->loseItem('Fish Bones', 1);
            $this->houseSimService->getState()->loseItem('Garden Shovel', 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Fish Head Shovel!', 'items/tool/shovel/fish-head')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->inventoryService->petCollectsItem('Fish Head Shovel', $pet, $pet->getName() . ' created this by adorning a Garden Shovel with some Fish Bones.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            if($this->squirrel3->rngNextInt(1, 10) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to make a Fish Head Shovel, but couldn\'t figure out how to arrange the bones #relatable', 'icons/activity-logs/confused')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
                ;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to make a Fish Head Shovel, but couldn\'t figure out how to arrange the bones...', 'icons/activity-logs/confused')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createFarmersMultiTool(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 14)
        {
            $this->houseSimService->getState()->loseItem('Scythe', 1);
            $this->houseSimService->getState()->loseItem('Garden Shovel', 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Farmer\'s Multi-tool!', 'items/tool/shovel/multi-tool')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->inventoryService->petCollectsItem('Farmer\'s Multi-tool', $pet, $pet->getName() . ' created this by combining the best parts of a Scythe and a Garden Shovel.', $activityLog);
            $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' had this left over after making a Farmer\'s Multi-tool.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to combine a Scythe and a Garden Shovel, but couldn\'t decide which of the two tools to start with?', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
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

        if($roll >= $difficulty)
        {
            $spunWhat = $this->houseSimService->getState()->loseOneOf([ 'Fluff', 'Cobweb' ]);

            if($roll >= $difficulty + 12)
            {
                $pet->increaseEsteem(3);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% spun some ' . $spunWhat . ' into TWO ' . $making->getName() . '!', 'items/' . $making->getImage())
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + $difficulty + 12)
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
                ;

                $this->inventoryService->petCollectsItem($making, $pet, $pet->getName() . ' spun this from ' . $spunWhat . '.', $activityLog);

                $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);
            }
            else
            {
                $pet->increaseEsteem(1);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% spun some ' . $spunWhat . ' into ' . $making->getName() . '.', 'items/' . $making->getImage())
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + $difficulty)
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
                ;

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            }

            $this->inventoryService->petCollectsItem($making, $pet, $pet->getName() . ' spun this from ' . $spunWhat . '.', $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to spin some ' . $making->getName() . ', but couldn\'t figure it out.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
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

        if($roll <= 2 && $pet->getFood() < 4)
        {
            $this->houseSimService->getState()->loseItem('Chocolate Bar', 1);

            $pet->increaseFood($this->squirrel3->rngNextInt(2, 4));
            $pet->increaseEsteem(-2);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started making ' . $making->getNameWithArticle() . ', but ended up eating the Chocolate Bar, instead >_>', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting', 'Eating' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 10)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Chocolate Bar', 1);

            $makeTwo = $roll >= 20 && $making->getName() === 'Chocolate Key';

            if($makeTwo)
            {
                $pet->increaseEsteem(4);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% molded a Chocolate Bar into TWO ' . $making->getName() . 's!', 'items/' . $making->getImage())
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
                ;

                $this->inventoryService->petCollectsItem($making, $pet, $pet->getName() . ' made this out of a Chocolate Bar.', $activityLog);

                $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ], $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% molded a Chocolate Bar into ' . $making->getNameWithArticle() . '.', 'items/' . $making->getImage())
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
                ;

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            }

            $this->inventoryService->petCollectsItem($making, $pet, $pet->getName() . ' made this out of a Chocolate Bar.', $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make ' . $making->getNameWithArticle() . ', but couldn\'t get the mold right...', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createYellowDyeFromTeaLeaves(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 4)
        {
            $this->houseSimService->getState()->loseItem('Tea Leaves', 1);

            $message = $pet->getName() . ' tried to extract Yellow Dye from Tea Leaves, but accidentally made Black Tea, instead!';

            if($this->squirrel3->rngNextInt(1, 10) === 1)
                $message .= ' (Aren\'t the leaves themselves green? Where are all these colors coming from?!)';

            $activityLog = $this->responseService->createActivityLog($pet, $message, '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Black Tea', $pet, $pet->getName() . ' accidentally made this while trying to extract Yellow Dye from Tea Leaves.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Tea Leaves', 1);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% extracted Yellow Dye from some Tea Leaves.', 'items/resource/dye-yellow')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Yellow Dye', $pet, $pet->getName() . ' extracted this from Tea Leaves.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to extract Yellow Dye from some Tea Leaves, but wasn\'t sure how to start.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function extractFromScales(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getCrafts()->getTotal());
        $itemName = $this->squirrel3->rngNextInt(1, 2) === 1 ? 'Green Dye' : 'Glue';

        if($roll >= 20)
        {
            $this->houseSimService->getState()->loseItem('Scales');
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% extracted Green Dye _and_ Glue from some Scales!', 'items/animal/scales')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Green Dye', $pet, $pet->getName() . ' extracted this from Scales.', $activityLog);
            $this->inventoryService->petCollectsItem('Glue', $pet, $pet->getName() . ' extracted this from Scales.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 90), PetActivityStatEnum::CRAFT, true);
        }
        else if($roll >= 12)
        {
            $this->houseSimService->getState()->loseItem('Scales');
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% extracted ' . $itemName . ' from some Scales.', 'items/animal/scales')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' extracted this from Scales.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to extract ' . $itemName . ' from some Scales, but wasn\'t sure how to start.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function createFabricMache(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 14)
        {
            $possibleItems = [
                'Fabric Mâché Basket'
            ];

            $item = $this->squirrel3->rngNextFromArray($possibleItems);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('White Cloth', 1);
            $this->houseSimService->getState()->loseItem('Glue', 1);
            $pet->increaseEsteem(2);

            if($item === 'Fabric Mâché Basket' && $this->squirrel3->rngNextInt(1, 10) === 1)
            {
                $transformation = $this->squirrel3->rngNextFromArray([
                    [ 'item' => 'Flower Basket', 'goodies' => 'flowers' ],
                    [ 'item' => 'Fruit Basket', 'goodies' => 'fruit' ],
                ]);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Fabric Mâché Basket. Once they were done, a fairy appeared out of nowhere, and filled the basket up with ' . $transformation['goodies'] . '!', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting', 'Fae-kind' ]))
                ;
                $this->inventoryService->petCollectsItem($transformation['item'], $pet, $pet->getName() . ' created a Fabric Mâché Basket; once they were done, a fairy appeared and filled it with ' . $transformation['goodies'] . '!', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a ' . $item . '.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
                ;
                $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' created this from White Cloth and Glue.', $activityLog);
            }

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make some Fabric Mâché, but couldn\'t come up with a good pattern.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function createGoldTrifecta(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Gold Triangle', 3);
            $this->houseSimService->getState()->loseItem('Glue', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Gold Trifecta.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Gold Trifecta', $pet, $pet->getName() . ' created by gluing together three Gold Triangles.', $activityLog);

            if($this->squirrel3->rngNextInt(1, 2) === 1) $this->inventoryService->petCollectsItem('String', $pet, $pet->getName() . ' recovered this when creating a Gold Trifecta.', $activityLog);
            if($this->squirrel3->rngNextInt(1, 2) === 1) $this->inventoryService->petCollectsItem('String', $pet, $pet->getName() . ' recovered this when creating a Gold Trifecta.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog =  $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to make a Gold Trifecta, but wasn\'t sure how to begin...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createLSquare(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll == 1)
        {
            $this->houseSimService->getState()->loseItem('Ruler', 1);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make an L-Square, but accidentally snapped one of the Rulers in two! :|', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Ruler', 2);
            $this->houseSimService->getState()->loseItem('Glue', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created an L-Square.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('L-Square', $pet, $pet->getName() . ' created by gluing together a couple Rulers.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to make an L-Square, but spent forever trying to make it _exactly_ 90 degrees, and eventually gave up...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createAlienCookingBuddy(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 10)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Cooking Buddy', 1);
            $this->houseSimService->getState()->loseItem('Glue', 1);
            $this->houseSimService->getState()->loseItem('Antenna', 1);
            $pet
                ->increaseEsteem(4)
                ->increaseSafety(2)
            ;
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% cracked themselves up by creating a Cooking "Alien".', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Cooking "Alien"', $pet, $pet->getName() . ' created by gluing some Antennae on a Cooking Buddy.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to do something silly to a Cooking Buddy, but couldn\'t decide what...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createAlienCamera(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 10)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Painted Camera', 1);
            $this->houseSimService->getState()->loseItem('Glue', 1);
            $this->houseSimService->getState()->loseItem('Antenna', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% put together an "Alien" Camera!', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('"Alien" Camera', $pet, $pet->getName() . ' created by gluing some Antennae to a Painted Camera.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to do something silly to a Painted Camera, but couldn\'t decide what...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createBugBow(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getNature()->getTotal(), $petWithSkills->getCrafts()->getTotal()));

        if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Fiberglass Bow', 1);
            $this->houseSimService->getState()->loseItem('Cobweb', 1);
            $this->houseSimService->getState()->loseItem('Antenna', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% turned a boring ol\' Fiberglass Bow into a Bug Bow!', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Bug Bow', $pet, $pet->getName() . ' created this out of a Fiberglass Bow and some bug bits.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started making a Bug Bow, but kept getting stuck to the Cobweb.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createProboscis(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 +
            $petWithSkills->getIntelligence()->getTotal() +
            $petWithSkills->getDexterity()->getTotal() +
            $petWithSkills->getCrafts()->getTotal()
        );

        if($roll >= 26)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Antenna', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% spun a Proboscis from Alien Tissue and Antenna, and there was still plenty of Alien Tissue left over!', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Proboscis', $pet, $pet->getName() . ' created this out of Alien Tissue and some bug bits.', $activityLog);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Alien Tissue', 1);
            $this->houseSimService->getState()->loseItem('Antenna', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% spun a Proboscis from Alien Tissue and Antenna!', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Proboscis', $pet, $pet->getName() . ' created this out of Alien Tissue and some bug bits.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Proboscis, but had trouble spinning the Alien Tissue.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createFiberglassPanFlute(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getMusic()->getTotal(), $petWithSkills->getCrafts()->getTotal()));

        if($roll == 1)
        {
            $this->houseSimService->getState()->loseItem('White Cloth', 1);
            $pet->increaseEsteem(-2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to make a Fiberglass Pan Flute, but while making ribbons, accidentally tore the White Cloth to shreds...', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);

            $this->houseSimService->getState()->loseItem('White Cloth', 1);
            $this->houseSimService->getState()->loseItem('Glue', 1);
            $this->houseSimService->getState()->loseItem('Fiberglass Flute', 1);

            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Fiberglass Pan Flute.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Fiberglass Pan Flute', $pet, $pet->getName() . ' created this by hacking a Fiberglass Flute into several pieces, and gluing them together with a ribbon of cloth.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to make a Fiberglass Pan Flute, but didn\'t feel confident about cutting the Fiberglass Flute in half.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createGlassPendulum(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $pet->increaseSafety(-4);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to cut a piece of glass, but cut themselves, instead! :(', 'icons/activity-logs/wounded')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Glass', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% cut some Glass to look like a gem, and made a Glass Pendulum.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Glass Pendulum', $pet, $pet->getName() . ' created this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $pet->increaseSafety(-1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Glass Pendulum, but almost cut themselves on the glass, and gave up.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createBownaner(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() * 2 + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2 && $pet->getFood() < 4)
        {
            $this->houseSimService->getState()->loseItem('Naner', 1);
            $pet->increaseFood(4);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make a bow out of a Naner, but was feeling hungry, so... they ate the Naner.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting', 'Eating' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 11)
        {
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Naner', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a makeshift bow... out of a Naner.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 11)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Bownaner', $pet, $pet->getName() . ' created this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Bownaner, but the String kept getting all tangled.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createEatYourFruitsAndVeggies(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $weather = $this->weatherService->getWeather(new \DateTimeImmutable(), $pet);
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() * 2 + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2 && $pet->getFood() < 4)
        {
            $this->houseSimService->getState()->loseItem('Carrot', 1);
            $pet->increaseFood(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make an Eat Your Fruits And Veggies, but was feeling hungry, so... they ate the Carrot.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting', 'Eating' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Carrot', 1);
            $this->houseSimService->getState()->loseItem('Bownaner', 1);
            $pet->increaseEsteem(2);

            if($roll >= 22 || $weather->isHoliday(HolidayEnum::EASTER))
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made an Eat Your Fruits and Veggies, and even had enough Carrot left over to make a Carrot Key!', '')
                    ->addInterestingness($weather->isHoliday(HolidayEnum::EASTER) ? PetActivityLogInterestingnessEnum::HOLIDAY_OR_SPECIAL_EVENT : (PetActivityLogInterestingnessEnum::HO_HUM + 22))
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting', 'Special Event', 'Easter' ]))
                ;

                $this->inventoryService->petCollectsItem('Carrot Key', $pet, $pet->getName() . ' made this.', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% loaded a Bownaner with a Carrot...', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 12)
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
                ;
            }

            $this->inventoryService->petCollectsItem('Eat Your Fruits and Veggies', $pet, $pet->getName() . ' created this.', $activityLog);

            $this->petExperienceService->gainExp($pet, $roll >= 22 ? 3 : 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to load a Bownaner with a Carrot, but the String kept getting all tangled.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createLeafSpear(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getStrength()->getTotal() * 2 + $petWithSkills->getDexterity()->getTotal());

        if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Really Big Leaf', 1);
            $pet->increaseEsteem(2);

            $message = $pet->getName() . ' rolled up a Really Big Leaf, and tied it, creating a Leaf Spear!';

            if($this->squirrel3->rngNextInt(1, 5) > $petWithSkills->getStrength()->getTotal())
                $message .= ' (It\'s harder than it looks!)';

            $activityLog = $this->responseService->createActivityLog($pet, $message, '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Leaf Spear', $pet, $pet->getName() . ' created this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $pet->increaseSafety(-1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Leaf Spear, but the Really Big Leaf is surprisingly strong! ' . $pet->getName() . ' couldn\'t get it to roll up...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createBenjaminFranklin(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getScience()->getTotal()));

        if($roll >= 17)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Paper', 1);
            $this->houseSimService->getState()->loseItem('Silver Key', 1);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created Benjamin Franklin. (A kite, not the person.)', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 17)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting', 'Physics' ]))
            ;
            $this->inventoryService->petCollectsItem('Benjamin Franklin', $pet, $pet->getName() . ' created this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a kite, but couldn\'t come up with a good design...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting', 'Physics' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createShortRangeTelephone(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $this->houseSimService->getState()->loseItem('String', 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make a Short-range Telephone, but accidentally broke the String! :(', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 45), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $bucketType = $this->houseSimService->getState()->loseOneOf([ 'Small Plastic Bucket', 'Small, Yellow Plastic Bucket' ]);
            $this->houseSimService->getState()->loseItem('String', 1);
            $pet->increaseEsteem(2);

            if($bucketType === 'Small, Yellow Plastic Bucket' && $roll >= 20)
            {
                $extra = $this->squirrel3->rngNextInt(1, 10) === 1
                    ? ' (That\'s totally how dye works! Yep!)'
                    : ''
                ;

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Short-range Telephone, and was even able to squeeze the Yellow Dye out of the Small, Yellow Plastic Bucket they used.' . $extra, '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
                ;

                $this->inventoryService->petCollectsItem('Yellow Dye', $pet, $pet->getName() . ' recovered this from a Small, Yellow Plastic Bucket while making a Short-range Telephone', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Short-range Telephone. They tried to extract the Yellow Dye from the Small, Yellow Plastic Bucket they used, but wasn\'t able to recover a useful amount of it.', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
                ;
            }

            $this->inventoryService->petCollectsItem('Short-range Telephone', $pet, $pet->getName() . ' created this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a bow out of an L-Square, but, ironically, couldn\'t get the measurements right.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createCrowsEye(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $this->houseSimService->getState()->loseItem('String', 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make a Crow\'s Eye, but accidentally broke the String they were trying to use! :(', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 45), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 20)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('"Rustic" Magnifying Glass', 1);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Black Feathers', 1);
            $pet->increaseEsteem(3);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Crow\'s Eye... with Roadkill?!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $roadkill = $this->spiceRepository->findOneByName('with Roadkill');

            $this->inventoryService->petCollectsEnhancedItem('Crow\'s Eye', null, $roadkill, $pet, $pet->getName() . ' created this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Crow\'s Eye, but felt they were missing a certain... je ne sais quoi.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createRibbelysComposite(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Green Dye', 1);
            $this->houseSimService->getState()->loseItem('L-Square', 1);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created Ribbely\'s Composite.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Ribbely\'s Composite', $pet, $pet->getName() . ' created this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a bow out of an L-Square, but, ironically, couldn\'t get the measurements right.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createFeatheredHat(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('White Cloth', 1);
            $this->houseSimService->getState()->loseItem('Ruby Feather', 1);
            $pet->increaseEsteem(5);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made an Afternoon Hat by shaping some White Cloth, and tying a Ruby Feather to it!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 17)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Afternoon Hat', $pet, $pet->getName() . ' created this!', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to make a hat, but couldn\'t come up with a good design...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createOrnatePanFlute(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getMusic()->getTotal()));

        if($roll >= 18)
        {
            $this->houseSimService->getState()->loseItem('Fiberglass Pan Flute', 1);
            $this->houseSimService->getState()->loseItem('Yellow Dye', 1);
            $this->houseSimService->getState()->loseItem('Feathers', 1);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created an Ornate Pan Flute.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Ornate Pan Flute', $pet, $pet->getName() . ' created this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS, PetSkillEnum::MUSIC ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to decorate a Fiberglass Pan Flute, but couldn\'t come up with a good design.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::MUSIC ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createSnakebite(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $pet->increaseEsteem(-2);
            $pet->increaseSafety(-4);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to craft Snakebite, but cut themself on a Talon!', 'icons/activity-logs/wounded')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Talon', 1);
            $this->houseSimService->getState()->loseItem('Scales', 1);
            $this->houseSimService->getState()->loseItem('Wooden Sword', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Snakebite sword.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Snakebite', $pet, $pet->getName() . ' made this by improving a Wooden Sword.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to improve a Wooden Sword into Snakebite, but failed.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createVeilPiercer(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $umbraCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getUmbra()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($umbraCheck < 15)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to enchant a Decorated Spear, but couldn\'t get an enchantment to stick.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }
        else // success!
        {
            $veilPiercer = $this->itemRepository->findOneByName('Veil-piercer');

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Decorated Spear', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% enchanted a Decorated Spear to be ' . $veilPiercer->getNameWithArticle() . '.', 'items/' . $veilPiercer->getImage())
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem($veilPiercer, $pet, $pet->getName() . ' made this by enchanting a Decorated Spear.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ], $activityLog);
        }

        return $activityLog;
    }

    private function createNagatooth(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $craftsCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($craftsCheck < 15)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried decorate a Decorated Spear _even more_, but couldn\'t get the pattern just right...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Dark Scales', 1);
            $this->houseSimService->getState()->loseItem('Decorated Spear', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% further decorated a Decorated Spear; now it\'s a Nagatooth!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Nagatooth', $pet, $pet->getName() . ' made this by further decorating a Decorated Spear.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }

        return $activityLog;
    }

    private function createLassoscope(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $craftsCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal());

        if($craftsCheck < 20)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to make a Lassoscope, but couldn\'t successfully lasso a Gold Telescope...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Flying Grappling Hook', 1);
            $this->houseSimService->getState()->loseItem('Gold Telescope', 1);
            $pet->increaseEsteem($this->squirrel3->rngNextInt(4, 8));

            $safelyExamineLions = $this->squirrel3->rngNextInt(1, 10) === 1;

            $activityLogMessage = $safelyExamineLions
                ? $pet->getName() . ' made a Lassoscope! (Now they can safely examine lions!)'
                : $pet->getName() . ' made a Lassoscope!'
            ;

            $activityLog = $this->responseService->createActivityLog($pet, $activityLogMessage, '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
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
        $weather = $this->weatherService->getWeather(new \DateTimeImmutable(), $pet);
        $craftsCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal());

        if($craftsCheck < 14)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Carrot lure for a Crooked Fishing Rod, but couldn\'t figure out how best to go about it...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Crooked Fishing Rod', 1);
            $this->houseSimService->getState()->loseItem('Carrot', 1);

            if($craftsCheck >= 25 || $weather->isHoliday(HolidayEnum::EASTER))
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Carotene Stick, and even had enough Carrot left over to make a Carrot Key!', '')
                    ->addInterestingness($weather->isHoliday(HolidayEnum::EASTER) ? PetActivityLogInterestingnessEnum::HOLIDAY_OR_SPECIAL_EVENT : (PetActivityLogInterestingnessEnum::HO_HUM + 25))
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting', 'Special Event', 'Easter' ]))
                ;

                $this->inventoryService->petCollectsItem('Carrot Key', $pet, $pet->getName() . ' made this.', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Carrot Lure for a Crooked Fishing Rod; now it\'s a Carotene Stick!', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
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

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 90), PetActivityStatEnum::CRAFT, true);
        $this->houseSimService->getState()->loseItem('Crooked Fishing Rod', 1);
        $this->houseSimService->getState()->loseItem('Yellow Dye', 1);
        $this->houseSimService->getState()->loseItem('Green Dye', 1);
        $pet->increaseEsteem(1);
        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Painted Fishing Rod.', '')
            ->addTags($this->petActivityLogTagRepository->findByNames([ 'Painting' ]))
        ;
        $this->inventoryService->petCollectsItem('Painted Fishing Rod', $pet, $pet->getName() . ' painted this, using Yellow and Green Dye.', $activityLog);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        return $activityLog;
    }

    private function createPaintedBoomerang(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 90), PetActivityStatEnum::CRAFT, true);
        $this->houseSimService->getState()->loseItem('Plastic Boomerang', 1);
        $this->houseSimService->getState()->loseItem('Quinacridone Magenta Dye', 1);
        $pet->increaseEsteem(3);
        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Painted Boomerang.', '')
            ->addTags($this->petActivityLogTagRepository->findByNames([ 'Painting' ]))
        ;
        $this->inventoryService->petCollectsItem('Painted Boomerang', $pet, $pet->getName() . ' painted this, using Quinacridone Magenta Dye.', $activityLog);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        return $activityLog;
    }

    private function createGoldIdol(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 90), PetActivityStatEnum::CRAFT, true);
        $this->houseSimService->getState()->loseItem('Plastic Idol', 1);
        $this->houseSimService->getState()->loseItem('Yellow Dye', 1);
        $pet->increaseEsteem(1);
        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a "Gold" Idol.', '')
            ->addTags($this->petActivityLogTagRepository->findByNames([ 'Painting' ]))
        ;
        $this->inventoryService->petCollectsItem('"Gold" Idol', $pet, $pet->getName() . ' painted this, using Yellow Dye.', $activityLog);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        return $activityLog;
    }

    private function createYellowBucket(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 30), PetActivityStatEnum::CRAFT, true);
        $this->houseSimService->getState()->loseItem('Small Plastic Bucket', 1);
        $this->houseSimService->getState()->loseItem('Yellow Dye', 1);
        $pet->increaseEsteem(1);
        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% dunked a Small Plastic Bucket into some Yellow Dye.', '')
            ->addTags($this->petActivityLogTagRepository->findByNames([ 'Painting' ]))
        ;
        $this->inventoryService->petCollectsItem('Small, Yellow Plastic Bucket', $pet, $pet->getName() . ' "painted" this, using Yellow Dye.', $activityLog);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        return $activityLog;
    }

    private function createPaintedDumbbell(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::CRAFT, true);
        $this->houseSimService->getState()->loseItem('Dumbbell', 1);
        $this->houseSimService->getState()->loseItem('Yellow Dye', 1);
        $pet->increaseEsteem(1);

        if($this->squirrel3->rngNextInt(1, 10) === 1)
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% painted emojis on a Dumbbell. (That makes them better, right?)', '');
        else
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% painted emojis on a Dumbbell.', '');

        $activityLog->addTags($this->petActivityLogTagRepository->findByNames([ 'Painting' ]));

        $this->inventoryService->petCollectsItem('Painted Dumbbell', $pet, $pet->getName() . ' painted this, using Yellow Dye.', $activityLog);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);

        return $activityLog;
    }

    private function createPaintedCamera(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::CRAFT, true);
        $this->houseSimService->getState()->loseItem('Digital Camera', 1);
        $this->houseSimService->getState()->loseItem('Yellow Dye', 1);
        $pet->increaseEsteem(1);

        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% painted a face on a Digital Camera!', '');

        $activityLog->addTags($this->petActivityLogTagRepository->findByNames([ 'Painting' ]));

        $this->inventoryService->petCollectsItem('Painted Camera', $pet, $pet->getName() . ' painted this, using Yellow Dye.', $activityLog);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);

        return $activityLog;
    }

    private function repairRustyBlunderbuss(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getSmithingBonus()->getTotal()));

        if($roll >= 18)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Rusty Blunderbuss', 1);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% repaired a Rusty Blunderbuss. It\'s WAY less rusty now!', 'items/tool/gun/blunderbuss')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting', 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Blunderbuss', $pet, $pet->getName() . ' repaired this Rusty Blunderbuss.', $activityLog);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% spent a while trying to repair a Rusty Blunderbuss, but wasn\'t able to make any progress.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting', 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function repairRustyRapier(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getSmithingBonus()->getTotal()));

        if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Rusty Rapier', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% repaired a Rusty Rapier. It\'s WAY less rusty now!', 'items/tool/sword/rapier')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting', 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Rapier', $pet, $pet->getName() . ' repaired this Rapier.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ], $activityLog);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, false);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% spent a while trying to repair a Rusty Rapier, but wasn\'t able to make any progress.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting', 'Smithing' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ], $activityLog);
        }

        return $activityLog;
    }

    private function repairOldMechanism(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + min($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getScience()->getTotal()));

        if($roll >= 18)
        {
            $loot = $this->squirrel3->rngNextFromArray([
                'Telluriscope', 'Seismustatus', 'Espophone', 'Ferroleuvorter', 'Saccharactum',
            ]);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Rusted, Busted Mechanism', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% repaired a Rusted, Busted Mechanism; it\'s now a fully-functional ' . $loot . '!', 'items/old-mechanism/busted')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting', 'Physics' ]))
            ;
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' repaired this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% spent a while trying to repair a Rusted, Busted Mechanism, but wasn\'t able to make any progress.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting', 'Physics' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createLaserGuidedSword(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getScience()->getTotal(), $petWithSkills->getCrafts()->getTotal()));

        if($roll >= 14)
        {
            $this->houseSimService->getState()->loseItem('Iron Sword', 1);
            $this->houseSimService->getState()->loseItem('Glue', 1);
            $this->houseSimService->getState()->loseItem('Laser Pointer', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Laser-guided Sword.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting', 'Electronics' ]))
            ;

            if($this->squirrel3->rngNextInt(1, 4) === 1)
                $this->inventoryService->petCollectsItem('Laser-guided Sword', $pet, $pet->getName() . ' created by gluing a Laser Pointer to a sword. Naturally.', $activityLog);
            else
                $this->inventoryService->petCollectsItem('Laser-guided Sword', $pet, $pet->getName() . ' created by gluing a Laser Pointer to a sword.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to improve an Iron Sword, but wasn\'t sure how to begin...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting', 'Electronics' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
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

    private function createFlag(ComputedPetSkills $petWithSkills, string $dye, string $making)
    {
        $pet = $petWithSkills->getPet();
        $makingItem = $this->itemRepository->findOneByName($making);

        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 10)
        {
            $this->houseSimService->getState()->loseItem('White Flag', 1);
            $this->houseSimService->getState()->loseItem($dye, 1);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% dyed ' . $makingItem->getNameWithArticle() . '.', 'items/' . $makingItem->getImage())
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Painting' ]))
            ;
            $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' dyed this flag.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to dye a flag, but couldn\'t come up with a good design.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Painting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 45), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createSunSunFlag(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 12)
        {
            $this->houseSimService->getState()->loseItem('Sun Flag', 1);
            $this->houseSimService->getState()->loseItem('Sunflower Stick', 1);
            $pet->increaseSafety($this->squirrel3->rngNextInt(2, 4));
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Sun-sun Flag!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Sun-sun Flag', $pet, $pet->getName() . ' made this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to make a Sun Flag even sunnier, but wasn\'t sure how...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 45), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createSunSunFlagFlagSon(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 20)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Sun-sun Flag', 2);
            $pet->increaseEsteem($this->squirrel3->rngNextInt(2, 4));
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Sun-sun Flag-flag, Son!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Sun-sun Flag-flag, Son', $pet, $pet->getName() . ' made this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to combine two Sun-sun Flags, but couldn\'t figure it out...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 45), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createPaleFlail(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Plastic Fishing Rod', 1);
            $this->houseSimService->getState()->loseItem('Moon Pearl', 1);
            $this->houseSimService->getState()->loseItem('Talon', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Pale Flail!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Pale Flail', $pet, $pet->getName() . ' made this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to make a flail, but had trouble shaping the Plastic Fishing Rod.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createBindle(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('White Flag', 1);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick');
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% used their Eidetic Memory to perfectly knot _two_ Bindles!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Bindle', $pet, $pet->getName() . ' made this, thanks to their Eidetic Memory.', $activityLog);
            $this->inventoryService->petCollectsItem('Bindle', $pet, $pet->getName() . ' made this, thanks to their Eidetic Memory.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else if($roll >= 10)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('White Flag', 1);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick');
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Bindle by tying a White Flag to a stick.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Bindle', $pet, $pet->getName() . ' made this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to tie a Bindle, but couldn\'t remember their knots...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 45), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createBindle2(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('White Flag', 1);
            $this->houseSimService->getState()->loseItem('Crooked Fishing Rod');
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% used their Eidetic Memory to perfectly knot _two_ Bindles!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Bindle', $pet, $pet->getName() . ' made this, thanks to their Eidetic Memory.', $activityLog);
            $this->inventoryService->petCollectsItem('Bindle', $pet, $pet->getName() . ' made this, thanks to their Eidetic Memory.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else if($roll >= 10)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('White Flag', 1);
            $this->houseSimService->getState()->loseItem('Crooked Fishing Rod');
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Bindle by tying a White Flag to a Crooked Fishing Rod.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Bindle', $pet, $pet->getName() . ' made this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to tie a Bindle, but couldn\'t remember their knots...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 45), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createPeacockPlushy(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $craftsCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($craftsCheck >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('White Cloth', 1);
            $this->houseSimService->getState()->loseItem('Quinacridone Magenta Dye', 1);

            $stuffing = $this->houseSimService->getState()->loseOneOf([ 'Beans', 'Fluff' ]);

            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Peacock Plushy stuffed with ' . $stuffing . '!', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Peacock Plushy', $pet, $pet->getName() . ' created this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a plushy, but couldn\'t come up with a good pattern...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createGrabbyArm(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $craftsCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($craftsCheck >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Plastic', 1);
            $this->houseSimService->getState()->loseItem('Iron Bar', 1);

            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Grabby Arm!', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Grabby Arm', $pet, $pet->getName() . ' created this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a grabby arm, but couldn\'t get it to grab...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function makeSpyBalloon(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $craftsCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($craftsCheck >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Blue Balloon', 1);
            $this->houseSimService->getState()->loseItem('Gold Telescope', 1);

            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% simply tied a Gold Telescope to a Blue Balloon! Easy!', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Spy Balloon', $pet, $pet->getName() . ' created this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to tie a Gold Telescope to a Blue Balloon, but their knot kept coming loose...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }
}
