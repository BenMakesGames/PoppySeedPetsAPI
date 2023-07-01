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
use App\Repository\PetActivityLogTagRepository;
use App\Service\CalendarService;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;

class PlasticPrinterService
{
    private InventoryService $inventoryService;
    private ResponseService $responseService;
    private PetExperienceService $petExperienceService;
    private ItemRepository $itemRepository;
    private CalendarService $calendarService;
    private IRandom $squirrel3;
    private HouseSimService $houseSimService;
    private PetActivityLogTagRepository $petActivityLogTagRepository;

    public function __construct(
        InventoryService $inventoryService, ResponseService $responseService, PetExperienceService $petExperienceService,
        ItemRepository $itemRepository, CalendarService $calendarService, Squirrel3 $squirrel3,
        HouseSimService $houseSimService, PetActivityLogTagRepository $petActivityLogTagRepository
    )
    {
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->petExperienceService = $petExperienceService;
        $this->itemRepository = $itemRepository;
        $this->calendarService = $calendarService;
        $this->squirrel3 = $squirrel3;
        $this->houseSimService = $houseSimService;
        $this->petActivityLogTagRepository = $petActivityLogTagRepository;
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
            $possibilities[] = new ActivityCallback($this, 'createPlasticIdol', 5);

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

            if($this->houseSimService->hasInventory('String') && $this->houseSimService->hasInventory('Antenna') && $this->houseSimService->hasInventory('Green Dye'))
                $possibilities[] = new ActivityCallback($this, 'createDicerca', 12);

            if($this->houseSimService->hasInventory('Grabby Arm') && $this->houseSimService->hasInventory('Green Dye'))
                $possibilities[] = new ActivityCallback($this, 'createDinoGrabbyArm', 10);
        }

