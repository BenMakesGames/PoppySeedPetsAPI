<?php
namespace App\Service\PetActivity\Crafting\Helpers;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\EnumInvalidValueException;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\TransactionService;

class StickCraftingService
{
    private $petExperienceService;
    private $inventoryService;
    private $responseService;
    private $transactionService;

    public function __construct(
        PetExperienceService $petExperienceService, InventoryService $inventoryService, ResponseService $responseService,
        TransactionService $transactionService
    )
    {
        $this->petExperienceService = $petExperienceService;
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->transactionService = $transactionService;
    }

    public function createCrookedFishingRod(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + max($pet->getCrafts(), $pet->getNature()));

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Crooked Fishing Rod, but broke the String :(', 'icons/activity-logs/broke-string');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Crooked Fishing Rod, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');

            }
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Crooked Fishing Rod.', '');
            $this->inventoryService->petCollectsItem('Crooked Fishing Rod', $pet, $pet->getName() . ' created this from String and a Crooked Stick.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Crooked Fishing Rod, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }
    public function createSunflowerStick(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + max($pet->getCrafts(), $pet->getNature()));

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);

            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Sunflower Stick, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
        }
        else if($roll >= 10)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Sunflower', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Sunflower Stick.', '');
            $this->inventoryService->petCollectsItem('Sunflower Stick', $pet, $pet->getName() . ' created this by affixing a Sunflower to the end of a Crooked Stick.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' wanted to make something out of a Sunflower, but couldn\'t come up with anything...', 'icons/activity-logs/confused');
        }
    }

    public function createTorchOrFlag(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts());

        $making = ArrayFunctions::pick_one([
            'Stereotypical Torch',
            'White Flag'
        ]);

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(15, 30), PetActivityStatEnum::CRAFT, false);
            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseEsteem(-1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a ' . $making . ', but accidentally tore the White Cloth into useless shapes :(', 'icons/activity-logs/torn-to-bits');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a ' . $making . ', but accidentally split the Crooked Stick :(', 'icons/activity-logs/broke-stick');
            }
        }
        else if($roll >= 8)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 45), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a ' . $making . '.', '');
            $this->inventoryService->petCollectsItem($making, $pet, $pet->getName() . ' created this from White Cloth and a Crooked Stick.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(15, 45), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a ' . $making . ', but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createHuntingSpear(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + max($pet->getCrafts(), $pet->getSkills()->getBrawl()));

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Hunting Spear, but broke the String :(', 'icons/activity-logs/broke-string');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Hunting Spear, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
            }
        }
        else if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Talon', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Hunting Spear.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
            ;
            $this->inventoryService->petCollectsItem('Hunting Spear', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Hunting Spear, but couldn\'t quite figure it out.', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createStrawBroom(Pet $pet): PetActivityLog
    {
        $craftsCheck = mt_rand(1, 20 + $pet->getCrafts() + $pet->getDexterity() + $pet->getIntelligence());

        if($craftsCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a broom, but spilled the Glue :(', '');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a broom, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
            }
        }
        else if($craftsCheck >= 13)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->inventoryService->loseOneOf([ 'Rice', 'Wheat' ], $pet->getOwner(), LocationEnum::HOME);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Straw Broom.', '');
            $this->inventoryService->petCollectsItem('Straw Broom', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a broom, but couldn\'t quite figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createBugCatchersNet(Pet $pet): PetActivityLog
    {
        $craftsCheck = mt_rand(1, 20 + max($pet->getCrafts(), $pet->getSkills()->getNature()) + $pet->getDexterity() + $pet->getIntelligence());

        if($craftsCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('Cobweb', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Bug-catcher\'s Net, but a passing fly got stuck in the Cobweb, and tangled it up! >:(', '');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Bug catcher\'s Net, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
            }
        }
        else if($craftsCheck >= 13)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Cobweb', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Bug-catcher\'s Net.', '');
            $this->inventoryService->petCollectsItem('Bug-catcher\'s Net', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Bug-catcher\'s Net, but couldn\'t quite figure it out.', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createHarvestStaff(Pet $pet): PetActivityLog
    {
        $craftsCheck = mt_rand(1, 20 + $pet->getCrafts() + $pet->getDexterity() + $pet->getIntelligence());

        if($craftsCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            if($pet->getFood() <= 4)
            {
                $foodEaten = $this->inventoryService->loseOneOf([ 'Rice', 'Corn' ], $pet->getOwner(), LocationEnum::HOME);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseFood(4);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' started to make a Harvest Staff, but got hungry, and ate the ' . $foodEaten . ' :(', '');
            }
            else if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Harvest Staff, but broke the String :(', 'icons/activity-logs/broke-string');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a staff, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
            }
        }
        else if($craftsCheck >= 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Rice', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Corn', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Harvest Staff.', '');
            $this->inventoryService->petCollectsItem('Harvest Staff', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a staff, but couldn\'t decide what kind...', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createVeryLongSpear(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + max($pet->getCrafts(), $pet->getSkills()->getBrawl()));

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to extend a Hunting Spear, but broke the String :(', 'icons/activity-logs/broke-string');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to extend a Hunting Spear, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
            }
        }
        else if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Hunting Spear', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created an Overly-long Spear.', '');
            $this->inventoryService->petCollectsItem('Overly-long Spear', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to extend a Hunting Spear, but couldn\'t quite figure it out.', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createRidiculouslyLongSpear(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + max($pet->getCrafts(), $pet->getSkills()->getBrawl()));

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to extend an Overly-long Spear, but broke the String :(', 'icons/activity-logs/broke-string');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to extend an Overly-long Spear, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
            }
        }
        else if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Overly-long Spear', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a - like - CRAZY-long Spear. It\'s really rather silly.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;
            $this->inventoryService->petCollectsItem('This is Getting Ridiculous', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' considered extending an Overly-long Spear, but then thought that maybe that was going a bit overboard.', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createChampignon(Pet $pet): PetActivityLog
    {
        $quintessenceHandling = mt_rand(1, 10 + $pet->getUmbra());

        if($quintessenceHandling <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Champignon, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }

        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts());

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);

            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Champignon, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Toadstool', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS,  PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Champignon.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem('Champignon', $pet, $pet->getName() . ' created this from a Crooked Stick, Toadstool, and bit of Quintessence.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS,  PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Champignon, but couldn\'t quite figure it out.', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createWoodenSword(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + max($pet->getCrafts(), $pet->getSkills()->getBrawl()));

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            if(mt_rand(1, 2) === 1)
            {
                $itemLost = $this->inventoryService->loseOneOf([ 'String', 'Glue' ], $pet->getOwner(), LocationEnum::HOME);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);

                if($itemLost === 'string')
                    return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Wooden Sword, but broke the String :(', 'icons/activity-logs/broke-string');
                else
                    return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Wooden Sword, but spilt the Glue :(', '');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Wooden Sword, but broke the Crooked Stick :( Like, more than the one time needed.', 'icons/activity-logs/broke-stick');

            }
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, true);
            $itemLost = $this->inventoryService->loseOneOf([ 'String', 'Glue' ], $pet->getOwner(), LocationEnum::HOME);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Wooden Sword.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 12)
            ;
            $this->inventoryService->petCollectsItem('Wooden Sword', $pet, $pet->getName() . ' created this from some ' . $itemLost . ' and a Crooked Stick.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Wooden Sword, but couldn\'t quite figure it out.', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createRusticMagnifyingGlass(Pet $pet): PetActivityLog
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
                    return $this->responseService->createActivityLog($pet, $pet->getName() . ' started to make a lens from a piece of glass, and cut themselves! :( They managed to save the glass, though! ' . $pet->getName() . ' is kind of proud of that.', 'icons/activity-logs/wounded');
                }
                else
                    return $this->responseService->createActivityLog($pet, $pet->getName() . ' started to make a lens from a piece of glass, and cut themselves! :( They managed to save the glass, though!', 'icons/activity-logs/wounded');
            }
            else
            {
                $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseEsteem(-2);
                $pet->increaseSafety(-4);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' started to make a lens from a piece of glass, but cut themselves, and dropped the glass :(', 'icons/activity-logs/wounded');
            }
        }
        else if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a "Rustic" Magnifying Glass.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
            ;
            $this->inventoryService->petCollectsItem('"Rustic" Magnifying Glass', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a magnifying glass, but almost broke the glass, and gave up.', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createSweetBeat(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts());
        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->inventoryService->loseItem('Sweet Beet', $pet->getOwner(), LocationEnum::HOME, 1);
                $pet->increaseEsteem(-1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Sweet Beat, but the Glue got all over the beet, wasting both :(', '');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Sweet Beat, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
            }
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::CRAFT, true);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Sweet Beet', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Sweet Beat.', 'items/resource/string')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem('Sweet Beat', $pet, $pet->getName() . ' created this by gluing a Sweet Beet to a Stick. Because that makes sense.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' started to create a Sweet Beat, but wasn\'t able to make any meaningful progress.', 'icons/activity-logs/confused');
        }
    }

}
