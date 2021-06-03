<?php
namespace App\Service\PetActivity\Crafting;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Model\ActivityCallback;
use App\Model\ComputedPetSkills;
use App\Repository\ItemRepository;
use App\Service\CalendarService;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;

class PlasticPrinterService
{
    private $inventoryService;
    private $responseService;
    private $petExperienceService;
    private $itemRepository;
    private $calendarService;
    private IRandom $squirrel3;
    private HouseSimService $houseSimService;

    public function __construct(
        InventoryService $inventoryService, ResponseService $responseService, PetExperienceService $petExperienceService,
        ItemRepository $itemRepository, CalendarService $calendarService, Squirrel3 $squirrel3,
        HouseSimService $houseSimService
    )
    {
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->petExperienceService = $petExperienceService;
        $this->itemRepository = $itemRepository;
        $this->calendarService = $calendarService;
        $this->squirrel3 = $squirrel3;
        $this->houseSimService = $houseSimService;
    }

    /**
     * @return ActivityCallback[]
     */
    public function getCraftingPossibilities(ComputedPetSkills $petWithSkills): array
    {
        $possibilities = [];

        if($this->houseSimService->hasInventory('3D Printer') && $this->houseSimService->hasInventory('Plastic'))
        {
            $possibilities[] = new ActivityCallback($this, 'createPlasticCraft', 10);
            $possibilities[] = new ActivityCallback($this, 'createPlasticIdol', 4);

            if($this->houseSimService->hasInventory('Iron Bar'))
                $possibilities[] = new ActivityCallback($this, 'createCompass', 10);

            if($this->houseSimService->hasInventory('String'))
                $possibilities[] = new ActivityCallback($this, 'createPlasticFishingRod', 10);

            if($this->houseSimService->hasInventory('Green Dye') && $this->houseSimService->hasInventory('Yellow Dye'))
                $possibilities[] = new ActivityCallback($this, 'createAlienLaser', 10);

            if($this->houseSimService->hasInventory('Black Feathers'))
                $possibilities[] = new ActivityCallback($this, 'createEvilFeatherDuster', 10);

            if($this->houseSimService->hasInventory('Plastic Boomerang', 2))
                $possibilities[] = new ActivityCallback($this, 'createNonsenserang', 10);
        }

        return $possibilities;
    }

