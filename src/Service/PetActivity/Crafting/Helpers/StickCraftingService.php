<?php
declare(strict_types=1);

namespace App\Service\PetActivity\Crafting\Helpers;

use App\Entity\PetActivityLog;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityLogTagEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Model\ComputedPetSkills;
use App\Service\Clock;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;

class StickCraftingService
{
    public function __construct(
        private readonly PetExperienceService $petExperienceService,
        private readonly InventoryService $inventoryService,
        private readonly ResponseService $responseService,
        private readonly IRandom $rng,
        private readonly HouseSimService $houseSimService,
        private readonly EntityManagerInterface $em,
        private readonly Clock $clock
    )
    {
    }

    public function createCrookedFishingRod(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getNature()->getTotal()));

        if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Crooked Fishing Rod.', '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $exp = $this->maybeSpotAStickBug($petWithSkills, $activityLog) ? 3 : 2;

            $this->inventoryService->petCollectsItem('Crooked Fishing Rod', $pet, $pet->getName() . ' created this from String and a Crooked Stick.', $activityLog);
            $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Crooked Fishing Rod, but couldn\'t figure it out.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createSkeweredMarshmallow(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getNature()->getTotal()));

        if($roll >= 10)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Marshmallows', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% skewered a Marshmallow!')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $exp = $this->maybeSpotAStickBug($petWithSkills, $activityLog) ? 3 : 2;

            $this->inventoryService->petCollectsItem('Skewered Marshmallow', $pet, $pet->getName() . ' "crafted" this.', $activityLog);
            $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% was going to skewer a Marshmallow, but then almost ate it, instead, but then stopped themselves, and then just abandoned the whole project...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createSunflowerStick(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getNature()->getTotal()));

        if($roll >= 10)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Sunflower', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Sunflower Stick.', '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $exp = $this->maybeSpotAStickBug($petWithSkills, $activityLog) ? 3 : 2;

            $this->inventoryService->petCollectsItem('Sunflower Stick', $pet, $pet->getName() . ' created this by affixing a Sunflower to the end of a Crooked Stick.', $activityLog);
            $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to make something out of a Sunflower, but couldn\'t come up with anything...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createTorchOrFlag(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        $making = ItemRepository::findOneByName($this->em, $this->rng->rngNextFromArray([
            'Stereotypical Torch',
            'White Flag'
        ]));

        if($roll >= 8)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('White Cloth', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created ' . $making->getNameWithArticle() . '.', '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $exp = $this->maybeSpotAStickBug($petWithSkills, $activityLog) ? 2 : 1;

            $this->inventoryService->petCollectsItem($making, $pet, $pet->getName() . ' created this from White Cloth and a Crooked Stick.', $activityLog);

            $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make ' . $making->getNameWithArticle() . ', but couldn\'t figure it out.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(15, 45), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createHuntingSpear(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $pet->getSkills()->getBrawl()));

        if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $this->houseSimService->getState()->loseItem('Talon', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Hunting Spear.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $exp = $this->maybeSpotAStickBug($petWithSkills, $activityLog) ? 3 : 2;

            $this->inventoryService->petCollectsItem('Hunting Spear', $pet, $pet->getName() . ' created this.', $activityLog);
            $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Hunting Spear, but couldn\'t quite figure it out.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createRedFlail(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2 && $pet->getFood() < 4)
        {
            $this->houseSimService->getState()->loseItem('Red', 1);
            $pet->increaseFood(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Red Flail, but ate the Red...', 'icons/activity-logs/broke-string')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home, 'Eating' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $this->houseSimService->getState()->loseItem('Red', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Red Flail.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $exp = $this->maybeSpotAStickBug($petWithSkills, $activityLog) ? 3 : 2;

            $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->inventoryService->petCollectsItem('Red Flail', $pet, $pet->getName() . ' created this.', $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Red Flail, but couldn\'t quite figure it out.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createStrawBroom(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($this->rng->rngNextInt(1, 100) === 1 && $this->houseSimService->hasInventory('Wheat', 1))
        {
            $this->houseSimService->getState()->loseItem('Wheat', 1);

            if($this->rng->rngNextInt(1, 4) === 1)
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make a Straw Broom, but a weird-lookin\' elf, or something, ran in, turned the Wheat into a Gold Bar, and left! And guess what kind of broom you can\'t make out of a Gold Bar! A STRAW ONE!', '');
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make a Straw Broom, but a weird-lookin\' elf, or something, ran in, turned the Wheat into a Gold Bar, and left!', '');

            $activityLog
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home, 'Fae-kind' ]))
            ;

            $this->inventoryService->petCollectsItem('Gold Bar', $pet, $pet->getName() . ' was going to make a Straw Broom, but a weird-lookin\' elf, or something, turned the Wheat into this Gold Bar before ' . $pet->getName() . ' could even get started!', $activityLog);

            return $activityLog;
        }

        $craftsCheck = $this->rng->rngNextInt(1, 20 + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($craftsCheck >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Glue', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);

            $this->houseSimService->getState()->loseOneOf($this->rng, [ 'Rice', 'Wheat' ]);

            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Straw Broom.', '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $exp = $this->maybeSpotAStickBug($petWithSkills, $activityLog) ? 3 : 2;

            $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->inventoryService->petCollectsItem('Straw Broom', $pet, $pet->getName() . ' created this.', $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a broom, but couldn\'t quite figure it out.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createBugCatchersNet(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $craftsCheck = $this->rng->rngNextInt(1, 20 + max($petWithSkills->getCrafts()->getTotal(), $pet->getSkills()->getNature()) + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($craftsCheck >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Cobweb', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);

            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Bug-catcher\'s Net.', '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $exp = $this->maybeSpotAStickBug($petWithSkills, $activityLog) ? 3 : 2;

            $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->inventoryService->petCollectsItem('Bug-catcher\'s Net', $pet, $pet->getName() . ' created this.', $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Bug-catcher\'s Net, but couldn\'t quite figure it out.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createHarvestStaff(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $craftsCheck = $this->rng->rngNextInt(1, 20 + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($craftsCheck <= 2 && $pet->getFood() <= 4)
        {
            $foodEaten = $this->houseSimService->getState()->loseOneOf($this->rng, [ 'Rice', 'Corn' ]);
            $pet->increaseFood(4);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make a Harvest Staff, but got hungry, and ate the ' . $foodEaten . ' :(', '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home, 'Eating' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }
        else if($craftsCheck >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $this->houseSimService->getState()->loseItem('Rice', 1);
            $this->houseSimService->getState()->loseItem('Corn', 1);

            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Harvest Staff.', '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $exp = $this->maybeSpotAStickBug($petWithSkills, $activityLog) ? 3 : 2;

            $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->inventoryService->petCollectsItem('Harvest Staff', $pet, $pet->getName() . ' created this.', $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a staff, but couldn\'t decide what kind...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createVeryLongSpear(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $pet->getSkills()->getBrawl()));

        if($roll >= 14)
        {
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $this->houseSimService->getState()->loseItem('Hunting Spear', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created an Overly-long Spear.', '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $exp = $this->maybeSpotAStickBug($petWithSkills, $activityLog) ? 3 : 2;

            $this->inventoryService->petCollectsItem('Overly-long Spear', $pet, $pet->getName() . ' created this.', $activityLog);
            $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to extend a Hunting Spear, but couldn\'t quite figure it out.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createRidiculouslyLongSpear(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $pet->getSkills()->getBrawl()));

        if($roll >= 16)
        {
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $this->houseSimService->getState()->loseItem('Overly-long Spear', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a - like - CRAZY-long Spear. It\'s really rather silly.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $exp = $this->maybeSpotAStickBug($petWithSkills, $activityLog) ? 3 : 2;

            $this->inventoryService->petCollectsItem('This is Getting Ridiculous', $pet, $pet->getName() . ' created this.', $activityLog);
            $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% considered extending an Overly-long Spear, but then thought that maybe that was going a bit overboard.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createChampignon(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getArcana()->getTotal()));

        if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Quintessence', 1);
            $this->houseSimService->getState()->loseItem('Toadstool', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Champignon.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                    PetActivityLogTagEnum::Magic_binding,
                ]))
            ;

            $exp = $this->maybeSpotAStickBug($petWithSkills, $activityLog) ? 3 : 2;

            $this->inventoryService->petCollectsItem('Champignon', $pet, $pet->getName() . ' created this from a Crooked Stick, Toadstool, and bit of Quintessence.', $activityLog);
            $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::CRAFTS,  PetSkillEnum::ARCANA ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Champignon, but couldn\'t quite figure it out.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home, 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::ARCANA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createWoodenSword(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $pet->getSkills()->getBrawl()));

        if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $itemLost = $this->houseSimService->getState()->loseOneOf($this->rng, [ 'String', 'Glue' ]);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Wooden Sword.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 12)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $exp = 1;

            if($this->maybeSpotAStickBug($petWithSkills, $activityLog))
                $exp++;
            else if($itemLost === 'String' && ($this->clock->getMonthAndDay() >= 1101 || $this->clock->getMonthAndDay() < 200) && $roll >= 25)
            {
                $exp += 3;

                $activityLog
                    ->setEntry($activityLog->getEntry() . '.. and had some leftover wood and string, so also threw together a pair of Knitting Needles!')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 22)
                ;

                $this->inventoryService->petCollectsItem('Knitting Needles', $pet, $pet->getName() . ' created this with the leftovers from making a Wooden Sword.', $activityLog);
            }

            $this->inventoryService->petCollectsItem('Wooden Sword', $pet, $pet->getName() . ' created this from some ' . $itemLost . ' and a Crooked Stick.', $activityLog);
            $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Wooden Sword, but couldn\'t quite figure it out.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createRusticMagnifyingGlass(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 2)
        {
            $pet->increaseSafety(-4);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make a lens from a piece of glass, but cut themselves! :(', 'icons/activity-logs/wounded')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }
        else if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $this->houseSimService->getState()->loseItem('Glass', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a "Rustic" Magnifying Glass.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $exp = $this->maybeSpotAStickBug($petWithSkills, $activityLog) ? 3 : 2;

            $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->inventoryService->petCollectsItem('"Rustic" Magnifying Glass', $pet, $pet->getName() . ' created this.', $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a magnifying glass, but almost broke the glass, and gave up.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createSweetBeat(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Glue', 1);
            $this->houseSimService->getState()->loseItem('Sweet Beet', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Sweet Beat.', 'items/resource/string')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $exp = $this->maybeSpotAStickBug($petWithSkills, $activityLog) ? 3 : 2;

            $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->inventoryService->petCollectsItem('Sweet Beat', $pet, $pet->getName() . ' created this by gluing a Sweet Beet to a Stick. Because that makes sense.', $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to create a Sweet Beat, but wasn\'t able to make any meaningful progress.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createNanerPicker(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Small, Yellow Plastic Bucket', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Naner-picker.', 'items/tool/basket/fruit-picker')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Crafting,
                    PetActivityLogTagEnum::Location_At_Home,
                ]))
            ;

            $exp = $this->maybeSpotAStickBug($petWithSkills, $activityLog) ? 2 : 1;

            $this->inventoryService->petCollectsItem('Naner-picker', $pet, $pet->getName() . ' created this by mounting a bucket on the end of a stick.' . ($this->rng->rngNextInt(1, 5) === 1 ? ' Few things could be simpler!' : ''), $activityLog);
            $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to think up a way to make a stick more useful, but wasn\'t able to come up with anything.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }
        return $activityLog;
    }

    private function maybeSpotAStickBug(ComputedPetSkills $petWithSkills, PetActivityLog $activityLog): bool
    {
        $pet = $petWithSkills->getPet();

        $repelsBugs =
            $pet->getTool() && (
                ($pet->getTool()->getItem()->getTool() && $pet->getTool()->getItem()->getTool()->getPreventsBugs()) ||
                ($pet->getTool()->getEnchantment() && $pet->getTool()->getEnchantment()->getEffects()->getPreventsBugs())
            )
        ;

        if($repelsBugs)
            return false;

        $spottedIt = $this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() * 2) >= 20;

        if(!$spottedIt)
            return false;

        $extraLoot = $this->rng->rngNextFromArray([
            'Worker Bee', 'Line of Ants', 'Stick Insect'
        ]);

        $this->inventoryService->petCollectsItem($extraLoot, $pet, $pet->getName() . ' found this on a stick!', $activityLog);

        $attractsBugs =
            $pet->getTool() && (
                ($pet->getTool()->getItem()->getTool() && $pet->getTool()->getItem()->getTool()->getAttractsBugs()) ||
                ($pet->getTool()->getEnchantment() && $pet->getTool()->getEnchantment()->getEffects()->getAttractsBugs())
            )
        ;

        if($attractsBugs)
        {
            $this->inventoryService->petCollectsItem($extraLoot, $pet, $pet->getName() . ' found this on a stick!', $activityLog);

            $activityLog->setEntry($activityLog->getEntry() . ' While they were doing that, they spotted a ' . $extraLoot . '! ... wait, make that TWO! They grabbed both before they could get away!')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
            ;
        }
        else
        {
            $activityLog->setEntry($activityLog->getEntry() . ' While they were doing that, they spotted a ' . $extraLoot . '! They grabbed it before it could get away!')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
            ;
        }

        return true;
    }
}
