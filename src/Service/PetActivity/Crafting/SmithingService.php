<?php
namespace App\Service\PetActivity\Crafting;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\EnumInvalidValueException;
use App\Enum\GuildEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Model\ActivityCallback;
use App\Repository\ItemRepository;
use App\Repository\SpiceRepository;
use App\Service\CalendarService;
use App\Service\InventoryService;
use App\Service\PetActivity\Crafting\Helpers\EvericeMeltingService;
use App\Service\PetActivity\Crafting\Helpers\GoldSmithingService;
use App\Service\PetActivity\Crafting\Helpers\HalloweenSmithingService;
use App\Service\PetActivity\Crafting\Helpers\IronSmithingService;
use App\Service\PetActivity\Crafting\Helpers\MeteoriteSmithingService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\TransactionService;

class SmithingService
{
    private $inventoryService;
    private $responseService;
    private $petExperienceService;
    private $transactionService;
    private $goldSmithingService;
    private $ironSmithingService;
    private $meteoriteSmithingService;
    private $halloweenSmithingService;
    private $calendarService;
    private $spiceRepository;
    private $evericeMeltingService;

    public function __construct(
        InventoryService $inventoryService, ResponseService $responseService, PetExperienceService $petExperienceService,
        TransactionService $transactionService, GoldSmithingService $goldSmithingService,
        IronSmithingService $ironSmithingService, MeteoriteSmithingService $meteoriteSmithingService,
        HalloweenSmithingService $halloweenSmithingService, CalendarService $calendarService,
        SpiceRepository $spiceRepository, EvericeMeltingService $evericeMeltingService
    )
    {
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->petExperienceService = $petExperienceService;
        $this->transactionService = $transactionService;
        $this->goldSmithingService = $goldSmithingService;
        $this->ironSmithingService = $ironSmithingService;
        $this->meteoriteSmithingService = $meteoriteSmithingService;
        $this->halloweenSmithingService = $halloweenSmithingService;
        $this->calendarService = $calendarService;
        $this->spiceRepository = $spiceRepository;
        $this->evericeMeltingService = $evericeMeltingService;
    }

