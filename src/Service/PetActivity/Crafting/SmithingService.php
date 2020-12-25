<?php
namespace App\Service\PetActivity\Crafting;

use App\Entity\PetActivityLog;
use App\Enum\GuildEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Model\ActivityCallback;
use App\Model\ComputedPetSkills;
use App\Repository\SpiceRepository;
use App\Service\CalendarService;
use App\Service\InventoryService;
use App\Service\PetActivity\Crafting\Helpers\EvericeMeltingService;
use App\Service\PetActivity\Crafting\Helpers\GoldSmithingService;
use App\Service\PetActivity\Crafting\Helpers\HalloweenSmithingService;
use App\Service\PetActivity\Crafting\Helpers\IronSmithingService;
use App\Service\PetActivity\Crafting\Helpers\MeteoriteSmithingService;
use App\Service\PetActivity\Crafting\Helpers\SilverSmithingService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;

class SmithingService
{
    private $inventoryService;
    private $responseService;
    private $petExperienceService;
    private $goldSmithingService;
    private $ironSmithingService;
    private $meteoriteSmithingService;
    private $halloweenSmithingService;
    private $calendarService;
    private $spiceRepository;
    private $evericeMeltingService;
    private $silverSmithingService;

    public function __construct(
        InventoryService $inventoryService, ResponseService $responseService, PetExperienceService $petExperienceService,
        GoldSmithingService $goldSmithingService, SilverSmithingService $silverSmithingService,
        IronSmithingService $ironSmithingService, MeteoriteSmithingService $meteoriteSmithingService,
        HalloweenSmithingService $halloweenSmithingService, CalendarService $calendarService,
        SpiceRepository $spiceRepository, EvericeMeltingService $evericeMeltingService
    )
    {
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->petExperienceService = $petExperienceService;
        $this->goldSmithingService = $goldSmithingService;
        $this->ironSmithingService = $ironSmithingService;
        $this->meteoriteSmithingService = $meteoriteSmithingService;
        $this->halloweenSmithingService = $halloweenSmithingService;
        $this->calendarService = $calendarService;
        $this->spiceRepository = $spiceRepository;
        $this->evericeMeltingService = $evericeMeltingService;
        $this->silverSmithingService = $silverSmithingService;
    }

