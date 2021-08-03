<?php
namespace App\Service\PetActivity\Crafting\Helpers;

use App\Entity\PetActivityLog;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Model\ComputedPetSkills;
use App\Repository\ItemRepository;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;

class IronSmithingService
{
    private $petExperienceService;
    private $inventoryService;
    private $responseService;
    private $itemRepository;
    private IRandom $squirrel3;
    private HouseSimService $houseSimService;

    public function __construct(
        PetExperienceService $petExperienceService, InventoryService $inventoryService, ResponseService $responseService,
        ItemRepository $itemRepository, Squirrel3 $squirrel3, HouseSimService $houseSimService
    )
    {
        $this->petExperienceService = $petExperienceService;
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->itemRepository = $itemRepository;
        $this->squirrel3 = $squirrel3;
        $this->houseSimService = $houseSimService;
    }

    public function createIronKey(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
            $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 24));
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to forge an Iron Key, but got burned while trying! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Iron Bar', 1);

            $keys = $this->squirrel3->rngNextInt(1, 5) === 1 ? 2 : 1;

            if($keys === 2)
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% forged *two* Iron Keys from an Iron Bar!', 'items/key/iron');
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% forged an Iron Key from an Iron Bar.', 'items/key/iron');

            $this->inventoryService->petCollectsItem('Iron Key', $pet, $pet->getName() . ' forged this from an Iron Bar.', $activityLog);

            if($keys === 2)
                $this->inventoryService->petCollectsItem('Iron Key', $pet, $pet->getName() . ' forged this from an Iron Bar.', $activityLog);

            $this->petExperienceService->gainExp($pet, $keys, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem($keys === 1 ? 1 : 4);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to forge an Iron Key from an Iron Bar, but couldn\'t get the shape right.', 'icons/activity-logs/confused');
        }
    }

    public function createBasicIronCraft(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $making = $this->squirrel3->rngNextFromArray([
            [ 'item' => 'Iron Tongs', 'description' => 'Iron Tongs', 'image' => 'items/tool/tongs', 'difficulty' => 13, 'experience' => 1 ],
            [ 'item' => 'Iron Sword', 'description' => 'an Iron Sword', 'image' => 'items/tool/sword/iron', 'difficulty' => 14, 'experience' => 1 ],
            [ 'item' => 'Flute', 'description' => 'a Flute', 'image' => 'items/tool/instrument/flute', 'difficulty' => 15, 'experience' => 2 ],
            [ 'item' => 'Dumbbell', 'description' => 'a Dumbbell', 'image' => 'items/tool/dumbbell', 'difficulty' => 12, 'experience' => 1 ],
        ]);

        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
            $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 24));
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to forge ' . $making['description'] . ', but got burned while trying! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= $making['difficulty'])
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Iron Bar', 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% forged ' . $making['description'] . ' from an Iron Bar.', $making['image']);

            $this->inventoryService->petCollectsItem($making['item'], $pet, $pet->getName() . ' forged this from an Iron Bar.', $activityLog);

            $this->petExperienceService->gainExp($pet, $making['experience'], [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to forge ' . $making['description'] . ' from an Iron Bar, but couldn\'t get the shape right.', 'icons/activity-logs/confused');
        }
    }

    public function createWaterStrider(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Iron Bar', 1);
            $this->houseSimService->getState()->loseItem('Green Dye', 1);
            $this->houseSimService->getState()->loseItem('Bug-catcher\'s Net', 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% turned a simple Bug-catcher\'s Net into a Water Strider.', 'items/tool/net/water-strider')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
            ;

            $this->inventoryService->petCollectsItem('Water Strider', $pet, $pet->getName() . ' created this from a simple Bug-catcher\'s Net.', $activityLog);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            if($this->squirrel3->rngNextInt(1, 4) === 1)
            {
                $pet->increaseSafety(-$this->squirrel3->rngNextInt(1, 2));
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to sharpen an Iron Bar to top off a Bug-catcher\'s Net with, but nearly cut themselves in the process!', 'icons/activity-logs/confused');
            }
            else
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to spiff up a Bug-catcher\'s Net, but wasn\'t happy with any of their ideas...', 'icons/activity-logs/confused');
        }
    }

    public function createYellowScissors(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Iron Bar', 1);
            $this->houseSimService->getState()->loseItem('Plastic', 1);
            $this->houseSimService->getState()->loseItem('Yellow Dye', 1);

            $scienceRoll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

            if($scienceRoll >= 20)
            {
                $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ]);
                $pet->increaseEsteem(3);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made Yellow Scissors, and science\'d up a mechanical can-opener with the leftover materials.', 'items/tool/scissors/yellow-can-opener')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                ;

                $this->inventoryService->petCollectsItem('Yellow Can-opener', $pet, $pet->getName() . ' created this.', $activityLog);
            }
            else
            {
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseEsteem(1);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made Yellow Scissors.', 'items/tool/scissors/yellow')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
                ;
            }

            if($this->squirrel3->rngNextInt(1, 20 + ($pet->getId() % 4) * 3) >= 23)
                $this->inventoryService->petCollectsItem('Yellow Scissors', $pet, $pet->getName() . ' created (and sharpened) this!', $activityLog);
            else
                $this->inventoryService->petCollectsItem('Yellow Scissors', $pet, $pet->getName() . ' created this.', $activityLog);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make scissors, but getting the handle shape right is apparently frickin\' impossible >:(', 'icons/activity-logs/confused');
        }
    }

    public function createGreenScissors(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Iron Bar', 1);
            $this->houseSimService->getState()->loseItem('Plastic', 1);
            $this->houseSimService->getState()->loseItem('Green Dye', 1);

            $scienceRoll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

            if($scienceRoll >= 20)
            {
                $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ]);
                $pet->increaseEsteem(3);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made Green Scissors, and science\'d up a mechanical can-opener with the leftover materials.', 'items/tool/scissors/yellow-can-opener')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                ;

                $this->inventoryService->petCollectsItem('Green Can-opener', $pet, $pet->getName() . ' created this.', $activityLog);
            }
            else
            {
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseEsteem(1);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made Green Scissors.', 'items/tool/scissors/yellow')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
                ;
            }

            if($this->squirrel3->rngNextInt(1, 20 + ($pet->getId() % 4) * 3) >= 23)
                $this->inventoryService->petCollectsItem('Green Scissors', $pet, $pet->getName() . ' created (and sharpened) this!', $activityLog);
            else
                $this->inventoryService->petCollectsItem('Green Scissors', $pet, $pet->getName() . ' created this.', $activityLog);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make scissors, but getting the handle shape right is apparently frickin\' impossible >:(', 'icons/activity-logs/confused');
        }
    }

    public function createSaucepan(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 10)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::SMITH, true);

            $this->houseSimService->getState()->loseItem('Iron Bar', 1);
            $this->houseSimService->getState()->loseItem('Plastic', 1);

            if($roll >= 20)
            {
                $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseEsteem(2);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Saucepan... and a Whisk!', 'items/tool/saucepan')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                ;
                $this->inventoryService->petCollectsItem('Whisk', $pet, $pet->getName() . ' created this.', $activityLog);
            }
            else
            {
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseEsteem(1);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Saucepan.', 'items/tool/saucepan')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
                ;
            }

            $this->inventoryService->petCollectsItem('Saucepan', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Saucepan, but couldn\'t figure out the purpose of the thing...', 'icons/activity-logs/confused');
        }
    }

    public function createScythe(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        $item = $this->itemRepository->findOneByName($this->squirrel3->rngNextFromArray([
            'Scythe',
            'Garden Shovel'
        ]));

        if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $this->houseSimService->getState()->loseItem('Iron Bar', 1);

            if($roll >= 33)
            {
                $this->petExperienceService->gainExp($pet, 5, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseEsteem($this->squirrel3->rngNextInt(5, 8));

                $bonusItems = $this->squirrel3->rngNextSubsetFromArray([ 'Trowel', 'Hand Rake', 'Bezeling Planisher' ], 2);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made ' . $item->getNameWithArticle() . ' from a Crooked Stick, and Iron Bar... with enough left over to make a ' . $bonusItems[0] . ' _and_ ' . $bonusItems[1] . ', as well! (Dang! Such skills!)', 'items/' . $item->getImage())
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 33)
                ;
                $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' created this from a Crooked Stick, and Iron Bar.', $activityLog);

                foreach($bonusItems as $bonusItem)
                    $this->inventoryService->petCollectsItem($bonusItem, $pet, $pet->getName() . ' created this with the leftovers from making ' . $item->getNameWithArticle() . '.', $activityLog);
            }
            else if($roll >= 23)
            {
                $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseEsteem($this->squirrel3->rngNextInt(3, 6));

                $bonusItem = $this->squirrel3->rngNextFromArray([ 'Trowel', 'Hand Rake', 'Bezeling Planisher' ]);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made ' . $item->getNameWithArticle() . ' from a Crooked Stick, and Iron Bar... with enough left over to make a ' .  $bonusItem .', as well!', 'items/' . $item->getImage())
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 23)
                ;
                $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' created this from a Crooked Stick, and Iron Bar.', $activityLog);

                $this->inventoryService->petCollectsItem($bonusItem, $pet, $pet->getName() . ' created this with the leftovers from making ' . $item->getNameWithArticle() . '.', $activityLog);
            }
            else
            {
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                $pet->increaseEsteem(1);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made ' . $item->getNameWithArticle() . ' from a Crooked Stick, and Iron Bar.', 'items/' . $item->getImage())
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
                ;
                $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' created this from a Crooked Stick, and Iron Bar.', $activityLog);
            }

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make ' . $item->getNameWithArticle() . ', but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createGrapplingHook(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Iron Bar', 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Grappling Hook from Iron Bar, and String.', 'items/tool/grappling-hook')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem('Grappling Hook', $pet, $pet->getName() . ' created this from Iron Bar, and String.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Grappling Hook, but couldn\'t figure it out.', 'icons/activity-logs/confused');
        }
    }

    public function createHeavyTool(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $makes = $this->itemRepository->findOneByName($this->squirrel3->rngNextFromArray([
            'Heavy Hammer',
            'Heavy Lance'
        ]));

        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + min($petWithSkills->getStrength()->getTotal(), $petWithSkills->getStamina()->getTotal()) + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($petWithSkills->getStrength()->getTotal() < 3)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::SMITH, false);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to make ' . $makes->getNameWithArticle() . ', but they aren\'t strong enough... (The Dark Matter is WAY too heavy!)', '');
        }
        else if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            $pet
                ->increaseSafety(-$this->squirrel3->rngNextInt(4, 8))
                ->increaseEsteem(-$this->squirrel3->rngNextInt(1, 2))
            ;

            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make ' . $makes->getNameWithArticle() . ', but dropped an Iron Bar on their toes!', '');
        }
        else if($roll <= 17)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make ' . $makes->getNameWithArticle() . ', but the Dark Matter was being especially difficult to work with! >:(', '');
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Dark Matter', 1);
            $this->houseSimService->getState()->loseItem('Iron Bar', 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(3);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made ' . $makes->getNameWithArticle() . ' from an Iron Bar and some Dark Matter!', 'items/tool/hammer/heavy')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
            ;
            $this->inventoryService->petCollectsItem($makes, $pet, $pet->getName() . ' created this from an Iron Bar and some Dark Matter!', $activityLog);
            return $activityLog;
        }
    }

    public function createMirrorShield(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
            $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 24));
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make an iron shield, but got burned while trying! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Mirror', 1);
            $this->houseSimService->getState()->loseItem('Iron Bar', 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Mirror Shield!', 'items/tool/shield/mirror')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem('Mirror Shield', $pet, $pet->getName() . ' created this from Iron Bar, and a Mirror.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to make an iron shield, but couldn\'t come up with a good design...', 'icons/activity-logs/confused');
        }
    }

    public function createMushketeer(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Toadstool', 1);
            $this->houseSimService->getState()->loseItem('Iron Bar', 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Mushketeer!', 'items/tool/sword/mushroom')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
            ;
            $this->inventoryService->petCollectsItem('Mushketeer', $pet, $pet->getName() . ' created this from Iron Bar, and a Toadstool.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to make a sword using a Toadstool, but couldn\'t figure it out...', 'icons/activity-logs/confused');
        }
    }

}