        return $possibilities;
    }

    private function printerActingUp(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ]);
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PLASTIC_PRINT, false);
        return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to print something out of Plastic, but the 3D Printer kept acting up.', 'icons/activity-logs/confused')
            ->addTags($this->petActivityLogTagRepository->findByNames([ '3D Printing' ]))
        ;
    }

    public function createPlasticFishingRod(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + max($petWithSkills->getScience()->getTotal(), $petWithSkills->getCrafts()->getTotal()) + $petWithSkills->getNature()->getTotal() - $petWithSkills->getNature()->base);

        if($roll >= 12)
        {
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Plastic', 1);

            $pet->increaseEsteem(2);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PLASTIC_PRINT, true);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Plastic Fishing Rod.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 12)
                ->addTags($this->petActivityLogTagRepository->findByNames([ '3D Printing' ]))
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
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + max($petWithSkills->getScience()->getTotal(), $petWithSkills->getCrafts()->getTotal()));

        if($roll >= 14)
        {
            $this->houseSimService->getState()->loseItem('Plastic Boomerang', 2);
            $this->houseSimService->getState()->loseItem('Plastic', 1);

            $pet->increaseEsteem(2);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PLASTIC_PRINT, true);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Nonsenserang!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
                ->addTags($this->petActivityLogTagRepository->findByNames([ '3D Printing' ]))
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
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + max($petWithSkills->getScience()->getTotal(), $petWithSkills->getCrafts()->getTotal()));

        if($roll >= 16)
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
                    ->addTags($this->petActivityLogTagRepository->findByNames([ '3D Printing' ]))
                ;

                $this->inventoryService->petCollectsItem($extraLoot, $pet, $pet->getName() . ' found this while trying out the Evil Feather Duster they just made.', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created an Evil Feather Duster.', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                    ->addTags($this->petActivityLogTagRepository->findByNames([ '3D Printing' ]))
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
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + max($petWithSkills->getScience()->getTotal(), $petWithSkills->getCrafts()->getTotal()));

        if($roll >= 12)
        {
            $this->houseSimService->getState()->loseItem('Iron Bar', 1);
            $this->houseSimService->getState()->loseItem('Plastic', 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PLASTIC_PRINT, true);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Compass.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 12)
                ->addTags($this->petActivityLogTagRepository->findByNames([ '3D Printing' ]))
            ;
            $this->inventoryService->petCollectsItem('Compass', $pet, $pet->getName() . ' created this from Plastic and an Iron Bar.', $activityLog);

            return $activityLog;
        }
        else
        {
            return $this->printerActingUp($pet);
        }
    }

    public function createDicerca(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $skill =
            $petWithSkills->getIntelligence()->getTotal() +
            $petWithSkills->getDexterity()->getTotal() +
            floor(($petWithSkills->getScience()->getTotal() + $petWithSkills->getCrafts()->getTotal()) / 2)
        ;

        $roll = $this->squirrel3->rngNextInt(1, 20 + $skill);

        if($roll >= 18)
        {
            $this->houseSimService->getState()->loseItem('Green Dye', 1);
            $this->houseSimService->getState()->loseItem('Plastic', 1);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Antenna', 1);
            $pet->increaseEsteem(2);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::PLASTIC_PRINT, true);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% printed up and decorated a Dicerca mask.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                ->addTags($this->petActivityLogTagRepository->findByNames([ '3D Printing', 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Dicerca', $pet, $pet->getName() . ' printed this.', $activityLog);
            return $activityLog;
        }
        else
            return $this->printerActingUp($pet);
    }

    public function createAlienLaser(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + max($petWithSkills->getScience()->getTotal(), $petWithSkills->getCrafts()->getTotal()));

        if($roll >= 14)
        {
            $this->houseSimService->getState()->loseItem('Green Dye', 1);
            $this->houseSimService->getState()->loseItem('Yellow Dye', 1);
            $this->houseSimService->getState()->loseItem('Plastic', 1);
            $pet->increaseEsteem(2);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PLASTIC_PRINT, true);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% printed a Toy Alien Gun.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
                ->addTags($this->petActivityLogTagRepository->findByNames([ '3D Printing' ]))
            ;
            $this->inventoryService->petCollectsItem('Toy Alien Gun', $pet, $pet->getName() . ' printed this.', $activityLog);
            return $activityLog;
        }
        else
            return $this->printerActingUp($pet);
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

        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + max($petWithSkills->getScience()->getTotal(), $petWithSkills->getCrafts()->getTotal()));

        if($roll >= 10)
        {
            $this->houseSimService->getState()->loseItem('Plastic', 1);

            $pet->increaseEsteem(2);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PLASTIC_PRINT, true);

            if($beingHalloweeny)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wants to make a Halloween-themed bucket, and created ' . $item->getNameWithArticle() . ' as a base.', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
                    ->addTags($this->petActivityLogTagRepository->findByNames([ '3D Printing', 'Special Event', 'Halloween' ]))
                ;
            }
            else
            {
                if($roll >= 30 && $pet->hasMerit(MeritEnum::BEHATTED))
                {
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created ' . $item->getNameWithArticle() . '... and a pair of Googly Eyes with bits of leftover Plastic!', '')
                        ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 30)
                        ->addTags($this->petActivityLogTagRepository->findByNames([ '3D Printing' ]))
                    ;

                    $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);

                    $this->inventoryService->petCollectsItem('Googly Eyes', $pet, $pet->getName() . ' created this from Plastic.', $activityLog);
                }
                else
                {
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created ' . $item->getNameWithArticle() . '.', '')
                        ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
                        ->addTags($this->petActivityLogTagRepository->findByNames([ '3D Printing' ]))
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
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + max($petWithSkills->getScience()->getTotal(), $petWithSkills->getCrafts()->getTotal()));

        if($roll >= 13)
        {
            $this->houseSimService->getState()->loseItem('Plastic', 1);

            $pet->increaseEsteem(2);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PLASTIC_PRINT, true);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Plastic Idol.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
                ->addTags($this->petActivityLogTagRepository->findByNames([ '3D Printing' ]))
            ;
            $this->inventoryService->petCollectsItem('Plastic Idol', $pet, $pet->getName() . ' created this from Plastic.', $activityLog);

            return $activityLog;
        }
        else
        {
            return $this->printerActingUp($pet);
        }
    }

    public function createDinoGrabbyArm(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + max($petWithSkills->getScience()->getTotal(), $petWithSkills->getCrafts()->getTotal()));

        if($roll >= 13)
        {
            $this->houseSimService->getState()->loseItem('Plastic', 1);
            $this->houseSimService->getState()->loseItem('Green Dye', 1);
            $this->houseSimService->getState()->loseItem('Grabby Arm', 1);

            $pet->increaseEsteem(2);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PLASTIC_PRINT, true);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% zhuzhed up a Grabby Arm, turning it into a Dino Grabby Arm.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
                ->addTags($this->petActivityLogTagRepository->findByNames([ '3D Printing' ]))
            ;
            $this->inventoryService->petCollectsItem('Dino Grabby Arm', $pet, $pet->getName() . ' created this by zhuzhing up a Grabby Arm.', $activityLog);

            return $activityLog;
        }
        else
        {
            return $this->printerActingUp($pet);
        }
    }
}