    public function getCraftingPossibilities(ComputedPetSkills $petWithSkills, array $quantities): array
    {
        $pet = $petWithSkills->getPet();
        $weight = ($pet->getSafety() > 0 || $pet->isInGuild(GuildEnum::DWARFCRAFT)) ? 10 : 1;

        $possibilities = [];

        if(array_key_exists('Charcoal', $quantities))
            $possibilities[] = new ActivityCallback($this, 'createCoke', $weight);

        if(array_key_exists('Iron Ore', $quantities))
            $possibilities[] = new ActivityCallback($this, 'createIronBar', $weight);

        if(array_key_exists('Silver Ore', $quantities))
            $possibilities[] = new ActivityCallback($this, 'createSilverBar', $weight);

        if(array_key_exists('Gold Ore', $quantities))
            $possibilities[] = new ActivityCallback($this, 'createGoldBar', $weight);

        if(array_key_exists('Silica Grounds', $quantities) && array_key_exists('Limestone', $quantities))
            $possibilities[] = new ActivityCallback($this, 'createGlass', $weight);

        if(array_key_exists('Glass', $quantities))
        {
            $possibilities[] = new ActivityCallback($this, 'createCrystalBall', $weight);

            if(array_key_exists('Plastic', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createFiberglass', $weight);

            if(array_key_exists('Silver Bar', $quantities) || array_key_exists('Dark Matter', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createMirror', $weight);
        }

        if(array_key_exists('Fiberglass', $quantities) && array_key_exists('String', $quantities))
            $possibilities[] = new ActivityCallback($this, 'createFiberglassBow', 10);

        if(array_key_exists('Iron Bar', $quantities))
        {
            $possibilities[] = new ActivityCallback($this->ironSmithingService, 'createIronKey', $weight);
            $possibilities[] = new ActivityCallback($this->ironSmithingService, 'createBasicIronCraft', $weight);

            if(array_key_exists('Plastic', $quantities))
            {
                if(array_key_exists('Yellow Dye', $quantities))
                    $possibilities[] = new ActivityCallback($this->ironSmithingService, 'createYellowScissors', 10);

                if(array_key_exists('Green Dye', $quantities))
                    $possibilities[] = new ActivityCallback($this->ironSmithingService, 'createGreenScissors', 10);

                $possibilities[] = new ActivityCallback($this->ironSmithingService, 'createSaucepan', 7);
            }

            if(array_key_exists('Crooked Stick', $quantities))
                $possibilities[] = new ActivityCallback($this->ironSmithingService, 'createScythe', 10);

            if(array_key_exists('String', $quantities))
                $possibilities[] = new ActivityCallback($this->ironSmithingService, 'createGrapplingHook', 10);

            if(array_key_exists('Dark Matter', $quantities))
                $possibilities[] = new ActivityCallback($this->ironSmithingService, 'createHeavyHammer', $petWithSkills->getStrength()->getTotal() >= 3 ? $weight : ceil($weight / 2));

            if(array_key_exists('Mirror', $quantities))
                $possibilities[] = new ActivityCallback($this->ironSmithingService, 'createMirrorShield', $weight);

            if(array_key_exists('Toadstool', $quantities))
                $possibilities[] = new ActivityCallback($this->ironSmithingService, 'createMushketeer', $weight);

            if(array_key_exists('Green Dye', $quantities) && array_key_exists('Bug-catcher\'s Net', $quantities))
                $possibilities[] = new ActivityCallback($this->ironSmithingService, 'createWaterStrider', 10);
        }

        if(array_key_exists('Yellow Scissors', $quantities) && array_key_exists('Green Scissors', $quantities) && array_key_exists('Quinacridone Magenta Dye', $quantities))
            $possibilities[] = new ActivityCallback($this, 'createTriColorScissors', 10);

        if(array_key_exists('Firestone', $quantities))
        {
            if(array_key_exists('Tri-color Scissors', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createPapersBane', $weight);

            if(array_key_exists('Warping Wand', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createRedWarpingWand', 10);
        }

        if(array_key_exists('Silver Bar', $quantities))
        {
            $possibilities[] = new ActivityCallback($this->silverSmithingService, 'createSilverKey', $weight);
            $possibilities[] = new ActivityCallback($this->silverSmithingService, 'createBasicSilverCraft', $weight);

            if(array_key_exists('Crown Coral', $quantities))
                $possibilities[] = new ActivityCallback($this->silverSmithingService, 'createCoralTrident', 10);

            if(array_key_exists('"Rustic" Magnifying Glass', $quantities))
                $possibilities[] = new ActivityCallback($this->silverSmithingService, 'createElvishMagnifyingGlass', 10);

            if(array_key_exists('Leaf Spear', $quantities))
                $possibilities[] = new ActivityCallback($this->silverSmithingService, 'createSylvanFishingRod', 10);

            if(array_key_exists('Glass', $quantities))
            {
                if(array_key_exists('Silica Grounds', $quantities))
                    $possibilities[] = new ActivityCallback($this->silverSmithingService, 'createHourglass', $weight);
            }

            if(array_key_exists('Gold Key', $quantities) && array_key_exists('White Cloth', $quantities))
                $possibilities[] = new ActivityCallback($this->silverSmithingService, 'createGoldKeyblade', 10);

            if(array_key_exists('Wand of Lightning', $quantities))
                $possibilities[] = new ActivityCallback($this->silverSmithingService, 'createLightningAxe', $weight);
        }

        if(array_key_exists('Gold Bar', $quantities))
        {
            $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createGoldKey', $weight);

            $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createGoldTuningFork', ceil($weight / 2));

            if(array_key_exists('Eggplant', $quantities))
                $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createAubergineScepter', 8);

            if(array_key_exists('Blackonite', $quantities) && array_key_exists('White Cloth', $quantities))
                $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createVicious', 10);

            if(array_key_exists('Fiberglass', $quantities) && array_key_exists('Moon Pearl', $quantities))
                $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createMoonhammer', 10);

            if(array_key_exists('Dark Scales', $quantities) && array_key_exists('Dragon Flag', $quantities))
                $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createKundravsStandard', 10);

            if(array_key_exists('String', $quantities))
                $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createGoldTriangle', 10);

            if(array_key_exists('Chanterelle', $quantities) && array_key_exists('Flute', $quantities))
                $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createFungalClarinet', 10);

            if(array_key_exists('Glass', $quantities))
                $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createGoldTelescope', $weight);

            if(array_key_exists('Plastic Shovel', $quantities) && array_key_exists('Green Dye', $quantities))
                $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createCoreopsis', 10);

            if(array_key_exists('Plastic', $quantities) && array_key_exists('3D Printer', $quantities))
                $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createGoldRod', 10);

            if(array_key_exists('Enchanted Compass', $quantities))
                $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createGoldCompass', $weight);

            if(array_key_exists('Silver Key', $quantities) && array_key_exists('White Cloth', $quantities))
                $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createSilverKeyblade', 10);

            if(array_key_exists('Leaf Spear', $quantities) && array_key_exists('Iron Bar', $quantities))
                $possibilities[] = new ActivityCallback($this->goldSmithingService, 'createSiderealLeafSpear', 10);
        }

        if(array_key_exists('Silver Bar', $quantities) && array_key_exists('Gold Bar', $quantities) && array_key_exists('White Cloth', $quantities))
            $possibilities[] = new ActivityCallback($this, 'createCeremonialTrident', 10);

        if(array_key_exists('Iron Sword', $quantities))
        {
            if(array_key_exists('Wooden Sword', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createWoodsMetal', 10);

            if(array_key_exists('Scales', $quantities) && array_key_exists('Fluff', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createDragonscale', 10);

            if(array_key_exists('Dark Scales', $quantities) && array_key_exists('Fluff', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createDrakkonscale', 10);

            if(array_key_exists('Everice', $quantities) && array_key_exists('Firestone', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createAntipode', 10);
        }

        if(array_key_exists('Antipode', $quantities) && array_key_exists('Lightning Sword', $quantities))
            $possibilities[] = new ActivityCallback($this, 'createTrinityBlade', 10);

        if(array_key_exists('Everice', $quantities))
        {
            if(array_key_exists('Poker', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createWandOfIce', 10);

            if(array_key_exists('Crooked Fishing Rod', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createIceFishing', 10);
        }

        if(array_key_exists('Meteorite', $quantities))
        {
            if(array_key_exists('Iron Bar', $quantities) && array_key_exists('Gold Bar', $quantities))
                $possibilities[] = new ActivityCallback($this->meteoriteSmithingService, 'createIlumetsa', 10);
        }

        if($this->calendarService->isHalloweenCrafting())
        {
            if(array_key_exists('Small, Yellow Plastic Bucket', $quantities) || array_key_exists('Upside-down, Yellow Plastic Bucket', $quantities))
                $possibilities[] = new ActivityCallback($this->halloweenSmithingService, 'createPumpkinBucket', 10);
        }

        return $possibilities;
    }

    public function createTriColorScissors(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + max($petWithSkills->getDexterity()->getTotal(), $petWithSkills->getIntelligence()->getTotal()) + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);

            $lostItem = ArrayFunctions::pick_one([
                'Yellow Scissors', 'Green Scissors'
            ]);

            $this->inventoryService->loseItem($lostItem, $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(-1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make Tri-color Scissors, but totally broke the ' . $lostItem . '! :( All that\'s left is the blade (in the form of an Iron Bar - how convenient!)', '');

            $this->inventoryService->petCollectsItem('Iron Bar', $pet, $pet->getName() . ' all the remains of a totally-broken ' . $lostItem . '...', $activityLog);

            return $activityLog;
        }
        else if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Yellow Scissors', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Green Scissors', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Quinacridone Magenta Dye', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(4);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% combined two pairs of scissors, creating Tri-color Scissors!', 'items/tool/scissors/tri-color')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;
            $this->inventoryService->petCollectsItem('Tri-color Scissors', $pet, $pet->getName() . ' made this by combining two pairs of scissors!', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make Tri-color Scissors, but got confused just thinking about what it would even look like...', 'icons/activity-logs/confused');
        }
    }

    public function createPapersBane(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + max($petWithSkills->getDexterity()->getTotal(), $petWithSkills->getIntelligence()->getTotal()) + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 1)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);

            $gainedItem = ArrayFunctions::pick_one([
                'Yellow Scissors', 'Green Scissors'
            ]);

            $this->inventoryService->loseItem('Tri-color Scissors', $pet->getOwner(), LocationEnum::HOME, 1);
            $pet
                ->increaseEsteem(-mt_rand(2, 4))
                ->increaseSafety(-2)
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make Paper\'s Bane, but melted the Tri-color Scissors, leaving only ' . $gainedItem . '! (And getting slightly singed...)', '');

            $this->inventoryService->petCollectsItem($gainedItem, $pet, $pet->getName() . ' all the remains of a melted pair (trio?) of Tri-color Scissors...', $activityLog);

            return $activityLog;
        }
        else if($roll >= 20)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Tri-color Scissors', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Firestone', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(4);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% infused Tri-color Scissors with the eternal heat of Firestone!', 'items/tool/scissors/papersbane')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
            ;
            $this->inventoryService->petCollectsItem('Paper\'s Bane', $pet, $pet->getName() . ' made this by infusing Tri-color Scissors with the eternal heat of Firestone!', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make Paper\'s Bane, but almost burned themselves on the Firestone...', 'icons/activity-logs/confused');
        }
    }

    public function createRedWarpingWand(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 26)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Warping Wand', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Firestone', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(6);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% infused a Warping Wand with the eternal heat of Firestone!', 'items/tool/scissors/papersbane')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 25)
            ;
            $this->inventoryService->petCollectsItem('Red Warping Wand', $pet, $pet->getName() . ' made this by infusing a Warping Wand with the eternal heat of Firestone!', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);

            if(mt_rand(1, 2) === 1)
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Red Warping Wand, but almost burned themselves on the Firestone...', 'icons/activity-logs/confused');
            else
            {
                $location = ArrayFunctions::pick_one([ 'on the roof', 'in the bathtub', 'in the dishwasher', 'under your bed', 'in your closet', 'in the mailbox' ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Red Warping Wand, but accidentally warped the Firestone away. They looked around for a while, and finally found it ' . $location . '.', 'icons/activity-logs/confused');
            }
        }
    }

    public function createMirror(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);

            $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-2);
            $pet->increaseSafety(-4);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried silvering some Glass, but burnt themselves, and dropped the glass :(', 'icons/activity-logs/wounded');
        }
        else if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::SMITH, true);
            $mirrorBacking = $this->inventoryService->loseOneOf([ 'Silver Bar', 'Dark Matter' ], $pet->getOwner(), LocationEnum::HOME);
            $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);

            if($mirrorBacking === 'Dark Matter')
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Dark Mirror.', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
                ;
                $this->inventoryService->petCollectsItem('Dark Mirror', $pet, $pet->getName() . ' created this.', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Mirror.', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
                ;
                $this->inventoryService->petCollectsItem('Mirror', $pet, $pet->getName() . ' created this.', $activityLog);
            }

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Mirror, but couldn\'t get the Glass smooth enough.', 'icons/activity-logs/confused');
        }
    }

    public function createGlass(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->inventoryService->loseItem('Silica Grounds', $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-mt_rand(2, 12));
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make Glass, but got burned while trying! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Silica Grounds', $pet->getOwner(), LocationEnum::HOME, 1);

            if(mt_rand(1, 3) === 1)
            {
                $this->inventoryService->loseItem('Limestone', $pet->getOwner(), LocationEnum::HOME, 1);

                if(mt_rand(1, 3) === 1)
                {
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% melted Silica Grounds and Limestone into TWO Glass!', 'items/mineral/silica-glass');
                    $this->inventoryService->petCollectsItem('Glass', $pet, $pet->getName() . ' created this from Silica Grounds and Limestone.', $activityLog);
                }
                else
                {
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% melted Silica Grounds and Limestone into Glass.', 'items/mineral/silica-glass');
                }
            }
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% melted Silica Grounds and Limestone into Glass. (There\'s plenty of Limestone left over, though!)', 'items/mineral/silica-glass');

            $activityLog
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
            ;

            $this->inventoryService->petCollectsItem('Glass', $pet, $pet->getName() . ' created this from Silica Grounds and Limestone.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make Glass, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createCrystalBall(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $lucky = $pet->hasMerit(MeritEnum::LUCKY) && mt_rand(1, 60) === 1;

        if(mt_rand(1, 60) === 1 || $lucky)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);

            $luckySuffix = $lucky ? ' Lucky~!' : '';

            $pet->increaseEsteem(mt_rand(4, 8));

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make a Crystal Ball out of Glass, but happened to catch the light just right, and got a Rainbow, instead!' . $luckySuffix, 'items/mineral/silica-glass-ball')
                ->addInterestingness($lucky ? PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT : PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ;
            $this->inventoryService->petCollectsItem('Rainbow', $pet, $pet->getName() . ' created this on accident while trying to make a Crystal Ball!' . $luckySuffix, $activityLog);
            return $activityLog;
        }

        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 1)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);

            $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);

            $pet->increaseEsteem(-2);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Crystal Ball, but slipped and dropped it! :(', '');
        }
        else if($roll >= 20 && mt_rand(1, 3) === 1)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(mt_rand(2, 4));

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made TWO Crystal Balls out of Glass!', 'items/mineral/silica-glass-ball')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
            ;
            $this->inventoryService->petCollectsItem('Crystal Ball', $pet, $pet->getName() . ' created this from Glass.', $activityLog);
            $this->inventoryService->petCollectsItem('Crystal Ball', $pet, $pet->getName() . ' created this from Glass.', $activityLog);
            return $activityLog;
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Crystal Ball out of Glass.', 'items/mineral/silica-glass-ball')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 12)
            ;
            $this->inventoryService->petCollectsItem('Crystal Ball', $pet, $pet->getName() . ' created this from Glass.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Crystal Ball, but making a perfect sphere was proving difficult!', 'icons/activity-logs/confused');
        }
    }

    public function createFiberglass(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);

            if(mt_rand(1, 2) === 1)
                $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);
            else
                $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);

            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-mt_rand(2, 12));
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make Fiberglass, but got burned while trying! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= 25 && mt_rand(1, 3) === 1)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(mt_rand(3, 6));

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made TWO bundles of Fiberglass from Glass and Plastic!', 'items/resource/fiberglass')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 25)
            ;
            $this->inventoryService->petCollectsItem('Fiberglass', $pet, $pet->getName() . ' created this from Glass and Plastic.', $activityLog);
            $this->inventoryService->petCollectsItem('Fiberglass', $pet, $pet->getName() . ' created this from Glass and Plastic.', $activityLog);
            return $activityLog;
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a bundle of Fiberglass from Glass and Plastic.', 'items/resource/fiberglass')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem('Fiberglass', $pet, $pet->getName() . ' created this from Glass and Plastic.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make Fiberglass, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createFiberglassBow(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-1);

            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Fiberglass Bow, but burnt the String :(', 'icons/activity-logs/broke-string');
        }
        else if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Fiberglass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Fiberglass Bow.', 'items/tool/bow/fiberglass')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
            ;
            $this->inventoryService->petCollectsItem('Fiberglass Bow', $pet, $pet->getName() . ' created this from Fiberglass, and String.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Fiberglass Bow, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createCeremonialTrident(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $pet->increaseEsteem(-mt_rand(1, 3));

            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Ceremonial Trident, but completely destroyed the White Cloth, leaving only String behind! :(', '');
            $this->inventoryService->petCollectsItem('String', $pet, $pet->getName() . ' accidentally destroyed a White Cloth while trying to make a Ceremonial Trident; this String was all that remained of the cloth.', $activityLog);

            return $activityLog;
        }
        else if($roll <= 4)
        {
            $lost = ArrayFunctions::pick_one([ 'Gold Bar', 'Silver Bar' ]);
            $moneys = mt_rand(10, 30);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::SMITH, false);

            $this->inventoryService->loseItem($lost, $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Ceremonial Trident, but melted the heck out of the ' . $lost . '! :( ' . $pet->getName() . ' decided to make some coins out of it, instead, and got ' . $moneys . '~~m~~.', '');
        }
        else if($roll >= 17)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Silver Bar', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Gold Bar', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('White cloth', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, mt_rand(2, 3), [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(4);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Ceremonial Trident!', 'items/tool/spear/trident-ceremonial')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 17)
            ;
            $this->inventoryService->petCollectsItem('Ceremonial Trident', $pet, $pet->getName() . ' created this from gold, silver, and cloth.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, mt_rand(1, 2), [ PetSkillEnum::CRAFTS ]);

            if($pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Ceremonial Trident, but couldn\'t get the shape just right...', 'icons/activity-logs/confused');
            else
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Ceremonial Spear, but halfway through realized that they had misremembered the item name... >_>', 'icons/activity-logs/confused');
        }
    }

    public function createAntipode(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + max($petWithSkills->getDexterity()->getTotal(), $petWithSkills->getIntelligence()->getTotal()) + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 22)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Iron Sword', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Everice', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Firestone', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(6);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% smithed Antipode!', 'items/tool/sword/antipode')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 22)
            ;
            $this->inventoryService->petCollectsItem('Antipode', $pet, $pet->getName() . ' created this by hammering Everice and Firestone into an Iron Sword!', $activityLog);
            return $activityLog;
        }
        else if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);

            return $this->evericeMeltingService->doMeltEverice($pet, $pet->getName() . ' tried to make Antipode, but accidentally melted the Everice! (Whoa! That\'s not supposed to happen!)');
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make Antipode, but the ' . ArrayFunctions::pick_one([ 'Everice', 'Firestone' ]) . ' was being uncooperative :|', 'icons/activity-logs/confused');
        }
    }

    public function createWoodsMetal(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + max($petWithSkills->getDexterity()->getTotal(), $petWithSkills->getIntelligence()->getTotal()) + $petWithSkills->getStamina()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getScience()->getTotal()) + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->inventoryService->loseItem('Wooden Sword', $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(-2);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to create Wood\'s Metal, but accidentally burnt the Wooden Sword! :( All that remains is a lump of Charcoal...', '');

            $this->inventoryService->petCollectsItem('Charcoal', $pet, $pet->getName() . ' accidentally burnt a Wooden Sword; this is all that remains...', $activityLog);
        }
        else if($roll >= 17)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Iron Sword', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Wooden Sword', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(4);

            $message = $pet->getName() . ' created Wood\'s Metal by alloying an Iron Sword and a Wooden Sword.';

            if(mt_rand(1, 4) === 1)
                $message .= ' (That\'s probably how that works, right?)';

            $activityLog = $this->responseService->createActivityLog($pet, $message, 'items/tool/sword/woodsmetal')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 17)
            ;
            $this->inventoryService->petCollectsItem('Wood\'s Metal', $pet, $pet->getName() . ' made this!', $activityLog);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make Wood\'s Metal, but couldn\'t figure it out...', 'icons/activity-logs/confused');
        }

        return $activityLog;
    }

    public function createDragonscale(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + max($petWithSkills->getDexterity()->getTotal(), $petWithSkills->getIntelligence()->getTotal()) + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->inventoryService->loseItem('Scales', $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(-1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make Dragonscale, but overheated the Scales, causing them to tear and crumble! :(', '');
        }
        else if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Iron Sword', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Fluff', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Scales', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(4);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% upgraded an Iron Sword into Dragonscale!', 'items/tool/sword/dragon-scale')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;
            $this->inventoryService->petCollectsItem('Dragonscale', $pet, $pet->getName() . ' made this by inlaying Scales into an Iron Sword!', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make Dragonscale, but got intimidated by all the detailing work that would be required!', 'icons/activity-logs/confused');
        }
    }

    public function createDrakkonscale(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + max($petWithSkills->getDexterity()->getTotal(), $petWithSkills->getIntelligence()->getTotal()) + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->inventoryService->loseItem('Dark Scales', $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(-1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make Drakkonscale, but overheated the Dark Scales, causing them to tear and crumble! :(', '');
        }
        else if($roll >= 17)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Iron Sword', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Fluff', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Dark Scales', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(4);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% upgraded an Iron Sword into Drakkonscale!', 'items/tool/sword/drakkon-scale')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;
            $this->inventoryService->petCollectsItem('Drakkonscale', $pet, $pet->getName() . ' made this by inlaying Dark Scales into an Iron Sword!', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make Drakkonscale, but got intimidated by all the detailing work that would be required!', 'icons/activity-logs/confused');
        }
    }

    public function createTrinityBlade(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + max($petWithSkills->getDexterity()->getTotal(), $petWithSkills->getIntelligence()->getTotal()) + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 25)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Antipode', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Lightning Sword', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(6);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% smithed a Trinity Blade!', 'items/tool/sword/elemental')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 25)
            ;
            $this->inventoryService->petCollectsItem('Trinity Blade', $pet, $pet->getName() . ' created this by hammering a Lightning Sword and Antipode together!', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to make a Trinity Blade, but wasn\'t sure where to begin...', 'icons/activity-logs/confused');
        }
    }

    public function createWandOfIce(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + max($petWithSkills->getDexterity()->getTotal(), $petWithSkills->getIntelligence()->getTotal()) + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 19)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Poker', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Everice', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(4);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% smithed a Wand of Ice!', 'items/tool/wand/ice')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 19)
            ;
            $this->inventoryService->petCollectsItem('Wand of Ice', $pet, $pet->getName() . ' created this by hammering Everice into a Poker!', $activityLog);
            return $activityLog;
        }
        else if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);

            return $this->evericeMeltingService->doMeltEverice($pet, $pet->getName() . ' tried to make a Wand of Ice, but accidentally shattered the Everice!');
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Wand of Ice, but the Everice was being uncooperative :|', 'icons/activity-logs/confused');
        }
    }

    public function createIceFishing(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + max($petWithSkills->getDexterity()->getTotal(), $petWithSkills->getIntelligence()->getTotal()) + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Crooked Fishing Rod', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Everice', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% smithed Ice Fishing!', 'items/tool/wand/ice')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 19)
            ;
            $this->inventoryService->petCollectsItem('Ice Fishing', $pet, $pet->getName() . ' created this by hammering Everice into a Crooked Fishing Rod!', $activityLog);
            return $activityLog;
        }
        else if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            return $this->evericeMeltingService->doMeltEverice($pet, $pet->getName() . ' tried to make Ice Fishing, but accidentally shattered the Everice!');
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make Ice Fishing, but the Everice was being uncooperative :|', 'icons/activity-logs/confused');
        }
    }

    public function createCoke(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll === 1)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->inventoryService->loseItem('Iron Ore', $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-mt_rand(2, 24));
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to refine some Charcoal, but got burned while trying, and ruined the Charcoal! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= 12)
        {
            $getRareStone = mt_rand(1, $pet->hasMerit(MeritEnum::LUCKY) ? 50 : 100) === 1;
            $rareStone = ArrayFunctions::pick_one([ 'Blackonite', 'Firestone' ]);
            $attributeLuckiness = false;

            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Charcoal', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            $pet->increaseEsteem($getRareStone ? 8 : 1);

            if($getRareStone)
            {
                $attributeLuckiness = $pet->hasMerit(MeritEnum::LUCKY) && mt_rand(1, 4) > 1;

                if($attributeLuckiness)
                {
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% refined some Charcoal into Coke, and what\'s this?! There was a piece of ' . $rareStone . ' inside! Lucky~!', 'items/resource/coke')
                        ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                    ;
                }
                else
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% refined some Charcoal into Coke, and what\'s this?! There was a piece of ' . $rareStone . ' inside!', 'items/resource/coke');
            }
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% refined some Charcoal into Coke.', 'items/resource/coke');

            $this->inventoryService->petCollectsItem('Coke', $pet, $pet->getName() . ' refined this from Charcoal.', $activityLog);

            if($getRareStone)
            {
                if($attributeLuckiness)
                    $this->inventoryService->petCollectsItem($rareStone, $pet, $pet->getName() . ' found this while refining Charcoal into Coke! Lucky~!', $activityLog);
                else
                    $this->inventoryService->petCollectsItem($rareStone, $pet, $pet->getName() . ' found this while refining Charcoal into Coke!', $activityLog);
            }

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to refine Coke from Charcoal, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createIronBar(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->inventoryService->loseItem('Iron Ore', $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-mt_rand(2, 24));
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to refine some Iron Ore, but got burned while trying! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Iron Ore', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% refined some Iron Ore into an Iron Bar.', 'items/element/iron-pure');
            $this->inventoryService->petCollectsItem('Iron Bar', $pet, $pet->getName() . ' refined this from Iron Ore.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to refine Iron Ore into an Iron Bar, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createSilverBar(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->inventoryService->loseItem('Silver Ore', $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-mt_rand(2, 12));
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to refine some Silver Ore, but got burned while trying! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Silver Ore', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% refined some Silver Ore into a Silver Bar.', 'items/element/silver-pure');
            $this->inventoryService->petCollectsItem('Silver Bar', $pet, $pet->getName() . ' refined this from Silver Ore.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to refine Silver Ore into a Silver Bar, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createGoldBar(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->inventoryService->loseItem('Gold Ore', $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-mt_rand(2, 8));
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to refine some Gold Ore, but got burned while trying! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Gold Ore', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% refined some Gold Ore into a Gold Bar.', 'items/element/gold-pure');
            $this->inventoryService->petCollectsItem('Gold Bar', $pet, $pet->getName() . ' refined this from Gold Ore.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to refine Gold Ore into a Gold Bar, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }
}