    private function printerActingUp(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ]);
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PLASTIC_PRINT, false);
        return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to print something out of Plastic, but the 3D Printer kept acting up.', 'icons/activity-logs/confused');
    }

    public function createPlasticFishingRod(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getNature()->getTotal()));

        if($roll <= 3)
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PLASTIC_PRINT, false);

            if($this->squirrel3->rngNextInt(1, 2) === 1)
            {
                $this->houseSimService->getState()->loseItem('String', 1);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Plastic Fishing Rod, but broke the String :(', 'icons/activity-logs/broke-string');
            }
            else
            {
                $this->houseSimService->getState()->loseItem('Plastic', 1);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Plastic Fishing Rod, but the base plate of the 3D Printer moved, jacking up the Plastic :(', '');
            }
        }
        else if($roll >= 12)
        {
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Plastic', 1);

            $pet->increaseEsteem(2);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PLASTIC_PRINT, true);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Plastic Fishing Rod.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 12)
            ;
            $this->inventoryService->petCollectsItem('Plastic Fishing Rod', $pet, $pet->getName() . ' created this from String and Plastic.', $activityLog);

            return $activityLog;
        }
        else
        {
            return $this->printerActingUp($pet);
        }
    }

    public function createNonsenserang(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 3)
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PLASTIC_PRINT, false);

            if($this->squirrel3->rngNextInt(1, 2) === 1)
            {
                $this->houseSimService->getState()->loseItem('Plastic', 1);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make another boomerang blade for a Nonsenserang, but the base plate of the 3D Printer moved, jacking up the Plastic :(', '');
            }
            else
            {
                $this->houseSimService->getState()->loseItem('Plastic Boomerang', 1);
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to weld two Plastic Boomerangs together, but warped one of them beyond repair :( Well, at least it\'s still useful as a chunk of Plastic...', '');
                $this->inventoryService->petCollectsItem('Plastic', $pet, $pet->getName() . ' accidentally melted a Plastic Boomerang. This is all that remains.', $activityLog);
                return $activityLog;
            }
        }
        else if($roll >= 14)
        {
            $this->houseSimService->getState()->loseItem('Plastic Boomerang', 2);
            $this->houseSimService->getState()->loseItem('Plastic', 1);

            $pet->increaseEsteem(2);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PLASTIC_PRINT, true);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Nonsenserang!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
            ;
            $this->inventoryService->petCollectsItem('Nonsenserang', $pet, $pet->getName() . ' fused two Plastic Boomerangs together, and printed up an extra set of blades, producing this ridiculous implement.', $activityLog);

            return $activityLog;
        }
        else
        {
            return $this->printerActingUp($pet);
        }
    }

    public function createEvilFeatherDuster(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 3)
        {
            $this->houseSimService->getState()->loseItem('Plastic', 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PLASTIC_PRINT, false);

            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make an Evil Feather Duster, but the base plate of the 3D Printer moved, jacking up the Plastic :(', '');
        }
        else if($roll >= 16)
        {
            $this->houseSimService->getState()->loseItem('Black Feathers', 1);
            $this->houseSimService->getState()->loseItem('Plastic', 1);

            $getExtraStuff = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal())
                >= 21
            ;

            $pet->increaseEsteem(3);
            $this->petExperienceService->gainExp($pet, $getExtraStuff ? 3 : 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PLASTIC_PRINT, true);

            if($getExtraStuff)
            {
                $extraLoot = $this->itemRepository->findOneByName($this->squirrel3->rngNextFromArray([
                    'Fluff', 'Feathers', 'Dark Matter', 'Aging Powder', 'Baking Powder', 'Spider', 'Moon Dust',
                ]));

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created an Evil Feather Duster. While they were testing it out, they found ' . $extraLoot->getNameWithArticle() . '!', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 21)
                ;

                $this->inventoryService->petCollectsItem($extraLoot, $pet, $pet->getName() . ' found this while trying out the Evil Feather Duster they just made.', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created an Evil Feather Duster.', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                ;
            }

            $this->inventoryService->petCollectsItem('Evil Feather Duster', $pet, $pet->getName() . ' created this from Black Feathers and Plastic.', $activityLog);

            return $activityLog;
        }
        else
        {
            return $this->printerActingUp($pet);
        }
    }

    public function createCompass(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 3)
        {
            $this->houseSimService->getState()->loseItem('Plastic', 1);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PLASTIC_PRINT, false);

            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Compass, but the base plate of the 3D Printer moved, jacking up the Plastic :(', '');
        }
        else if($roll >= 12)
        {
            $this->houseSimService->getState()->loseItem('Iron Bar', 1);
            $this->houseSimService->getState()->loseItem('Plastic', 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PLASTIC_PRINT, true);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Compass.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 12)
            ;
            $this->inventoryService->petCollectsItem('Compass', $pet, $pet->getName() . ' created this from Plastic and an Iron Bar.', $activityLog);

            return $activityLog;
        }
        else
        {
            return $this->printerActingUp($pet);
        }
    }

    public function createAlienLaser(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 4)
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PLASTIC_PRINT, false);

            $lostItem = $this->squirrel3->rngNextFromArray([ 'Plastic', 'Yellow Dye', 'Green Dye' ]);

            $this->houseSimService->getState()->loseItem($lostItem, 1);

            if($lostItem === 'Plastic')
            {
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to print a Toy Alien Gun, but the base plate of the 3D Printer moved, jacking up the Plastic :(', '');
            }
            else
            {
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to print a Toy Alien Gun, but accidentally spilt the ' . $lostItem . ' :(', '');
            }
        }
        else if($roll >= 14)
        {
            $this->houseSimService->getState()->loseItem('Green Dye', 1);
            $this->houseSimService->getState()->loseItem('Yellow Dye', 1);
            $this->houseSimService->getState()->loseItem('Plastic', 1);
            $pet->increaseEsteem(2);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PLASTIC_PRINT, true);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% printed a Toy Alien Gun.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
            ;
            $this->inventoryService->petCollectsItem('Toy Alien Gun', $pet, $pet->getName() . ' printed this.', $activityLog);
            return $activityLog;
        }
        else
        {
            return $this->printerActingUp($pet);
        }
    }

    public function createPlasticCraft(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $allPlasticItems = [
            'Small Plastic Bucket',
            'Plastic Shovel',
            'Egg Carton',
            'Ruler',
            'Plastic Boomerang',
        ];

        $beingHalloweeny = false;

        if($this->calendarService->isHalloweenCrafting())
        {
            if($this->squirrel3->rngNextInt(1, 2) === 1)
            {
                $item = $this->itemRepository->findOneByName('Small Plastic Bucket');
                $beingHalloweeny = true;
            }
            else
            {
                $allPlasticItemsExceptBucket = array_filter($allPlasticItems, function($item) {
                    return $item !== 'Small Plastic Bucket';
                });

                $item = $this->itemRepository->findOneByName($this->squirrel3->rngNextFromArray($allPlasticItemsExceptBucket));
            }
        }
        else
        {
            $item = $this->itemRepository->findOneByName($this->squirrel3->rngNextFromArray($allPlasticItems));
        }

        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 3)
        {
            $this->houseSimService->getState()->loseItem('Plastic', 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PLASTIC_PRINT, false);

            if($beingHalloweeny)
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wants to make a Halloween-themed bucket, so tried to make ' . $item->getNameWithArticle() . ' as a base, but the base plate of the 3D Printer moved, jacking up the Plastic :(', '');
            else
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make ' . $item->getNameWithArticle() . ', but the base plate of the 3D Printer moved, jacking up the Plastic :(', '');
        }
        else if($roll >= 10)
        {
            $this->houseSimService->getState()->loseItem('Plastic', 1);

            $pet->increaseEsteem(2);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PLASTIC_PRINT, true);

            if($beingHalloweeny)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wants to make a Halloween-themed bucket, and created ' . $item->getNameWithArticle() . ' as a base.', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
                ;
            }
            else
            {
                if($roll >= 30 && $pet->hasMerit(MeritEnum::BEHATTED))
                {
                    $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);

                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created ' . $item->getNameWithArticle() . '... and a pair of Googly Eyes with bits of leftover Plastic!', '')
                        ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 30)
                    ;

                    $this->inventoryService->petCollectsItem('Googly Eyes', $pet, $pet->getName() . ' created this from Plastic.', $activityLog);
                }
                else
                {
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created ' . $item->getNameWithArticle() . '.', '')
                        ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
                    ;
                }
            }

            $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' created this from Plastic.', $activityLog);
            return $activityLog;
        }
        else
        {
            return $this->printerActingUp($pet);
        }
    }

    public function createPlasticIdol(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 3)
        {
            $this->houseSimService->getState()->loseItem('Plastic', 1);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PLASTIC_PRINT, false);

            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Plastic Idol, but the base plate of the 3D Printer moved, jacking up the Plastic :(', '');
        }
        else if($roll >= 13)
        {
            $this->houseSimService->getState()->loseItem('Plastic', 1);

            $pet->increaseEsteem(2);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PLASTIC_PRINT, true);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Plastic Idol.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
            ;
            $this->inventoryService->petCollectsItem('Plastic Idol', $pet, $pet->getName() . ' created this from Plastic.', $activityLog);

            return $activityLog;
        }
        else
        {
            return $this->printerActingUp($pet);
        }
    }
}