    public function getCraftingPossibilities(Pet $pet, array $quantities): array
    {
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
                $possibilities[] = new ActivityCallback($this->ironSmithingService, 'createHeavyHammer', $pet->getStrength() >= 3 ? $weight : ceil($weight / 2));

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
            $possibilities[] = new ActivityCallback($this, 'createSilverKey', $weight);
            $possibilities[] = new ActivityCallback($this, 'createBasicSilverCraft', $weight);

            if(array_key_exists('Crown Coral', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createCoralTrident', 10);

            if(array_key_exists('"Rustic" Magnifying Glass', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createElvishMagnifyingGlass', 10);

            if(array_key_exists('Leaf Spear', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createSylvanFishingRod', 10);

            if(array_key_exists('Glass', $quantities))
            {
                if(array_key_exists('Silica Grounds', $quantities))
                    $possibilities[] = new ActivityCallback($this, 'createHourglass', $weight);
            }

            if(array_key_exists('Gold Key', $quantities) && array_key_exists('White Cloth', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createGoldKeyblade', 10);
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
                $possibilities[] = new ActivityCallback($this, 'createSilverKeyblade', 10);

            if(array_key_exists('Leaf Spear', $quantities) && array_key_exists('Iron Bar', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createSiderealLeafSpear', 10);
        }

        if(array_key_exists('Silver Bar', $quantities) && array_key_exists('Gold Bar', $quantities) && array_key_exists('White Cloth', $quantities))
            $possibilities[] = new ActivityCallback($this, 'createCeremonialTrident', 10);

        if(array_key_exists('Iron Sword', $quantities))
        {
            if(array_key_exists('Scales', $quantities) && array_key_exists('Fluff', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createDragonscale', 10);

            if(array_key_exists('Dark Scales', $quantities) && array_key_exists('Fluff', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createDrakkonscale', 10);

            if(array_key_exists('Everice', $quantities) && array_key_exists('Firestone', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createAntipode', 10);
        }

        if(array_key_exists('Antipode', $quantities) && array_key_exists('Lightning Sword', $quantities))
            $possibilities[] = new ActivityCallback($this, 'createTrinityBlade', 10);

        if(array_key_exists('Poker', $quantities) && array_key_exists('Everice', $quantities))
            $possibilities[] = new ActivityCallback($this, 'createWandOfIce', 10);

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

    public function createTriColorScissors(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + max($pet->getDexterity(), $pet->getIntelligence()) + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);

            $lostItem = ArrayFunctions::pick_one([
                'Yellow Scissors', 'Green Scissors'
            ]);

            $this->inventoryService->loseItem($lostItem, $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(-1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' started to make Tri-color Scissors, but totally broke the ' . $lostItem . '! :( All that\'s left is the blade (in the form of an Iron Bar - how convenient!)', '');

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

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' combined two pairs of scissors, creating Tri-color Scissors!', 'items/tool/scissors/tri-color')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;
            $this->inventoryService->petCollectsItem('Tri-color Scissors', $pet, $pet->getName() . ' made this by combining two pairs of scissors!', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make Tri-color Scissors, but got confused just thinking about what it would even look like...', 'icons/activity-logs/confused');
        }
    }

    public function createPapersBane(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + max($pet->getDexterity(), $pet->getIntelligence()) + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

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

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make Paper\'s Bane, but melted the Tri-color Scissors, leaving only ' . $gainedItem . '! (And getting slightly singed...)', '');

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

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' infused Tri-color Scissors with the eternal heat of Firestone!', 'items/tool/scissors/papersbane')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
            ;
            $this->inventoryService->petCollectsItem('Paper\'s Bane', $pet, $pet->getName() . ' made this by infusing Tri-color Scissors with the eternal heat of Firestone!', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make Paper\'s Bane, but almost burned themselves on the Firestone...', 'icons/activity-logs/confused');
        }
    }

    public function createRedWarpingWand(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getDexterity() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

        if($roll >= 26)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Warping Wand', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Firestone', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(6);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' infused a Warping Wand with the eternal heat of Firestone!', 'items/tool/scissors/papersbane')
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
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Red Warping Wand, but almost burned themselves on the Firestone...', 'icons/activity-logs/confused');
            else
            {
                $location = ArrayFunctions::pick_one([ 'on the roof', 'in the bathtub', 'in the dishwasher', 'under your bed', 'in your closet', 'in the mailbox' ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Red Warping Wand, but accidentally warped the Firestone away. They looked around for a while, and finally found it ' . $location . '.', 'icons/activity-logs/confused');
            }
        }
    }

    public function createElvishMagnifyingGlass(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to improve a "Rustic" Magnifying Glass, but burnt it. All that\'s left now is the Glass...', '');

            $this->inventoryService->loseItem('"Rustic" Magnifying Glass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->petCollectsItem('Glass', $pet, $pet->getName() . ' burnt a "Rustic" Magnifying Glass; this is all that remained.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-2);

            return $activityLog;
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Silver Bar', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('"Rustic" Magnifying Glass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created an Elvish Magnifying Glass.', '');
            $this->inventoryService->petCollectsItem('Elvish Magnifying Glass', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to improve a "Rustic" Magnifying Glass, but nearly burnt it to a crisp in the process! (Nearly!)', 'icons/activity-logs/confused');
        }
    }

    public function createCoralTrident(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a trident out of Crown Coral, but shattered the coral completely :(', '');

            $this->inventoryService->loseItem('Crown Coral', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-mt_rand(2, 4));

            return $activityLog;
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Silver Bar', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crown Coral', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Coral Trident.', '');
            $this->inventoryService->petCollectsItem('Coral Trident', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' started making a Coral Trident, but working with coral is tricky! They gave up after a while...', 'icons/activity-logs/confused');
        }
    }

    public function createSylvanFishingRod(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts() + $pet->getSmithing());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Sylvan Fishing Rod, but ruined the silver :|', '');

            $this->inventoryService->loseItem('Silver Bar', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-2);

            return $activityLog;
        }
        else if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Silver Bar', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Leaf Spear', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' machined some silver components onto a Leaf Spear, making it a Sylvan Fishing Rod!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;
            $this->inventoryService->petCollectsItem('Sylvan Fishing Rod', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to machine some silver, but hand-making tiny gears proved too challenging...', 'icons/activity-logs/confused');
        }
    }

    public function createSiderealLeafSpear(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts() + $pet->getSmithing());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);

            if(mt_rand(1, 2) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a little iron moon, but ended up completely ruining the iron :|', '');

                $this->inventoryService->loseItem('Iron Bar', $pet->getOwner(), LocationEnum::HOME, 1);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a little gold sun, but ended up completely ruining the gold :|', '');

                $this->inventoryService->loseItem('Gold Bar', $pet->getOwner(), LocationEnum::HOME, 1);
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-2);

            return $activityLog;
        }
        else if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Iron Bar', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Gold Bar', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Leaf Spear', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' decorated a Leaf Spear with a little sun and moon, creating a Sidereal Leaf Spear!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;
            $this->inventoryService->petCollectsItem('Sidereal Leaf Spear', $pet, $pet->getName() . ' created this by attaching a little gold sun and iron moon to a Leaf Spear.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a little gold sun and iron moon, but couldn\'t get the shapes just right...', 'icons/activity-logs/confused');
        }
    }

    public function createGoldKeyblade(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a keyblade, but accidentally tore the White Cloth :|', '');

            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-1);

            return $activityLog;
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Silver Bar', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Gold Key', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Gold Keyblade.', '');
            $this->inventoryService->petCollectsItem('Gold Keyblade', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a keyblade, but couldn\'t get the hilt right...', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createSilverKeyblade(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a keyblade, but accidentally tore the White Cloth :|', '');

            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-1);

            return $activityLog;
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Gold Bar', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Silver Key', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Silver Keyblade.', '');
            $this->inventoryService->petCollectsItem('Silver Keyblade', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a keyblade, but couldn\'t get the hilt right...', 'icons/activity-logs/confused');
        }
    }

    public function createHourglass(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + max($pet->getStamina(), $pet->getDexterity()) + $pet->getCrafts() + $pet->getSmithing());

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);

            $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-2);
            $pet->increaseSafety(-4);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried blowing Glass, but burnt themselves, and dropped the glass :(', 'icons/activity-logs/wounded');
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Silver Bar', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Silica Grounds', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created an Hourglass.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem('Hourglass', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make an Hourglass, but it\'s so detailed and fiddly! Ugh!', 'icons/activity-logs/confused');
        }
    }

    public function createMirror(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);

            $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-2);
            $pet->increaseSafety(-4);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried silvering some Glass, but burnt themselves, and dropped the glass :(', 'icons/activity-logs/wounded');
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
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Dark Mirror.', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
                ;
                $this->inventoryService->petCollectsItem('Dark Mirror', $pet, $pet->getName() . ' created this.', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Mirror.', '')
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
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Mirror, but couldn\'t get the Glass smooth enough.', 'icons/activity-logs/confused');
        }
    }

    public function createGlass(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());
        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->inventoryService->loseItem('Silica Grounds', $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-mt_rand(2, 12));
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make Glass, but got burned while trying! :(', 'icons/activity-logs/burn');
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
                    $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' melted Silica Grounds and Limestone into TWO Glass!', 'items/mineral/silica-glass');
                    $this->inventoryService->petCollectsItem('Glass', $pet, $pet->getName() . ' created this from Silica Grounds and Limestone.', $activityLog);
                }
                else
                {
                    $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' melted Silica Grounds and Limestone into Glass.', 'items/mineral/silica-glass');
                }
            }
            else
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' melted Silica Grounds and Limestone into Glass. (There\'s plenty of Limestone left over, though!)', 'items/mineral/silica-glass');

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
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make Glass, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createCrystalBall(Pet $pet): PetActivityLog
    {
        $lucky = $pet->hasMerit(MeritEnum::LUCKY) && mt_rand(1, 60) === 1;

        if(mt_rand(1, 60) === 1 || $lucky)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);

            $luckySuffix = $lucky ? ' Lucky~!' : '';

            $pet->increaseEsteem(mt_rand(4, 8));

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' started to make a Crystal Ball out of Glass, but happened to catch the light just right, and got a Rainbow, instead!' . $luckySuffix, 'items/mineral/silica-glass-ball')
                ->addInterestingness($lucky ? PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT : PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ;
            $this->inventoryService->petCollectsItem('Rainbow', $pet, $pet->getName() . ' created this on accident while trying to make a Crystal Ball!' . $luckySuffix, $activityLog);
            return $activityLog;
        }

        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts() + $pet->getSmithing());

        if($roll <= 1)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);

            $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);

            $pet->increaseEsteem(-2);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Crystal Ball, but slipped and dropped it! :(', '');
        }
        else if($roll >= 20 && mt_rand(1, 3) === 1)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(mt_rand(2, 4));

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' made TWO Crystal Balls out of Glass!', 'items/mineral/silica-glass-ball')
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

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' made a Crystal Ball out of Glass.', 'items/mineral/silica-glass-ball')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 12)
            ;
            $this->inventoryService->petCollectsItem('Crystal Ball', $pet, $pet->getName() . ' created this from Glass.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Crystal Ball, but making a perfect sphere was proving difficult!', 'icons/activity-logs/confused');
        }
    }

    public function createFiberglass(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

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
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make Fiberglass, but got burned while trying! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= 25 && mt_rand(1, 3) === 1)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(mt_rand(3, 6));

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' made TWO bundles of Fiberglass from Glass and Plastic!', 'items/resource/fiberglass')
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

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' made a bundle of Fiberglass from Glass and Plastic.', 'items/resource/fiberglass')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem('Fiberglass', $pet, $pet->getName() . ' created this from Glass and Plastic.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make Fiberglass, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createFiberglassBow(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-1);

            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Fiberglass Bow, but burnt the String :(', 'icons/activity-logs/broke-string');
        }
        else if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Fiberglass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' made a Fiberglass Bow.', 'items/tool/bow/fiberglass')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
            ;
            $this->inventoryService->petCollectsItem('Fiberglass Bow', $pet, $pet->getName() . ' created this from Fiberglass, and String.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Fiberglass Bow, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createCeremonialTrident(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $pet->increaseEsteem(-mt_rand(1, 3));

            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Ceremonial Trident, but completely destroyed the White Cloth, leaving only String behind! :(', '');
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
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Ceremonial Trident, but melted the heck out of the ' . $lost . '! :( ' . $pet->getName() . ' decided to make some coins out of it, instead, and got ' . $moneys . '~~m~~.', '');
        }
        else if($roll >= 17)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Silver Bar', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Gold Bar', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('White cloth', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, mt_rand(2, 3), [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(4);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' made a Ceremonial Trident!', 'items/tool/spear/trident-ceremonial')
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
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Ceremonial Trident, but couldn\'t get the shape just right...', 'icons/activity-logs/confused');
            else
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Ceremonial Spear, but halfway through realized that they had misremembered the item name... >_>', 'icons/activity-logs/confused');
        }
    }

    public function createAntipode(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + max($pet->getDexterity(), $pet->getIntelligence()) + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

        if($roll >= 22)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Iron Sword', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Everice', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Firestone', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(6);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' smithed Antipode!', 'items/tool/sword/antipode')
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
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make Antipode, but the ' . ArrayFunctions::pick_one([ 'Everice', 'Firestone' ]) . ' was being uncooperative :|', 'icons/activity-logs/confused');
        }
    }

    public function createDragonscale(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + max($pet->getDexterity(), $pet->getIntelligence()) + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->inventoryService->loseItem('Scales', $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(-1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' started to make Dragonscale, but overheated the Scales, causing them to tear and crumble! :(', '');
        }
        else if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Iron Sword', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Fluff', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Scales', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(4);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' upgraded an Iron Sword into Dragonscale!', 'items/tool/sword/dragon-scale')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;
            $this->inventoryService->petCollectsItem('Dragonscale', $pet, $pet->getName() . ' made this by inlaying Scales into an Iron Sword!', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make Dragonscale, but got intimidated by all the detailing work that would be required!', 'icons/activity-logs/confused');
        }
    }

    public function createDrakkonscale(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + max($pet->getDexterity(), $pet->getIntelligence()) + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->inventoryService->loseItem('Dark Scales', $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(-1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' started to make Drakkonscale, but overheated the Dark Scales, causing them to tear and crumble! :(', '');
        }
        else if($roll >= 17)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Iron Sword', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Fluff', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Dark Scales', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(4);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' upgraded an Iron Sword into Drakkonscale!', 'items/tool/sword/drakkon-scale')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;
            $this->inventoryService->petCollectsItem('Drakkonscale', $pet, $pet->getName() . ' made this by inlaying Dark Scales into an Iron Sword!', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make Drakkonscale, but got intimidated by all the detailing work that would be required!', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createTrinityBlade(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + max($pet->getDexterity(), $pet->getIntelligence()) + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

        if($roll >= 25)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Antipode', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Lightning Sword', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(6);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' smithed a Trinity Blade!', 'items/tool/sword/elemental')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 25)
            ;
            $this->inventoryService->petCollectsItem('Trinity Blade', $pet, $pet->getName() . ' created this by hammering a Lightning Sword and Antipode together!', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' wanted to make a Trinity Blade, but wasn\'t sure where to begin...', 'icons/activity-logs/confused');
        }
    }

    public function createWandOfIce(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + max($pet->getDexterity(), $pet->getIntelligence()) + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

        if($roll >= 19)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Poker', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Everice', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(4);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' smithed a Wand of Ice!', 'items/tool/wand/ice')
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
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Wand of Ice, but the Everice was being uncooperative :|', 'icons/activity-logs/confused');
        }
    }

    public function createCoke(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

        if($roll === 1)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->inventoryService->loseItem('Iron Ore', $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-mt_rand(2, 24));
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to refine some Charcoal, but got burned while trying, and ruined the Charcoal! :(', 'icons/activity-logs/burn');
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
                    $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' refined some Charcoal into Coke, and what\'s this?! There was a piece of ' . $rareStone . ' inside! Lucky~!', 'items/resource/coke')
                        ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                    ;
                }
                else
                    $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' refined some Charcoal into Coke, and what\'s this?! There was a piece of ' . $rareStone . ' inside!', 'items/resource/coke');
            }
            else
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' refined some Charcoal into Coke.', 'items/resource/coke');

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
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to refine Coke from Charcoal, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createIronBar(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());
        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->inventoryService->loseItem('Iron Ore', $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-mt_rand(2, 24));
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to refine some Iron Ore, but got burned while trying! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Iron Ore', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' refined some Iron Ore into an Iron Bar.', 'items/element/iron-pure');
            $this->inventoryService->petCollectsItem('Iron Bar', $pet, $pet->getName() . ' refined this from Iron Ore.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to refine Iron Ore into an Iron Bar, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createSilverBar(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());
        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->inventoryService->loseItem('Silver Ore', $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-mt_rand(2, 12));
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to refine some Silver Ore, but got burned while trying! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Silver Ore', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' refined some Silver Ore into a Silver Bar.', 'items/element/silver-pure');
            $this->inventoryService->petCollectsItem('Silver Bar', $pet, $pet->getName() . ' refined this from Silver Ore.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to refine Silver Ore into a Silver Bar, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createGoldBar(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());
        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->inventoryService->loseItem('Gold Ore', $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-mt_rand(2, 8));
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to refine some Gold Ore, but got burned while trying! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Gold Ore', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' refined some Gold Ore into a Gold Bar.', 'items/element/gold-pure');
            $this->inventoryService->petCollectsItem('Gold Bar', $pet, $pet->getName() . ' refined this from Gold Ore.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to refine Gold Ore into a Gold Bar, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createSilverKey(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());
        $reRoll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->inventoryService->loseItem('Silver Bar', $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-mt_rand(2, 12));
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to forge a Silver Key, but got burned while trying! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Silver Bar', $pet->getOwner(), LocationEnum::HOME, 1);

            $keys = mt_rand(1, 7) === 1 ? 2 : 1;

            if($keys === 2)
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' forged *two* Silver Keys from a Silver Bar!', 'items/key/silver');
            else
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' forged a Silver Key from a Silver Bar.', 'items/key/silver');

            $this->inventoryService->petCollectsItem('Silver Key', $pet, $pet->getName() . ' forged this from a Silver Bar.', $activityLog);

            if($keys === 2)
                $this->inventoryService->petCollectsItem('Silver Key', $pet, $pet->getName() . ' forged this from a Silver Bar.', $activityLog);

            $this->petExperienceService->gainExp($pet, $keys, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem($keys === 1 ? 1 : 6);

            return $activityLog;
        }
        else if($reRoll >= 12)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(75, 90), PetActivityStatEnum::SMITH, true);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $this->inventoryService->loseItem('Silver Bar', $pet->getOwner(), LocationEnum::HOME, 1);

            $moneys = mt_rand(10, 20);
            $this->transactionService->getMoney($pet->getOwner(), $moneys, $pet->getName() . ' tried to forge a Silver Key, but couldn\'t get the shape right, so just made silver coins, instead.');
            $pet->increaseFood(-1);

            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to forge a Silver Key from a Silver Bar, but couldn\'t get the shape right, so just made ' . $moneys . ' Moneys worth of silver coins, instead.', 'icons/activity-logs/moneys');
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to forge a Silver Key from a Silver Bar, but couldn\'t get the shape right.', 'icons/activity-logs/confused');
        }
    }

    public function createBasicSilverCraft(Pet $pet): PetActivityLog
    {
        $making = ArrayFunctions::pick_one([
            [ 'item' => 'Silver Colander', 'description' => 'a Silver Colander', 'image' => 'items/tool/colander', 'difficulty' => 13, 'experience' => 1 ],
        ]);

        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());
        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->inventoryService->loseItem('Silver Bar', $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-mt_rand(2, 12));
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to forge ' . $making['description'] . ', but got burned while trying! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= $making['difficulty'])
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Silver Bar', $pet->getOwner(), LocationEnum::HOME, 1);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' forged ' . $making['description'] . ' from a Silver Bar.', $making['image']);

            $this->inventoryService->petCollectsItem($making['item'], $pet, $pet->getName() . ' forged this from a Silver Bar.', $activityLog);

            $this->petExperienceService->gainExp($pet, $making['experience'], [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to forge ' . $making['description'] . ' from a Silver Bar, but couldn\'t get the shape right.', 'icons/activity-logs/confused');
        }
    }
}
