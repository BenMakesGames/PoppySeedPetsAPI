<?php
namespace App\Service\PetActivity\Crafting\Helpers;

use App\Entity\PetActivityLog;
use App\Enum\MeritEnum;
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

class GoldSmithingService
{
    private $petExperienceService;
    private $inventoryService;
    private $responseService;
    private $coinSmithingService;
    private $itemRepository;
    private IRandom $squirrel3;
    private HouseSimService $houseSimService;
    private PetActivityLogTagRepository $petActivityLogTagRepository;

    public function __construct(
        PetExperienceService $petExperienceService, InventoryService $inventoryService, ResponseService $responseService,
        HouseSimService $houseSimService, CoinSmithingService $coinSmithingService, ItemRepository $itemRepository,
        Squirrel3 $squirrel3, PetActivityLogTagRepository $petActivityLogTagRepository
    )
    {
        $this->petExperienceService = $petExperienceService;
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->coinSmithingService = $coinSmithingService;
        $this->itemRepository = $itemRepository;
        $this->squirrel3 = $squirrel3;
        $this->houseSimService = $houseSimService;
        $this->petActivityLogTagRepository = $petActivityLogTagRepository;
    }

    public function createGoldTriangle(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getMusic()->getTotal()) + $petWithSkills->getSmithingBonus()->getTotal());

        $makingItem = $this->itemRepository->findOneByName('Gold Triangle');

        if($roll <= 2)
        {
            $reRoll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

            if($reRoll >= 12)
                return $this->coinSmithingService->makeGoldCoins($petWithSkills, $makingItem);
            else
                return $this->coinSmithingService->spillGold($petWithSkills, $makingItem);
        }
        else if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::MUSIC ]);
            $pet->increaseEsteem(1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Gold Triangle from Gold Bar, and String.', 'items/tool/instrument/triangle-gold')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' created this from Gold Bar, and String.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Gold Triangle, but couldn\'t figure it out.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
            ;
        }
    }

    public function createAubergineScepter(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        $makingItem = $this->itemRepository->findOneByName('Aubergine Scepter');

        if($roll <= 2)
        {
            if($this->squirrel3->rngNextBool())
            {
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);

                $this->houseSimService->getState()->loseItem('Eggplant', 1);

                $this->petExperienceService->gainExp($pet, 1, [PetSkillEnum::CRAFTS]);

                $pet->increaseSafety(-$this->squirrel3->rngNextInt(4, 8));

                if($pet->hasMerit(MeritEnum::GOURMAND))
                {
                    $pet->increaseFood($this->squirrel3->rngNextInt(4, 8));

                    return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make an Aubergine Scepter, but accidentally burnt the Eggplant! %pet:' . $pet->getId() . '.name%, as a true gourmand, could not allow even an Eggplant to go to waste, and ate it!', '')
                        ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing', 'Eating' ]))
                    ;
                }
                else
                {
                    return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make an Aubergine Scepter, but accidentally burned the Eggplant. It smelled _awful_!', '')
                        ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
                    ;
                }
            }
            else
            {
                $reRoll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

                if($reRoll >= 12)
                    return $this->coinSmithingService->makeGoldCoins($petWithSkills, $makingItem);
                else
                    return $this->coinSmithingService->spillGold($petWithSkills, $makingItem);
            }
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Eggplant', 1);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made an Aubergine Scepter.', 'items/tool/wand/aubergine-scepter')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' created... _this_.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            if($this->squirrel3->rngNextInt(1, 10) === 1)
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make an Aubergine Scepter, but couldn\'t figure it out. (It\'s probably for the better.)', 'icons/activity-logs/confused');
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make an Aubergine Scepter, but couldn\'t figure it out.', 'icons/activity-logs/confused');

            $activityLog->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]));

            return $activityLog;
        }
    }

    public function createMoonhammer(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 20)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Fiberglass', 1);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Moon Pearl', 1);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(3);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Moonhammer!', 'items/tool/scythe')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Moonhammer', $pet, $pet->getName() . ' created this from Fiberglass, gold, and a Moon Pearl.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make something out of Fiberglass, but wasn\'t happy with how it was turning out.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
            ;
        }
    }

    public function createVicious(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Blackonite', 1);
            $this->houseSimService->getState()->loseItem('White Cloth', 1);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(3);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made Vicious! (Scary...)', 'items/tool/sword/vicious')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Vicious', $pet, $pet->getName() . ' forged this blade from gold and Blackonite!', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make an evil-looking sword, but couldn\'t get it lookin\' _evil_ enough...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
            ;
        }
    }

    public function createKundravsStandard(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 20)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Dragon Flag', 1);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Dark Scales', 1);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(3);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made Kundrav\'s Standard!', 'items/tool/scythe')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Kundrav\'s Standard', $pet, $pet->getName() . ' created this using Dark Scales, a Gold Bar, and a Dragon Flag!', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to improve a Dragon Flag, but couldn\'t come up with any cool ideas.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
            ;
        }
    }

    public function createFungalClarinet(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2 && $pet->getFood() < 8)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);

            $this->houseSimService->getState()->loseItem('Chanterelle', 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseFood(4);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started making a Fungal Clarinet, but ended up eating the Chanterelle...', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing', 'Eating' ]))
            ;
            return $activityLog;
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Flute', 1);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Chanterelle', 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(3);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Fungal Clarinet with gold keys! (But not Gold Keys.)', 'items/tool/instrument/clarinet-fungal')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Fungal Clarinet', $pet, $pet->getName() . ' created this!', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% thought it might be cool to decorate a Flute, but couldn\'t think of something stylish enough.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
            ;
        }
    }

    public function createCoreopsis(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Plastic Shovel', 1);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Green Dye', 1);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% upgraded a Plastic Shovel into Coreopsis!', 'items/tool/shovel/coreopsis')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Coreopsis', $pet, $pet->getName() . ' created this!', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% thought it might be cool to upgrade a Plastic Shovel, but couldn\'t come up with anything...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
            ;
        }
    }

    public function createGoldKey(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        $makingItem = $this->itemRepository->findOneByName('Gold Key');

        if($roll <= 2)
        {
            $reRoll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

            if($reRoll >= 12)
                return $this->coinSmithingService->makeGoldCoins($petWithSkills, $makingItem);
            else
                return $this->coinSmithingService->spillGold($petWithSkills, $makingItem);
        }
        else if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);

            $keys = $this->squirrel3->rngNextInt(1, 10) === 1 ? 2 : 1;

            if($keys === 2)
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% forged *two* Gold Keys from a Gold Bar!', 'items/key/gold');
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% forged a Gold Key from a Gold Bar.', 'items/key/gold');

            $activityLog->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]));

            $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' forged this from a Gold Bar.', $activityLog);

            if($keys === 2)
                $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' forged this from a Gold Bar.', $activityLog);

            $this->petExperienceService->gainExp($pet, $keys, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem($keys === 1 ? 1 : 10);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to forge a Gold Key from a Gold Bar, but couldn\'t get the shape right.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
            ;
        }
    }

    public function createGoldTuningFork(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        $makingItem = $this->itemRepository->findOneByName('Gold Tuning Fork');

        if($roll <= 2)
        {
            $reRoll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

            if($reRoll >= 12)
                return $this->coinSmithingService->makeGoldCoins($petWithSkills, $makingItem);
            else
                return $this->coinSmithingService->spillGold($petWithSkills, $makingItem);
        }
        else if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% forged a Gold Tuning Fork from a Gold Bar.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
            ;

            $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' forged this from a Gold Bar.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to forge a Gold Tuning Fork from a Gold Bar, but couldn\'t get the shape right.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
            ;
        }
    }

    public function createGoldTelescope(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll === 1)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseSafety(-4);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make a lens from a piece of glass, but cut themselves! :(', 'icons/activity-logs/wounded')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
            ;
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Glass', 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Gold Telescope.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Gold Telescope', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a telescope, but almost broke the glass, and gave up.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
            ;
        }
    }
    public function createGoldCompass(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll === 1)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 8));
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to work gold into an Enchanted Compass, but got burned while trying! :(', 'icons/activity-logs/burn')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
            ;
        }
        else if($roll >= 20)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Enchanted Compass', 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Gold Compass.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 20)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Gold Compass', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to work gold into an Enchanted Compass, but the enchantment resisted any change.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
            ;
        }
    }

    public function createGoldRod(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $smithingRoll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());
        $printerRoll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        $makingItem = $this->itemRepository->findOneByName('Gold Rod');

        if($printerRoll <= 3)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::PLASTIC_PRINT, false);

             $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
             return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Gold Rod, but the 3D Printer started acting, and %pet:' . $pet->getId() . '.name% ended up spending all their time rechecking wires and software settings...', '')
                 ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing', '3D Printing' ]))
             ;
        }
        else if($smithingRoll <= 2)
        {
            $reRoll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

            if($reRoll >= 12)
                return $this->coinSmithingService->makeGoldCoins($petWithSkills, $makingItem);
            else
                return $this->coinSmithingService->spillGold($petWithSkills, $makingItem);
        }
        else if($smithingRoll >= 16)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Plastic', 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Gold Rod from Plastic and a Gold Bar.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing', '3D Printing' ]))
            ;
            $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' created this from Plastic and a Gold Bar.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Gold Rod, but almost burnt themselves, and gave up.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing', '3D Printing' ]))
            ;
        }
    }

    public function createSilverKeyblade(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Silver Key', 1);
            $this->houseSimService->getState()->loseItem('White Cloth', 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Silver Keyblade.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Silver Keyblade', $pet, $pet->getName() . ' created this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a keyblade, but couldn\'t get the hilt right...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
            ;
        }
    }

    public function createNoWhiskNoReward(ComputedPetSkills  $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        $makingItem = $this->itemRepository->findOneByName('No Whisk, No Reward');

        if($roll <= 2)
        {
            $reRoll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

            if($reRoll >= 12)
                return $this->coinSmithingService->makeGoldCoins($petWithSkills, $makingItem);
            else
                return $this->coinSmithingService->spillGold($petWithSkills, $makingItem);
        }
        else if($roll >= 24)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Firestone', 1);
            $this->houseSimService->getState()->loseItem('Culinary Knife', 1);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem($this->squirrel3->rngNextInt(2, 4));

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% smithed No Whisk, No Reward.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 24)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
            ;

            $this->inventoryService->petCollectsItem('No Whisk, No Reward', $pet, $pet->getName() . ' smithed this with the magic heat of a Firestone!', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make No Whisk, No Reward, but couldn\'t get the hilt right...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
            ;
        }
    }

    public function createSiderealLeafSpear(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Iron Bar', 1);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Leaf Spear', 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% decorated a Leaf Spear with a little sun and moon, creating a Sidereal Leaf Spear!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing', 'Crafting' ]))
            ;
            $this->inventoryService->petCollectsItem('Sidereal Leaf Spear', $pet, $pet->getName() . ' created this by attaching a little gold sun and iron moon to a Leaf Spear.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a little gold sun and iron moon, but couldn\'t get the shapes just right...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing', 'Crafting' ]))
            ;
        }
    }
}
