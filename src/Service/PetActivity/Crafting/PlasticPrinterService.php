<?php
namespace App\Service\PetActivity\Crafting;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Model\ActivityCallback;
use App\Model\ComputedPetSkills;
use App\Repository\ItemRepository;
use App\Service\CalendarService;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;

class PlasticPrinterService
{
    private $inventoryService;
    private $responseService;
    private $petExperienceService;
    private $itemRepository;
    private $calendarService;

    public function __construct(
        InventoryService $inventoryService, ResponseService $responseService, PetExperienceService $petExperienceService,
        ItemRepository $itemRepository, CalendarService $calendarService
    )
    {
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->petExperienceService = $petExperienceService;
        $this->itemRepository = $itemRepository;
        $this->calendarService = $calendarService;
    }

    /**
     * @return ActivityCallback[]
     */
    public function getCraftingPossibilities(ComputedPetSkills $petWithSkills, array $quantities): array
    {
        $possibilities = [];

        if(array_key_exists('3D Printer', $quantities) && array_key_exists('Plastic', $quantities))
        {
            $possibilities[] = new ActivityCallback($this, 'createPlasticCraft', 10);
            $possibilities[] = new ActivityCallback($this, 'createPlasticIdol', 4);

            if(array_key_exists('Iron Bar', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createCompass', 10);

            if(array_key_exists('String', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createPlasticFishingRod', 10);

            if(array_key_exists('Green Dye', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createAlienLaser', 10);

            if(array_key_exists('Black Feathers', $quantities))
                $possibilities[] = new ActivityCallback($this, 'createEvilFeatherDuster', 10);

            if(array_key_exists('Plastic Boomerang', $quantities) && $quantities['Plastic Boomerang']->quantity >= 2)
                $possibilities[] = new ActivityCallback($this, 'createNonsenserang', 10);
        }

        return $possibilities;
    }

    private function printerActingUp(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::PLASTIC_PRINT, false);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ]);
        return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to print something out of Plastic, but the 3D Printer kept acting up.', 'icons/activity-logs/confused');
    }

    public function createPlasticFishingRod(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getNature()->getTotal()));

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::PLASTIC_PRINT, false);
            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Plastic Fishing Rod, but broke the String :(', 'icons/activity-logs/broke-string');
            }
            else
            {
                $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Plastic Fishing Rod, but the base plate of the 3D Printer moved, jacking up the Plastic :(', '');

            }
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PLASTIC_PRINT, true);
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
            $pet->increaseEsteem(2);
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
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::PLASTIC_PRINT, false);

            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make another boomerang blade for a Nonsenserang, but the base plate of the 3D Printer moved, jacking up the Plastic :(', '');
            }
            else
            {
                $this->inventoryService->loseItem('Plastic Boomerang', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to weld two Plastic Boomerangs together, but warped one of them beyond repair :( Well, at least it\'s still useful as a chunk of Plastic...', '');
                $this->inventoryService->petCollectsItem('Plastic', $pet, $pet->getName() . ' accidentally melted a Plastic Boomerang. This is all that remains.', $activityLog);
                return $activityLog;
            }
        }
        else if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PLASTIC_PRINT, true);
            $this->inventoryService->loseItem('Plastic Boomerang', $pet->getOwner(), LocationEnum::HOME, 2);
            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
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
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::PLASTIC_PRINT, false);

            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make an Evil Feather Duster, but the base plate of the 3D Printer moved, jacking up the Plastic :(', '');
        }
        else if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PLASTIC_PRINT, true);
            $this->inventoryService->loseItem('Black Feathers', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created an Evil Feather Duster.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;
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
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::PLASTIC_PRINT, false);

            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Compass, but the base plate of the 3D Printer moved, jacking up the Plastic :(', '');
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PLASTIC_PRINT, true);
            $this->inventoryService->loseItem('Iron Bar', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
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
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 4)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::PLASTIC_PRINT, false);

            $lostItem = ArrayFunctions::pick_one([ 'Plastic', 'Yellow Dye', 'Green Dye' ]);

            if($lostItem === 'Plastic')
            {
                $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to print a Toy Alien Gun, but the base plate of the 3D Printer moved, jacking up the Plastic :(', '');
            }
            else
            {
                $this->inventoryService->loseItem($lostItem, $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to print a Toy Alien Gun, but accidentally spilt the ' . $lostItem . ' :(', '');
            }
        }
        else if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PLASTIC_PRINT, true);
            $this->inventoryService->loseItem('Green Dye', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Yellow Dye', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
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
            if(mt_rand(1, 2) === 1)
            {
                $item = $this->itemRepository->findOneByName('Small Plastic Bucket');
                $beingHalloweeny = true;
            }
            else
            {
                $allPlasticItemsExceptBucket = array_filter($allPlasticItems, function($item) {
                    return $item !== 'Small Plastic Bucket';
                });

                $item = $this->itemRepository->findOneByName(ArrayFunctions::pick_one($allPlasticItemsExceptBucket));
            }
        }
        else
        {
            $item = $this->itemRepository->findOneByName(ArrayFunctions::pick_one($allPlasticItems));
        }

        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::PLASTIC_PRINT, false);

            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);

            if($beingHalloweeny)
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wants to make a Halloween-themed bucket, so tried to make ' . $item->getNameWithArticle() . ' as a base, but the base plate of the 3D Printer moved, jacking up the Plastic :(', '');
            else
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make ' . $item->getNameWithArticle() . ', but the base plate of the 3D Printer moved, jacking up the Plastic :(', '');
        }
        else if($roll >= 10)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PLASTIC_PRINT, true);
            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);

            if($beingHalloweeny)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wants to make a Halloween-themed bucket, and created ' . $item->getNameWithArticle() . ' as a base.', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
                ;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created ' . $item->getNameWithArticle() . '.', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
                ;
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
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll <= 3)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::PLASTIC_PRINT, false);

            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Plastic Idol, but the base plate of the 3D Printer moved, jacking up the Plastic :(', '');
        }
        else if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PLASTIC_PRINT, true);
            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
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
