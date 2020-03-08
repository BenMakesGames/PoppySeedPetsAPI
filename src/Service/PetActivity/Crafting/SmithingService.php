<?php
namespace App\Service\PetActivity\Crafting;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\EnumInvalidValueException;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\PetActivity\Crafting\Helpers\GoldSmithingService;
use App\Service\PetActivity\Crafting\Helpers\IronSmithingService;
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

    public function __construct(
        InventoryService $inventoryService, ResponseService $responseService, PetExperienceService $petExperienceService,
        TransactionService $transactionService, GoldSmithingService $goldSmithingService,
        IronSmithingService $ironSmithingService
    )
    {
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->petExperienceService = $petExperienceService;
        $this->transactionService = $transactionService;
        $this->goldSmithingService = $goldSmithingService;
        $this->ironSmithingService = $ironSmithingService;
    }

    public function getCraftingPossibilities(Pet $pet, array $quantities): array
    {
        $possibilities = [];

        if(array_key_exists('Iron Ore', $quantities))
            $possibilities[] = [ $this, 'createIronBar' ];

        if(array_key_exists('Silver Ore', $quantities))
            $possibilities[] = [ $this, 'createSilverBar' ];

        if(array_key_exists('Gold Ore', $quantities))
            $possibilities[] = [ $this, 'createGoldBar' ];

        if(array_key_exists('Silica Grounds', $quantities) && array_key_exists('Limestone', $quantities))
            $possibilities[] = [ $this, 'createGlass' ];

        if(array_key_exists('Glass', $quantities) && array_key_exists('Plastic', $quantities))
            $possibilities[] = [ $this, 'createFiberglass' ];

        if(array_key_exists('Fiberglass', $quantities) && array_key_exists('String', $quantities))
            $possibilities[] = [ $this, 'createFiberglassBow' ];

        if(array_key_exists('Iron Bar', $quantities))
        {
            $possibilities[] = [ $this->ironSmithingService, 'createIronKey' ];
            $possibilities[] = [ $this->ironSmithingService, 'createBasicIronCraft' ];

            if(array_key_exists('Plastic', $quantities))
            {
                if(array_key_exists('Yellow Dye', $quantities))
                    $possibilities[] = [ $this->ironSmithingService, 'createYellowScissors' ];

                if(array_key_exists('Green Dye', $quantities))
                    $possibilities[] = [ $this->ironSmithingService, 'createGreenScissors' ];
            }

            if(array_key_exists('Crooked Stick', $quantities) && array_key_exists('Iron Bar', $quantities))
                $possibilities[] = [ $this->ironSmithingService, 'createScythe' ];

            if(array_key_exists('String', $quantities))
                $possibilities[] = [ $this->ironSmithingService, 'createGrapplingHook' ];

            if(array_key_exists('Dark Matter', $quantities) && $pet->getStrength() >= 3)
                $possibilities[] = [ $this->ironSmithingService, 'createHeavyHammer' ];

            if(array_key_exists('Mirror', $quantities))
                $possibilities[] = [ $this->ironSmithingService, 'createMirrorShield' ];
        }

        if(array_key_exists('Silver Bar', $quantities))
        {
            $possibilities[] = [ $this, 'createSilverKey' ];

            if(array_key_exists('"Rustic" Magnifying Glass', $quantities))
                $possibilities[] = [ $this, 'createElvishMagnifyingGlass' ];

            if(array_key_exists('Glass', $quantities))
            {
                if(array_key_exists('Silica Grounds', $quantities))
                    $possibilities[] = [ $this, 'createHourglass' ];
                else
                    $possibilities[] = [ $this, 'createMirror' ];
            }
        }

        if(array_key_exists('Gold Bar', $quantities))
        {
            $possibilities[] = [ $this->goldSmithingService, 'createGoldKey' ];

            if(mt_rand(1, 2) === 1)
                $possibilities[] = [ $this->goldSmithingService, 'createGoldTuningFork' ];

            if(array_key_exists('Fiberglass', $quantities) && array_key_exists('Moon Pearl', $quantities))
                $possibilities[] = [ $this->goldSmithingService, 'createMoonhammer' ];

            if(array_key_exists('Dark Scales', $quantities) && array_key_exists('Dragon Flag', $quantities))
                $possibilities[] = [ $this->goldSmithingService, 'createKundravsStandard' ];

            if(array_key_exists('String', $quantities))
                $possibilities[] = [ $this->goldSmithingService, 'createGoldTriangle' ];

            if(array_key_exists('Chanterelle', $quantities) && array_key_exists('Flute', $quantities))
                $possibilities[] = [ $this->goldSmithingService, 'createFungalClarinet' ];

            if(array_key_exists('Glass', $quantities))
                $possibilities[] = [ $this->goldSmithingService, 'createGoldTelescope' ];

            if(array_key_exists('Plastic Shovel', $quantities) && array_key_exists('Green Dye', $quantities))
                $possibilities[] = [ $this->goldSmithingService, 'createCoreopsis' ];

            if(array_key_exists('Plastic', $quantities) && array_key_exists('3D Printer', $quantities))
                $possibilities[] = [ $this->goldSmithingService, 'createGoldRod' ];
        }

        if(array_key_exists('Silver Bar', $quantities) && array_key_exists('Gold Bar', $quantities) && array_key_exists('White Cloth', $quantities))
            $possibilities[] = [ $this, 'createCeremonialTrident' ];

        if(array_key_exists('Iron Sword', $quantities) && array_key_exists('Everice', $quantities) && array_key_exists('Firestone', $quantities))
            $possibilities[] = [ $this, 'createAntipode' ];

        if(array_key_exists('Antipode', $quantities) && array_key_exists('Lightning Sword', $quantities))
            $possibilities[] = [ $this, 'createTrinityBlade' ];

        return $possibilities;
    }

    /**
     * @throws EnumInvalidValueException
     */
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

    /**
     * @throws EnumInvalidValueException
     */
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

    /**
     * @throws EnumInvalidValueException
     */
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
            $this->inventoryService->loseItem('Silver Bar', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Mirror.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
            ;
            $this->inventoryService->petCollectsItem('Mirror', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Mirror, but couldn\'t get the Glass smooth enough.', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
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
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' melted Silica Grounds and Limestone into Glass.', 'items/mineral/silica-glass');
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

    /**
     * @throws EnumInvalidValueException
     */
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
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' made Fiberglass from Glass and Plastic.', 'items/resource/fiberglass')
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

    /**
     * @throws EnumInvalidValueException
     */
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

    /**
     * @throws EnumInvalidValueException
     */
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
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Ceremonial Spear, but halfway through realized that they had misremembered the item name... >_>', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
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
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make Antipode, but the ' . ArrayFunctions::pick_one([ 'Everice', 'Firestone' ]) . ' was being uncooperative :|', 'icons/activity-logs/confused');
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

    /**
     * @throws EnumInvalidValueException
     */
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
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Wand of Ice, but the Everice was being uncooperative :|', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
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

    /**
     * @throws EnumInvalidValueException
     */
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

    /**
     * @throws EnumInvalidValueException
     */
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
}
