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
use App\Model\ComputedPetSkills;
use App\Repository\ItemRepository;
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
    private $itemRepository;

    public function __construct(
        PetExperienceService $petExperienceService, InventoryService $inventoryService, ResponseService $responseService,
        TransactionService $transactionService, ItemRepository $itemRepository
    )
    {
        $this->petExperienceService = $petExperienceService;
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->transactionService = $transactionService;
        $this->itemRepository = $itemRepository;
    }

    public function createCrookedFishingRod(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getNature()->getTotal()));

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Crooked Fishing Rod, but broke the String :(', 'icons/activity-logs/broke-string');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Crooked Fishing Rod, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');

            }
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Crooked Fishing Rod.', '');
            $this->inventoryService->petCollectsItem('Crooked Fishing Rod', $pet, $pet->getName() . ' created this from String and a Crooked Stick.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Crooked Fishing Rod, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }
    public function createSunflowerStick(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getNature()->getTotal()));

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);

            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Sunflower Stick, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
        }
        else if($roll >= 10)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Sunflower', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Sunflower Stick.', '');
            $this->inventoryService->petCollectsItem('Sunflower Stick', $pet, $pet->getName() . ' created this by affixing a Sunflower to the end of a Crooked Stick.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to make something out of a Sunflower, but couldn\'t come up with anything...', 'icons/activity-logs/confused');
        }
    }

    public function createTorchOrFlag(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        $making = $this->itemRepository->findOneByName(ArrayFunctions::pick_one([
            'Stereotypical Torch',
            'White Flag'
        ]));

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(15, 30), PetActivityStatEnum::CRAFT, false);
            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseEsteem(-1);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make ' . $making->getNameWithArticle() . ', but accidentally tore the White Cloth into useless shapes :(', 'icons/activity-logs/torn-to-bits');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make ' . $making->getNameWithArticle() . ', but accidentally split the Crooked Stick :(', 'icons/activity-logs/broke-stick');
            }
        }
        else if($roll >= 8)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 45), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('White Cloth', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created ' . $making->getNameWithArticle() . '.', '');
            $this->inventoryService->petCollectsItem($making, $pet, $pet->getName() . ' created this from White Cloth and a Crooked Stick.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(15, 45), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make ' . $making->getNameWithArticle() . ', but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function createHuntingSpear(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $pet->getSkills()->getBrawl()));

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Hunting Spear, but broke the String :(', 'icons/activity-logs/broke-string');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Hunting Spear, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
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
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Hunting Spear.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
            ;
            $this->inventoryService->petCollectsItem('Hunting Spear', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Hunting Spear, but couldn\'t quite figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createStrawBroom(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if(mt_rand(1, 100) === 1 && $this->inventoryService->loseItem('Wheat', $pet->getOwner(), LocationEnum::HOME))
        {
            if(mt_rand(1, 4) === 1)
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make a Straw Broom, but a weird-lookin\' elf, or something, ran in, turned the Wheat into a Gold Bar, and left! And guess what kind of broom you can\'t make out of a Gold Bar! A STRAW ONE!', '');
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make a Straw Broom, but a weird-lookin\' elf, or something, ran in, turned the Wheat into a Gold Bar, and left!', '');

            $activityLog->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY);

            $this->inventoryService->petCollectsItem('Gold Bar', $pet, $pet->getName() . ' was going to make a Straw Broom, but a weird-lookin\' elf, or something, turned the Wheat into this Gold Bar before ' . $pet->getName() . ' could even get started!', $activityLog);

            return $activityLog;
        }

        $craftsCheck = mt_rand(1, 20 + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($craftsCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a broom, but spilled the Glue :(', '');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a broom, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
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
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Straw Broom.', '');
            $this->inventoryService->petCollectsItem('Straw Broom', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a broom, but couldn\'t quite figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createBugCatchersNet(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $craftsCheck = mt_rand(1, 20 + max($petWithSkills->getCrafts()->getTotal(), $pet->getSkills()->getNature()) + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($craftsCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('Cobweb', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Bug-catcher\'s Net, but a passing fly got stuck in the Cobweb, and tangled it up! >:(', '');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Bug catcher\'s Net, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
            }
        }
        else if($craftsCheck >= 13)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Cobweb', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Bug-catcher\'s Net.', '');
            $this->inventoryService->petCollectsItem('Bug-catcher\'s Net', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Bug-catcher\'s Net, but couldn\'t quite figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createHarvestStaff(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $craftsCheck = mt_rand(1, 20 + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($craftsCheck <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            if($pet->getFood() <= 4)
            {
                $foodEaten = $this->inventoryService->loseOneOf([ 'Rice', 'Corn' ], $pet->getOwner(), LocationEnum::HOME);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseFood(4);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make a Harvest Staff, but got hungry, and ate the ' . $foodEaten . ' :(', '');
            }
            else if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Harvest Staff, but broke the String :(', 'icons/activity-logs/broke-string');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a staff, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
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
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Harvest Staff.', '');
            $this->inventoryService->petCollectsItem('Harvest Staff', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a staff, but couldn\'t decide what kind...', 'icons/activity-logs/confused');
        }
    }

    public function createVeryLongSpear(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $pet->getSkills()->getBrawl()));

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to extend a Hunting Spear, but broke the String :(', 'icons/activity-logs/broke-string');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to extend a Hunting Spear, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
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
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created an Overly-long Spear.', '');
            $this->inventoryService->petCollectsItem('Overly-long Spear', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to extend a Hunting Spear, but couldn\'t quite figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createRidiculouslyLongSpear(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $pet->getSkills()->getBrawl()));

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to extend an Overly-long Spear, but broke the String :(', 'icons/activity-logs/broke-string');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to extend an Overly-long Spear, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
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
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a - like - CRAZY-long Spear. It\'s really rather silly.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;
            $this->inventoryService->petCollectsItem('This is Getting Ridiculous', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% considered extending an Overly-long Spear, but then thought that maybe that was going a bit overboard.', 'icons/activity-logs/confused');
        }
    }

    public function createChampignon(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $quintessenceHandling = mt_rand(1, 10 + $petWithSkills->getUmbra()->getTotal());

        if($quintessenceHandling <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::MAGIC_BIND, false);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Champignon, but mishandled the Quintessence; it evaporated back into the fabric of the universe :(', '');
        }

        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);

            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Champignon, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Quintessence', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Toadstool', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS,  PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Champignon.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem('Champignon', $pet, $pet->getName() . ' created this from a Crooked Stick, Toadstool, and bit of Quintessence.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS,  PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Champignon, but couldn\'t quite figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createWoodenSword(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $pet->getSkills()->getBrawl()));

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            if(mt_rand(1, 2) === 1)
            {
                $itemLost = $this->inventoryService->loseOneOf([ 'String', 'Glue' ], $pet->getOwner(), LocationEnum::HOME);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);

                if($itemLost === 'String')
                    return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Wooden Sword, but broke the String :(', 'icons/activity-logs/broke-string');
                else
                    return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Wooden Sword, but spilt the Glue :(', '');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Wooden Sword, but broke the Crooked Stick :( Like, more than the one time needed.', 'icons/activity-logs/broke-stick');

            }
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, true);
            $itemLost = $this->inventoryService->loseOneOf([ 'String', 'Glue' ], $pet->getOwner(), LocationEnum::HOME);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Wooden Sword.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 12)
            ;
            $this->inventoryService->petCollectsItem('Wooden Sword', $pet, $pet->getName() . ' created this from some ' . $itemLost . ' and a Crooked Stick.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Wooden Sword, but couldn\'t quite figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createRusticMagnifyingGlass(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);

            if(mt_rand(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStamina()->getTotal()) >= 18)
            {
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseEsteem(2);
                $pet->increaseSafety(-4);

                if(mt_rand(1, 4) === 1)
                {
                    $pet->increaseEsteem(2);
                    return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make a lens from a piece of glass, and cut themselves! :( They managed to save the glass, though! ' . $pet->getName() . ' is kind of proud of that.', 'icons/activity-logs/wounded');
                }
                else
                    return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make a lens from a piece of glass, and cut themselves! :( They managed to save the glass, though!', 'icons/activity-logs/wounded');
            }
            else
            {
                $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseEsteem(-2);
                $pet->increaseSafety(-4);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make a lens from a piece of glass, but cut themselves, and dropped the glass :(', 'icons/activity-logs/wounded');
            }
        }
        else if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Glass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a "Rustic" Magnifying Glass.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
            ;
            $this->inventoryService->petCollectsItem('"Rustic" Magnifying Glass', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a magnifying glass, but almost broke the glass, and gave up.', 'icons/activity-logs/confused');
        }
    }

    public function createSweetBeat(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('Glue', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->inventoryService->loseItem('Sweet Beet', $pet->getOwner(), LocationEnum::HOME, 1);
                $pet->increaseEsteem(-1);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Sweet Beat, but the Glue got all over the beet, wasting both :(', '');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Sweet Beat, but broke the Crooked Stick :(', 'icons/activity-logs/broke-stick');
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
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Sweet Beat.', 'items/resource/string')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem('Sweet Beat', $pet, $pet->getName() . ' created this by gluing a Sweet Beet to a Stick. Because that makes sense.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to create a Sweet Beat, but wasn\'t able to make any meaningful progress.', 'icons/activity-logs/confused');
        }
    }

}
