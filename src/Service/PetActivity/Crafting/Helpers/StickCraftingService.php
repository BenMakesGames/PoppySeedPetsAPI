<?php
namespace App\Service\PetActivity\Crafting\Helpers;

use App\Entity\PetActivityLog;
use App\Enum\EnumInvalidValueException;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Model\ComputedPetSkills;
use App\Repository\ItemRepository;
use App\Repository\PetActivityLogTagRepository;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;

class StickCraftingService
{
    private $petExperienceService;
    private $inventoryService;
    private $responseService;
    private $itemRepository;
    private IRandom $squirrel3;
    private HouseSimService $houseSimService;
    private PetActivityLogTagRepository $petActivityLogTagRepository;

    public function __construct(
        PetExperienceService $petExperienceService, InventoryService $inventoryService, ResponseService $responseService,
        ItemRepository $itemRepository, Squirrel3 $squirrel3, HouseSimService $houseSimService,
        PetActivityLogTagRepository $petActivityLogTagRepository
    )
    {
        $this->petExperienceService = $petExperienceService;
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->itemRepository = $itemRepository;
        $this->squirrel3 = $squirrel3;
        $this->houseSimService = $houseSimService;
        $this->petActivityLogTagRepository = $petActivityLogTagRepository;
    }

    public function createCrookedFishingRod(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getNature()->getTotal()));

        if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Crooked Fishing Rod.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Crooked Fishing Rod', $pet, $pet->getName() . ' created this from String and a Crooked Stick.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Crooked Fishing Rod, but couldn\'t figure it out.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
        }
    }

    public function createSunflowerStick(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getNature()->getTotal()));

        if($roll >= 10)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Sunflower', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Sunflower Stick.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Sunflower Stick', $pet, $pet->getName() . ' created this by affixing a Sunflower to the end of a Crooked Stick.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to make something out of a Sunflower, but couldn\'t come up with anything...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
        }
    }

    public function createTorchOrFlag(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        $making = $this->itemRepository->findOneByName($this->squirrel3->rngNextFromArray([
            'Stereotypical Torch',
            'White Flag'
        ]));

        if($roll >= 8)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('White Cloth', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created ' . $making->getNameWithArticle() . '.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem($making, $pet, $pet->getName() . ' created this from White Cloth and a Crooked Stick.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 45), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make ' . $making->getNameWithArticle() . ', but couldn\'t figure it out.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
        }
    }

    public function createHuntingSpear(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $pet->getSkills()->getBrawl()));

        if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $this->houseSimService->getState()->loseItem('Talon', 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Hunting Spear.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Hunting Spear', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Hunting Spear, but couldn\'t quite figure it out.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
        }
    }

    public function createRedFlail(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2 && $pet->getFood() < 4)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);

            $this->houseSimService->getState()->loseItem('Red', 1);
            $pet->increaseFood(3);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Red Flail, but ate the Red...', 'icons/activity-logs/broke-string')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
        }
        else if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $this->houseSimService->getState()->loseItem('Red', 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Red Flail.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Red Flail', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Red Flail, but couldn\'t quite figure it out.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
        }
    }

    public function createStrawBroom(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($this->squirrel3->rngNextInt(1, 100) === 1 && $this->houseSimService->hasInventory('Wheat', 1))
        {
            $this->houseSimService->getState()->loseItem('Wheat', 1);

            if($this->squirrel3->rngNextInt(1, 4) === 1)
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make a Straw Broom, but a weird-lookin\' elf, or something, ran in, turned the Wheat into a Gold Bar, and left! And guess what kind of broom you can\'t make out of a Gold Bar! A STRAW ONE!', '');
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make a Straw Broom, but a weird-lookin\' elf, or something, ran in, turned the Wheat into a Gold Bar, and left!', '');

            $activityLog
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting', 'Fae-kind' ]))
            ;

            $this->inventoryService->petCollectsItem('Gold Bar', $pet, $pet->getName() . ' was going to make a Straw Broom, but a weird-lookin\' elf, or something, turned the Wheat into this Gold Bar before ' . $pet->getName() . ' could even get started!', $activityLog);

            return $activityLog;
        }

        $craftsCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($craftsCheck >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Glue', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);

            $this->houseSimService->getState()->loseOneOf([ 'Rice', 'Wheat' ]);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Straw Broom.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Straw Broom', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a broom, but couldn\'t quite figure it out.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
        }
    }

    public function createBugCatchersNet(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $craftsCheck = $this->squirrel3->rngNextInt(1, 20 + max($petWithSkills->getCrafts()->getTotal(), $pet->getSkills()->getNature()) + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($craftsCheck >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Cobweb', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Bug-catcher\'s Net.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Bug-catcher\'s Net', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Bug-catcher\'s Net, but couldn\'t quite figure it out.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
        }
    }

    public function createHarvestStaff(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $craftsCheck = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($craftsCheck <= 2 && $pet->getFood() <= 4)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);

            $foodEaten = $this->houseSimService->getState()->loseOneOf([ 'Rice', 'Corn' ]);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseFood(4);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make a Harvest Staff, but got hungry, and ate the ' . $foodEaten . ' :(', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
        }
        else if($craftsCheck >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $this->houseSimService->getState()->loseItem('Rice', 1);
            $this->houseSimService->getState()->loseItem('Corn', 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Harvest Staff.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Harvest Staff', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a staff, but couldn\'t decide what kind...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
        }
    }

    public function createVeryLongSpear(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $pet->getSkills()->getBrawl()));

        if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $this->houseSimService->getState()->loseItem('Hunting Spear', 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created an Overly-long Spear.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Overly-long Spear', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to extend a Hunting Spear, but couldn\'t quite figure it out.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
        }
    }

    public function createRidiculouslyLongSpear(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $pet->getSkills()->getBrawl()));

        if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $this->houseSimService->getState()->loseItem('Overly-long Spear', 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a - like - CRAZY-long Spear. It\'s really rather silly.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('This is Getting Ridiculous', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% considered extending an Overly-long Spear, but then thought that maybe that was going a bit overboard.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
        }
    }

    public function createChampignon(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getUmbra()->getTotal()));

        if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Toadstool', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS,  PetSkillEnum::UMBRA ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Champignon.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting', 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Champignon', $pet, $pet->getName() . ' created this from a Crooked Stick, Toadstool, and bit of Quintessence.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS,  PetSkillEnum::UMBRA ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Champignon, but couldn\'t quite figure it out.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting', 'Magic-binding' ]))
            ;
        }
    }

    public function createWoodenSword(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $pet->getSkills()->getBrawl()));

        if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $itemLost = $this->houseSimService->getState()->loseOneOf([ 'String', 'Glue' ]);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Wooden Sword.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 12)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Wooden Sword', $pet, $pet->getName() . ' created this from some ' . $itemLost . ' and a Crooked Stick.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Wooden Sword, but couldn\'t quite figure it out.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
        }
    }

    public function createRusticMagnifyingGlass(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseSafety(-4);

            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make a lens from a piece of glass, but cut themselves! :(', 'icons/activity-logs/wounded')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
        }
        else if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $this->houseSimService->getState()->loseItem('Glass', 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a "Rustic" Magnifying Glass.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('"Rustic" Magnifying Glass', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a magnifying glass, but almost broke the glass, and gave up.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
        }
    }

    public function createSweetBeat(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::CRAFT, true);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $this->houseSimService->getState()->loseItem('Glue', 1);
            $this->houseSimService->getState()->loseItem('Sweet Beet', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Sweet Beat.', 'items/resource/string')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Sweet Beat', $pet, $pet->getName() . ' created this by gluing a Sweet Beet to a Stick. Because that makes sense.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to create a Sweet Beat, but wasn\'t able to make any meaningful progress.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
        }
    }

    public function createNanerPicker(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::CRAFT, true);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $this->houseSimService->getState()->loseItem('Small, Yellow Plastic Bucket', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Naner-picker.', 'items/tool/basket/fruit-picker')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Naner-picker', $pet, $pet->getName() . ' created this by mounting a bucket on the end of a stick.' . ($this->squirrel3->rngNextInt(1, 5) === 1 ? ' Few things could be simpler!' : ''), $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to think up a way to make a stick more useful, but wasn\'t able to come up with anything.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting' ]))
            ;
        }
    }
}
