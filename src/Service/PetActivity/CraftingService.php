<?php
namespace App\Service\PetActivity;

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
use App\Model\PetChanges;
use App\Repository\ItemRepository;
use App\Service\InventoryService;
use App\Service\PetActivity\Crafting\PlasticPrinterService;
use App\Service\PetActivity\Crafting\SmithingService;
use App\Service\PetActivity\Crafting\MagicBindingService;
use App\Service\PetActivity\Crafting\Helpers\StickCraftingService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;

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

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService,
        SmithingService $smithingService, MagicBindingService $magicBindingService,
        PlasticPrinterService $plasticPrinterService, PetExperienceService $petExperienceService,
        StickCraftingService $stickCraftingService, ItemRepository $itemRepository
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
    }

    /**
     * @return ActivityCallback[]
     */
    public function getCraftingPossibilities(Pet $pet, array $quantities): array
    {
        $possibilities = [];

        if(array_key_exists('Fluff', $quantities))
        {
            $possibilities[] = new ActivityCallback($this, 'spinFluff', 10);
        }

        if(array_key_exists('Tea Leaves', $quantities))
        {
            if($quantities['Tea Leaves']->quantity >= 2)
                $possibilities[] = new ActivityCallback($this, 'createYellowDyeFromTeaLeaves', 10);
        }

        if(array_key_exists('Scales', $quantities))
        {
            if($quantities['Scales']->quantity >= 2)
                $possibilities[] = new ActivityCallback($this, 'extractFromScales', 10);

            if(array_key_exists('Talon', $quantities) && array_key_exists('Wooden Sword', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createSnakebite', 10);
        }

        if(array_key_exists('Crooked Stick', $quantities))
        {
            if(array_key_exists('String', $quantities))
            {
                $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createCrookedFishingRod', 10);
                $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createWoodenSword', 10);

                if(array_key_exists('Talon', $quantities))
                    $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createHuntingSpear', 10);

                if(array_key_exists('Hunting Spear', $quantities))
                    $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createVeryLongSpear', 10);

                if(array_key_exists('Overly-long Spear', $quantities))
                    $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createRidiculouslyLongSpear', 10);

                if(array_key_exists('Corn', $quantities) && array_key_exists('Rice', $quantities))
                    $possibilities[] = new ActivityCallback($this->stickCraftingService, 'createHarvestStaff', 10);
            }

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

        if(array_key_exists('White Cloth', $quantities) && array_key_exists('String', $quantities) && array_key_exists('Ruby Feather', $quantities))
            $possibilities[] = new ActivityCallback($this, 'createFeatheredHat', 10);

        if(array_key_exists('String', $quantities))
        {
            if(array_key_exists('Glass', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createGlassPendulum', 10);

            if(array_key_exists('Paper', $quantities) && array_key_exists('Silver Key', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createBenjaminFranklin', 10);

            if(array_key_exists('Really Big Leaf', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createLeafSpear', 10);
        }

        if(array_key_exists('Feathers', $quantities))
        {
            if(array_key_exists('Hunting Spear', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createDecoratedSpear', 10);

            if(array_key_exists('Fiberglass Pan Flute', $quantities) && array_key_exists('Yellow Dye', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createOrnatePanFlute', 10);
        }

        if(array_key_exists('Decorated Spear', $quantities) && array_key_exists('Quintessence', $quantities))
            $possibilities[] = new ActivityCallback($this, 'createVeilPiercer', 10);

        if(array_key_exists('Crooked Fishing Rod', $quantities) && array_key_exists('Yellow Dye', $quantities) && array_key_exists('Green Dye', $quantities))
            $possibilities[] = new ActivityCallback($this, 'createPaintedFishingRod', 10);

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

        if(array_key_exists('Glass Pendulum', $quantities) && array_key_exists('Flute', $quantities) && array_key_exists('White Cloth', $quantities))
            $possibilities[] = new ActivityCallback($this, 'createDecoratedFlute', 10);

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

        $possibilities = array_merge($possibilities, $this->smithingService->getCraftingPossibilities($pet, $quantities));

        if(array_key_exists('Plastic', $quantities))
        {
            if(array_key_exists('3D Printer', $quantities))
                $possibilities = array_merge($possibilities, $this->plasticPrinterService->getCraftingPossibilities($pet, $quantities));

            if(array_key_exists('Smallish Pumpkin', $quantities) && array_key_exists('Crooked Stick', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createDrumpkin', 10);
        }

        $repairWeight = ($pet->getSmithing() >= 3 || $pet->getCrafts() >= 5) ? 10 : 1;

        if(array_key_exists('Rusty Blunderbuss', $quantities))
            $possibilities[] = new ActivityCallback($this, 'repairRustyBlunderbuss', $repairWeight);

        if(array_key_exists('Rusty Rapier', $quantities))
            $possibilities[] = new ActivityCallback($this, 'repairRustyRapier', $repairWeight);

        $possibilities = array_merge($possibilities, $this->magicBindingService->getCraftingPossibilities($pet, $quantities));

        return $possibilities;
    }

    /**
     * @param ActivityCallback[] $possibilities
     */
    public function adventure(Pet $pet, array $possibilities): PetActivityLog
    {
        if(count($possibilities) === 0)
            throw new \InvalidArgumentException('possibilities must contain at least one item.');

        /** @var ActivityCallback $method */
        $method = ArrayFunctions::pick_one($possibilities);

        $activityLog = null;
        $changes = new PetChanges($pet);

        /** @var PetActivityLog $activityLog */
        $activityLog = ($method->callable)($pet);

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));

        return $activityLog;
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createSimpleFiberglassItem(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts());

        $item = ArrayFunctions::pick_one([ 'Fiberglass Flute' ]);

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);

            $this->inventoryService->loseItem('Fiberglass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a ' . $item . ', but shattered the Fiberglass! :(', '');
        }
        else if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Fiberglass', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' made a ' . $item . ' from Fiberglass.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
            ;
            $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' created this from Fiberglass.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a ' . $item . ', but the Fiberglass wasn\'t cooperating.', 'icons/activity-logs/confused');
        }
    }

    private function createDecoratedFlute(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts());
        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Decorated Flute, but tore the White Cloth :(', '');
        }
        else if($roll >= 18)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::CRAFT, true);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ]);
            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Flute', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Glass Pendulum', $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Decorated Flute.', 'items/tool/instrument/flute-decorated')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
            ;
            $this->inventoryService->petCollectsItem('Decorated Flute', $pet, $pet->getName() . ' created this by tying a Glass Pendulum to a Flute.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' thought it might be cool to decorate a Flute, but couldn\'t think of something stylish enough.', 'icons/activity-logs/confused');
        }
    }

    private function createDrumpkin(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts() + $pet->getSmithing());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Drumpkin, but burnt the Plastic while trying to soften it :(', '');
            }
            else
            {
                $this->inventoryService->loseItem('Smallish Pumpkin', $pet->getOwner(), LocationEnum::HOME, 1);
                $pet->increaseFood(5);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Drumpkin, but broke the Smallish Pumpkin :( Not wanting to waste it, ' . $pet->getName() . ' ate the remains...)', '');
            }
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::CRAFT, true);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Smallish Pumpkin', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Drumpkin!', 'items/tool/instrument/drumpkin')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem('Drumpkin', $pet, $pet->getName() . ' created this!', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Drumpkin, but couldn\'t get the Plastic thin enough...', 'icons/activity-logs/confused');
        }
    }

    public function createDecoratedSpear(Pet $pet)
    {
        $roll = mt_rand(1, 20 + $pet->getDexterity() + $pet->getCrafts());

        if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(15, 30), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Feathers', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Hunting Spear', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Decorated Spear.', '');
            $this->inventoryService->petCollectsItem('Decorated Spear', $pet, $pet->getName() . ' decorated a Hunting Spear with Feathers to make this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(15, 30), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to decorate a Hunting Spear with Feathers, but couldn\'t get the look just right.', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function createDoubleScythe(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts());

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            $this->inventoryService->loseItem('Scythe', $pet->getOwner(), LocationEnum::HOME, 1);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Double Scythe, but split the blade on one of the Scythes :|', '');
            $pet->increaseEsteem(-2);

            $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' "made" this by accidentally breaking the blade of a Scythe while trying to make a Double Scythe.', $activityLog);

            return $activityLog;
        }
        else if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, true);
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

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Double Scythe; ' . $and, 'items/tool/scythe/double')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
            ;

            $this->inventoryService->petCollectsItem('Double Scythe', $pet, $pet->getName() . ' created this by taking the blade from one Scythe and attaching it to another.', $activityLog);
            $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' "made" this by taking the blade off of a Scythe (to make a Double Scythe).', $activityLog);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' thought it might be cool to make a Double Scythe, but then doubted themself...', 'icons/activity-logs/confused');
        }
    }

    private function createFarmersMultiTool(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts());

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            // scythes can already break when making double scythes, so let's at least skew towards garden shovels here
            $lostItem = ArrayFunctions::pick_one([ 'Scythe', 'Garden Shovel', 'Garden Shovel' ]);

            $this->inventoryService->loseItem($lostItem, $pet->getOwner(), LocationEnum::HOME, 1);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Farmer\'s Multi-tool, but split the blade of the ' . $lostItem . ' while trying to take it apart :|', '');
            $pet->increaseEsteem(-2);

            $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' "made" this by accidentally breaking the blade of a ' . $lostItem . ' while trying to make a Farmer\'s Multi-tool.', $activityLog);

            return $activityLog;
        }
        else if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $this->inventoryService->loseItem('Scythe', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Garden Shovel', $pet->getOwner(), LocationEnum::HOME, 1);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Farmer\'s Multi-tool!', 'items/tool/shovel/multi-tool')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
            ;

            $this->inventoryService->petCollectsItem('Farmer\'s Multi-tool', $pet, $pet->getName() . ' created this by combining the best parts of a Scythe and a Garden Shovel.', $activityLog);
            $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' had this left over after making a Farmer\'s Multi-tool.', $activityLog);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' wanted to combine a Scythe and a Garden Shovel, but couldn\'t decide which of the two tools to start with?', 'icons/activity-logs/confused');
        }
    }

    private function spinFluff(Pet $pet): PetActivityLog
    {
        $making = $this->itemRepository->findOneByName(ArrayFunctions::pick_one([
            'String',
            'White Cloth'
        ]));

        $difficulty = $making->getName() === 'String' ? 10 : 13;

        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts());
        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->inventoryService->loseItem('Fluff', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to spin some Fluff into ' . $making->getName() . ', but messed it up; the Fluff was wasted :(', '');
        }
        else if($roll >= $difficulty)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Fluff', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' spun some Fluff into ' . $making->getName() . '.', 'items/' . $making->getImage())
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + $difficulty)
            ;

            $this->inventoryService->petCollectsItem($making, $pet, $pet->getName() . ' spun this from Fluff.', $activityLog);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to spin some Fluff into ' . $making->getName() . ', but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function createYellowDyeFromTeaLeaves(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getNature() + $pet->getCrafts());
        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, false);
            $this->inventoryService->loseItem('Tea Leaves', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to extract Yellow Dye from Tea Leaves, but messed it up, ruining the Tea Leaves :(', '');
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Tea Leaves', $pet->getOwner(), LocationEnum::HOME, 2);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' extracted Yellow Dye from some Tea Leaves.', 'items/resource/dye-yellow');
            $this->inventoryService->petCollectsItem('Yellow Dye', $pet, $pet->getName() . ' extracted this from Tea Leaves.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' wanted to extract Yellow Dye from some Tea Leaves, but wasn\'t sure how to start.', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function extractFromScales(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getNature() + $pet->getCrafts());
        $itemName = mt_rand(1, 2) === 1 ? 'Green Dye' : 'Glue';

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, false);
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
            $this->petExperienceService->spendTime($pet, mt_rand(60, 90), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Scales', $pet->getOwner(), LocationEnum::HOME, 2);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' extracted Green Dye _and_ Glue from some Scales!', 'items/animal/scales');
            $this->inventoryService->petCollectsItem('Green Dye', $pet, $pet->getName() . ' extracted this from Scales.', $activityLog);
            $this->inventoryService->petCollectsItem('Glue', $pet, $pet->getName() . ' extracted this from Scales.', $activityLog);
            return $activityLog;
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Scales', $pet->getOwner(), LocationEnum::HOME, 2);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' extracted ' . $itemName . ' from some Scales.', 'items/animal/scales');
            $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' extracted this from Scales.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' wanted to extract ' . $itemName . ' from some Scales, but wasn\'t sure how to start.', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function createFabricMache(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-2);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make some Fabric Mâché, but messed it all up, ruining the White Cloth and wasting the Glue :(', 'icons/activity-logs/torn-to-bits');
        }
        else if($roll >= 14)
        {
            $possibleItems = [ 'Fabric Mâché Basket' ];
            $item = ArrayFunctions::pick_one($possibleItems);

            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a ' . $item . '.', '');
            $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' created this from White Cloth and Glue.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make some Fabric Mâché, but couldn\'t come up with a good pattern.', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function createGoldTrifecta(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-2);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Gold Trifecta, but messed up and wasted the Glue :(', '');
        }
        else if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Gold Triangle', $pet->getOwner(), LocationEnum::HOME, 3);
            $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Gold Trifecta.', '');
            $this->inventoryService->petCollectsItem('Gold Trifecta', $pet, $pet->getName() . ' created by gluing together three Gold Triangles.', $activityLog);

            if(mt_rand(1, 2) === 1) $this->inventoryService->petCollectsItem('String', $pet, $pet->getName() . ' recovered this when creating a Gold Trifecta.', $activityLog);
            if(mt_rand(1, 2) === 1) $this->inventoryService->petCollectsItem('String', $pet, $pet->getName() . ' recovered this when creating a Gold Trifecta.', $activityLog);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' wanted to make a Gold Trifecta, but wasn\'t sure how to begin...', 'icons/activity-logs/confused');
        }
    }

    private function createLSquare(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 45), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
                $pet->increaseEsteem(-2);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make an L-Square, but messed up and wasted the Glue :(', '');
            }
            else
            {
                $this->inventoryService->loseItem('Ruler', $pet->getOwner(), LocationEnum::HOME, 1);
                $pet->increaseSafety(-2);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make an L-Square, but accidentally snapped one of the Rulers in half! :(', '');
            }
        }
        else if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Ruler', $pet->getOwner(), LocationEnum::HOME, 2);
            $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created an L-Square.', '');
            $this->inventoryService->petCollectsItem('L-Square', $pet, $pet->getName() . ' created by gluing together a couple Rulers.', $activityLog);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' wanted to make an L-Square, but spent forever trying to make it _exactly_ 90 degrees, and eventually gave up...', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function createAlienCookingBuddy(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-2);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to glue some Antennae onto a Cooking Buddy, but messed up and wasted the Glue :(', '');
        }
        else if($roll >= 10)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Cooking Buddy', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Antenna', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet
                ->increaseEsteem(4)
                ->increaseSafety(2)
            ;
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' cracked themselves up by created a Cooking "Alien".', '');
            $this->inventoryService->petCollectsItem('Cooking "Alien"', $pet, $pet->getName() . ' created by gluing some Antennae on a Cooking Buddy.', $activityLog);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' wanted to do something silly to a Cooking Buddy, but couldn\'t decide what...', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function createFiberglassPanFlute(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + max($pet->getMusic(), $pet->getCrafts()));

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);

            $lostItem = ArrayFunctions::pick_one([ 'Glue', 'White Cloth', 'Fiberglass Flute' ]);
            $this->inventoryService->loseItem($lostItem, $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-2);

            switch($lostItem)
            {
                case 'Fiberglass Flute':
                    return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Fiberglass Pan Flute, but shattered the Fiberglass Flutes while trying to cut it up :|', '');

                case 'Glue':
                    return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Fiberglass Pan Flute, but accidentally spilled the Glue everywhere :|', '');

                case 'White Cloth':
                    return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Fiberglass Pan Flute, but tore the White Cloth while trying to cut it into shape :|', '');

                default:
                    throw new \Exception('Ben done fucked up: a pet was going to accidentally break a ' . $lostItem . ' while crafting, but that wasn\'t accounted for by the Fiberglass Pan Flute event code...');
            }
        }
        else if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::CRAFT, true);

            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Fiberglass Flute', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Fiberglass Pan Flute.', '');
            $this->inventoryService->petCollectsItem('Fiberglass Pan Flute', $pet, $pet->getName() . ' created this by hacking a Fiberglass Flute into several pieces, and gluing them together with a ribbon of cloth.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' wanted to make a Fiberglass Pan Flute, but didn\'t feel confident about cutting the Fiberglass Flute in half.', 'icons/activity-logs/confused');
        }
    }

    private function createGlassPendulum(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts());

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);

            if(mt_rand(1, 20 + $pet->getDexterity() + $pet->getStamina()) >= 18)
            {
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseEsteem(2);
                $pet->increaseSafety(-4);

                if(mt_rand(1, 4) === 1)
                {
                    $pet->increaseEsteem(2);
                    return $this->responseService->createActivityLog($pet, $pet->getName() . ' started to cut a piece of glass, but cut themselves, instead! :( They managed to save the glass, though! ' . $pet->getName() . ' is kind of proud of that.', 'icons/activity-logs/wounded');
                }
                else
                    return $this->responseService->createActivityLog($pet, $pet->getName() . ' started to cut a piece of glass, but cut themselves, instead! :( They managed to save the glass, though!', 'icons/activity-logs/wounded');
            }
            else
            {
                $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseEsteem(-2);
                $pet->increaseSafety(-4);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' started to cut a piece of glass, but cut themselves, instead, and dropped the glass :(', 'icons/activity-logs/wounded');
            }
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' cut some Glass to look like a gem, and made a Glass Pendulum.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem('Glass Pendulum', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $pet->increaseSafety(-1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Glass Pendulum, but almost cut themselves on the glass, and gave up.', 'icons/activity-logs/confused');
        }
    }

    private function createLeafSpear(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getStrength() * 2 + $pet->getDexterity());

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);

            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Leaf Spear, but the String couldn\'t hold the Really Big Leaf, and broke under the stress!', 'icons/activity-logs/broke-string');
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Really Big Leaf', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);

            $message = $pet->getName() . ' rolled up a Really Big Leaf, and tied it, creating a Leaf Spear!';

            if(mt_rand(1, 5) > $pet->getStrength())
                $message .= ' (It\'s harder than it looks!)';

            $activityLog = $this->responseService->createActivityLog($pet, $message, '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem('Leaf Spear', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $pet->increaseSafety(-1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Leaf Spear, but the Really Big Leaf is surprisingly strong! ' . $pet->getName() . ' couldn\'t get it to roll up...', 'icons/activity-logs/confused');
        }
    }

    private function createBenjaminFranklin(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + max($pet->getCrafts(), $pet->getScience()));

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ]);

            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a kite, but broke the String :(', 'icons/activity-logs/broke-string');
            }
            else
            {
                $this->inventoryService->loseItem('Paper', $pet->getOwner(), LocationEnum::HOME, 1);
                $pet->increaseEsteem(-2);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a kite, but tore the Paper :(', '');
            }
        }
        else if($roll >= 17)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Paper', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Silver Key', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created Benjamin Franklin. (A kite, not the person.)', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 17)
            ;
            $this->inventoryService->petCollectsItem('Benjamin Franklin', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a kite, but couldn\'t come up with a good design...', 'icons/activity-logs/confused');
        }
    }

    private function createFeatheredHat(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);

            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a hat, but tore the cloth :(', '');
        }
        else if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Ruby Feather', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(5);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' made an Afternoon Hat by shaping some White Cloth, and tying a Ruby Feather to it!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 17)
            ;
            $this->inventoryService->petCollectsItem('Afternoon Hat', $pet, $pet->getName() . ' created this!', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' wanted to make a hat, but couldn\'t come up with a good design...', 'icons/activity-logs/confused');
        }
    }

    private function createOrnatePanFlute(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + max($pet->getCrafts(), $pet->getMusic()));

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('Feathers', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::MUSIC ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to decorate a Fiberglass Pan Flute, but misruffled the feathers :(', '');
            }
            else
            {
                $this->inventoryService->loseItem('Yellow Dye', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::MUSIC ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to decorate a Fiberglass Pan Flute, but spilled the Yellow Dye :(', '');
            }
        }
        else if($roll >= 18)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Fiberglass Pan Flute', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Yellow Dye', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Feathers', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS, PetSkillEnum::MUSIC ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created an Ornate Pan Flute.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
            ;
            $this->inventoryService->petCollectsItem('Ornate Pan Flute', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::MUSIC ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' wanted to decorate a Fiberglass Pan Flute, but couldn\'t come up with a good design.', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function createSnakebite(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getDexterity() + $pet->getCrafts());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-2);
            $pet->increaseSafety(-4);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to craft Snakebite, but cut themself on a Talon!', 'icons/activity-logs/wounded');
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Talon', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Scales', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Wooden Sword', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Snakebite sword.', '');
            $this->inventoryService->petCollectsItem('Snakebite', $pet, $pet->getName() . ' made this by improving a Wooden Sword.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to improve a Wooden Sword into Snakebite, but failed.', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function createVeilPiercer(Pet $pet): PetActivityLog
    {
        $umbraCheck = mt_rand(1, 20 + $pet->getUmbra() + $pet->getIntelligence());
        $craftsCheck = mt_rand(1, 20 + $pet->getCrafts() + $pet->getDexterity() + $pet->getIntelligence());

        if($umbraCheck <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to enchanted a Decorated Spear, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }
        else if($craftsCheck < 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried enchant a Decorated Spear, but couldn\'t get an enchantment to stick.', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Decorated Spear', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' enchanted a Decorated Spear to be a Veil-piercer.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem('Veil-piercer', $pet, $pet->getName() . ' made this by enchanting a Decorated Spear.', $activityLog);
            return $activityLog;
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function createPaintedFishingRod(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, mt_rand(45, 90), PetActivityStatEnum::CRAFT, true);
        $this->inventoryService->loseItem('Crooked Fishing Rod', $pet->getOwner(), LocationEnum::HOME, 1);
        $this->inventoryService->loseItem('Yellow Dye', $pet->getOwner(), LocationEnum::HOME, 1);
        $this->inventoryService->loseItem('Green Dye', $pet->getOwner(), LocationEnum::HOME, 1);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
        $pet->increaseEsteem(1);
        $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Painted Fishing Rod.', '');
        $this->inventoryService->petCollectsItem('Painted Fishing Rod', $pet, $pet->getName() . ' painted this, using Yellow and Green Dye.', $activityLog);
        return $activityLog;
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function createGoldIdol(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, mt_rand(45, 90), PetActivityStatEnum::CRAFT, true);
        $this->inventoryService->loseItem('Plastic Idol', $pet->getOwner(), LocationEnum::HOME, 1);
        $this->inventoryService->loseItem('Yellow Dye', $pet->getOwner(), LocationEnum::HOME, 1);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
        $pet->increaseEsteem(1);
        $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a "Gold" Idol.', '');
        $this->inventoryService->petCollectsItem('"Gold" Idol', $pet, $pet->getName() . ' painted this, using Yellow Dye.', $activityLog);
        return $activityLog;
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function createYellowBucket(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, mt_rand(15, 30), PetActivityStatEnum::CRAFT, true);
        $this->inventoryService->loseItem('Small Plastic Bucket', $pet->getOwner(), LocationEnum::HOME, 1);
        $this->inventoryService->loseItem('Yellow Dye', $pet->getOwner(), LocationEnum::HOME, 1);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
        $pet->increaseEsteem(1);
        $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' dunked a Small Plastic Bucket into some Yellow Dye.', '');
        $this->inventoryService->petCollectsItem('Small, Yellow Plastic Bucket', $pet, $pet->getName() . ' "painted" this, using Yellow Dye.', $activityLog);
        return $activityLog;
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function createPaintedDumbbell(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::CRAFT, true);
        $this->inventoryService->loseItem('Dumbbell', $pet->getOwner(), LocationEnum::HOME, 1);
        $this->inventoryService->loseItem('Yellow Dye', $pet->getOwner(), LocationEnum::HOME, 1);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
        $pet->increaseEsteem(1);

        if(mt_rand(1, 10) === 1)
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' painted emojis on a Dumbbell. (That makes them better, right?)', '');
        else
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' painted emojis on a Dumbbell.', '');

        $this->inventoryService->petCollectsItem('Painted Dumbbell', $pet, $pet->getName() . ' painted this, using Yellow Dye.', $activityLog);

        return $activityLog;
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function repairRustyBlunderbuss(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + max($pet->getCrafts(), $pet->getSmithing()));

        if($roll === 1 && !$pet->hasMerit(MeritEnum::LUCKY))
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, false);
            $this->inventoryService->loseItem('Rusty Blunderbuss', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-4);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to repair a Rusty Blunderbuss, but accidentally broke it beyond repair :(', '');
        }
        else if($roll >= 18)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Rusty Blunderbuss', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' repaired a Rusty Blunderbuss. It\'s WAY less rusty now!', 'items/tool/gun/blunderbuss')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
            ;
            $this->inventoryService->petCollectsItem('Blunderbuss', $pet, $pet->getName() . ' repaired this Rusty Blunderbuss.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' spent a while trying to repair a Rusty Blunderbuss, but wasn\'t able to make any progress.', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function repairRustyRapier(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + max($pet->getCrafts(), $pet->getSmithing()));

        if($roll === 1 && !$pet->hasMerit(MeritEnum::LUCKY))
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, false);
            $this->inventoryService->loseItem('Rusty Rapier', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            $pet->increaseEsteem(-4);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to repair a Rusty Rapier, but accidentally broke it beyond repair :(', '');
        }
        else if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Rusty Rapier', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' repaired a Rusty Rapier. It\'s WAY less rusty now!', 'items/tool/sword/rapier')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
            ;
            $this->inventoryService->petCollectsItem('Rapier', $pet, $pet->getName() . ' repaired this Rapier.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' spent a while trying to repair a Rusty Rapier, but wasn\'t able to make any progress.', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function createLaserGuidedSword(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + max($pet->getScience(), $pet->getCrafts()));

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(-2);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Laser-guided Sword, but messed up and wasted the Glue :(', '');
        }
        else if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Iron Sword', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Laser Pointer', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Laser-guided Sword.', '');

            if(mt_rand(1, 4) === 1)
                $this->inventoryService->petCollectsItem('Laser-guided Sword', $pet, $pet->getName() . ' created by gluing a Laser Pointer to a sword. Naturally.', $activityLog);
            else
                $this->inventoryService->petCollectsItem('Laser-guided Sword', $pet, $pet->getName() . ' created by gluing a Laser Pointer to a sword.', $activityLog);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' wanted to improve an Iron Sword, but wasn\'t sure how to begin...', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function createSunFlag(Pet $pet): PetActivityLog
    {
        return $this->createFlag($pet, 'Yellow Dye', 'Sun Flag');
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function createDragonFlag(Pet $pet): PetActivityLog
    {
        return $this->createFlag($pet, 'Green Dye', 'Dragon Flag');
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function createFlag(Pet $pet, string $dye, string $making)
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(15, 30), PetActivityStatEnum::CRAFT, false);

            $this->inventoryService->loseItem($dye, $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a ' . $making . ', but accidentally spilt the ' . $dye . ' :(', '');
        }
        else if($roll >= 10)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('White Flag', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem($dye, $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' painted a ' . $making . '.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
            ;
            $this->inventoryService->petCollectsItem($making, $pet, $pet->getName() . ' painted this flag.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(15, 45), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to paint a flag, but couldn\'t come up with a good design.', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function createBindle(Pet $pet)
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 45), PetActivityStatEnum::CRAFT, false);

            $this->inventoryService->loseItem('White Flag', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Bindle, but accidentally tore the White Flag :(', '');
            $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' accidentally tore a White Flag; this remains.', $activityLog);
        }
        else if($roll >= 10 || $pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('White Flag', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' made a Bindle from a White Flag.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
            ;
            $this->inventoryService->petCollectsItem('Bindle', $pet, $pet->getName() . ' made this.', $activityLog);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(15, 45), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to tie a Bindle, but couldn\'t remember their knots...', 'icons/activity-logs/confused');
        }

        return $activityLog;
    }
}
