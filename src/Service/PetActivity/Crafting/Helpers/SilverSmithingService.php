<?php
namespace App\Service\PetActivity\Crafting\Helpers;

use App\Entity\Item;
use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Model\ComputedPetSkills;
use App\Repository\ItemRepository;
use App\Repository\PetActivityLogTagRepository;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\TransactionService;

class SilverSmithingService
{
    private $petExperienceService;
    private $inventoryService;
    private $responseService;
    private $itemRepository;
    private $coinSmithingService;
    private IRandom $squirrel3;
    private HouseSimService $houseSimService;
    private PetActivityLogTagRepository $petActivityLogTagRepository;

    public function __construct(
        PetExperienceService $petExperienceService, InventoryService $inventoryService, ResponseService $responseService,
        ItemRepository $itemRepository, CoinSmithingService $coinSmithingService, Squirrel3 $squirrel3,
        HouseSimService $houseSimService, PetActivityLogTagRepository $petActivityLogTagRepository
    )
    {
        $this->petExperienceService = $petExperienceService;
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->itemRepository = $itemRepository;
        $this->coinSmithingService = $coinSmithingService;
        $this->squirrel3 = $squirrel3;
        $this->houseSimService = $houseSimService;
        $this->petActivityLogTagRepository = $petActivityLogTagRepository;
    }

    public function spillSilver(ComputedPetSkills $petWithSkills, Item $triedToMake): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $pet->increaseEsteem(-1);
        $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 12));

        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to forge ' . $triedToMake->getNameWithArticle() . ', but they accidentally burned themselves! :(', 'icons/activity-logs/burn')
            ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing' ]))
        ;

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);

        return $activityLog;
    }

    public function createSilverKey(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        $silverKey = $this->itemRepository->findOneByName('Silver Key');

        if($pet->hasMerit(MeritEnum::SILVERBLOOD))
            $roll += 5;

        if($roll <= 2)
        {
            $reRoll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

            if($reRoll >= 12)
                return $this->coinSmithingService->makeSilverCoins($petWithSkills, $silverKey);
            else
                return $this->spillSilver($petWithSkills, $silverKey);
        }

        if($roll >= 12)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Silver Bar', 1);

            $keys = $roll >= 28 ? 2 : 1;

            if($keys === 2)
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% forged *two* Silver Keys from a Silver Bar!', 'items/key/silver');
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% forged a Silver Key from a Silver Bar.', 'items/key/silver');

            $activityLog->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing' ]));

            $this->inventoryService->petCollectsItem($silverKey, $pet, $pet->getName() . ' forged this from a Silver Bar.', $activityLog);

            if($keys === 2)
                $this->inventoryService->petCollectsItem($silverKey, $pet, $pet->getName() . ' forged this from a Silver Bar.', $activityLog);

            $this->petExperienceService->gainExp($pet, $keys, [ PetSkillEnum::CRAFTS ], $activityLog);
            $pet->increaseEsteem($keys === 1 ? 1 : 6);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to forge a Silver Key from a Silver Bar, but couldn\'t get the shape right.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createBasicSilverCraft(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $making = $this->squirrel3->rngNextFromArray([
            [ 'item' => 'Silver Colander', 'image' => 'items/tool/colander', 'difficulty' => 13, 'experience' => 1 ],
        ]);

        $makingItem = $this->itemRepository->findOneByName($making['item']);

        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($pet->hasMerit(MeritEnum::SILVERBLOOD))
            $roll += 5;

        if($roll <= 2)
        {
            $reRoll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

            if($reRoll >= 12)
                return $this->coinSmithingService->makeSilverCoins($petWithSkills, $makingItem);
            else
                return $this->spillSilver($petWithSkills, $makingItem);
        }

        if($roll >= $making['difficulty'])
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Silver Bar', 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% forged ' . $makingItem->getNameWithArticle() . ' from a Silver Bar.', $making['image'])
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + $making['difficulty'])
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing' ]))
            ;

            $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' forged this from a Silver Bar.', $activityLog);

            $this->petExperienceService->gainExp($pet, $making['experience'], [ PetSkillEnum::CRAFTS ], $activityLog);
            $pet->increaseEsteem(1);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to forge ' . $makingItem->getNameWithArticle() . ' from a Silver Bar, but couldn\'t get the shape right.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createCoralTrident(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($pet->hasMerit(MeritEnum::SILVERBLOOD))
            $roll += 5;

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a trident out of Crown Coral, but shattered the coral completely :(', '')
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing' ]))
            ;

            $this->houseSimService->getState()->loseItem('Crown Coral', 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $pet->increaseEsteem(-$this->squirrel3->rngNextInt(2, 4));
        }
        else if($roll >= 15)
        {
            $this->houseSimService->getState()->loseItem('Silver Bar', 1);
            $this->houseSimService->getState()->loseItem('Crown Coral', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Coral Trident.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Coral Trident', $pet, $pet->getName() . ' created this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started making a Coral Trident, but working with coral is tricky! They gave up after a while...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createElvishMagnifyingGlass(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($pet->hasMerit(MeritEnum::SILVERBLOOD))
            $roll += 5;

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to improve a "Rustic" Magnifying Glass, but burnt it. All that\'s left now is the Glass...', '')
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing' ]))
            ;

            $this->houseSimService->getState()->loseItem('"Rustic" Magnifying Glass', 1);
            $this->inventoryService->petCollectsItem('Glass', $pet, $pet->getName() . ' burned a "Rustic" Magnifying Glass; this is all that remained.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $pet->increaseEsteem(-2);
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Silver Bar', 1);
            $this->houseSimService->getState()->loseItem('"Rustic" Magnifying Glass', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created an Elvish Magnifying Glass.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Elvish Magnifying Glass', $pet, $pet->getName() . ' created this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to improve a "Rustic" Magnifying Glass, but nearly burnt it to a crisp in the process! (Nearly!)', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }
        return $activityLog;
    }

    public function createSylvanFishingRod(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($pet->hasMerit(MeritEnum::SILVERBLOOD))
            $roll += 5;

        $makingItem = $this->itemRepository->findOneByName('Sylvan Fishing Rod');

        if($roll <= 2)
        {
            $reRoll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

            if($reRoll >= 12)
                return $this->coinSmithingService->makeSilverCoins($petWithSkills, $makingItem);
            else
                return $this->spillSilver($petWithSkills, $makingItem);
        }
        else if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Silver Bar', 1);
            $this->houseSimService->getState()->loseItem('Leaf Spear', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% machined some silver components onto a Leaf Spear, making it a Sylvan Fishing Rod!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' created this.', $activityLog);

            return $activityLog;
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to machine some silver, but hand-making tiny gears proved too challenging...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);

            return $activityLog;
        }
    }

    public function createHourglass(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + max($petWithSkills->getStamina()->getTotal(), $petWithSkills->getDexterity()->getTotal()) + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($pet->hasMerit(MeritEnum::SILVERBLOOD))
            $roll += 5;

        if($roll <= 3)
        {
            $this->houseSimService->getState()->loseItem('Glass', 1);
            $pet->increaseEsteem(-2);
            $pet->increaseSafety(-4);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried blowing Glass, but burnt themselves, and dropped the glass :(', 'icons/activity-logs/wounded')
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Silver Bar', 1);
            $this->houseSimService->getState()->loseItem('Glass', 1);
            $this->houseSimService->getState()->loseItem('Silica Grounds', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created an Hourglass.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->inventoryService->petCollectsItem('Hourglass', $pet, $pet->getName() . ' created this.', $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make an Hourglass, but it\'s so detailed and fiddly! Ugh!', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createSharktoothAxe(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($pet->hasMerit(MeritEnum::SILVERBLOOD))
            $roll += 5;

        $makingItem = $this->itemRepository->findOneByName('Sharktooth Axe');

        if($roll == 1)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to improve an Iron Axe, but accidentally burnt the String they were using :|', '')
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing', 'Crafting' ]))
            ;

            $this->houseSimService->getState()->loseItem('String', 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $pet->increaseEsteem(-1);

            return $activityLog;
        }

        if($roll == 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to improve an Iron Axe, but accidentally cracked the Talon they were using :|', '')
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing', 'Crafting' ]))
            ;

            $this->houseSimService->getState()->loseItem('Talon', 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $pet->increaseEsteem(-1);

            return $activityLog;
        }

        if($roll == 3)
        {
            $reRoll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

            if($reRoll >= 12)
                return $this->coinSmithingService->makeSilverCoins($petWithSkills, $makingItem);
            else
                return $this->spillSilver($petWithSkills, $makingItem);
        }

        if($roll >= 19)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Silver Bar', 1);
            $this->houseSimService->getState()->loseItem('Iron Axe', 1);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Talon', 1);
            $pet->increaseEsteem(4);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Sharktooth Axe.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 19)
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing', 'Crafting' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' created this.', $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to silver an Iron Axe, but couldn\'t get the temperature just right...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing', 'Crafting' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createGoldKeyblade(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($pet->hasMerit(MeritEnum::SILVERBLOOD))
            $roll += 5;

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a keyblade, but accidentally tore the White Cloth :|', '')
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing', 'Crafting' ]))
            ;

            $this->houseSimService->getState()->loseItem('White Cloth', 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $pet->increaseEsteem(-1);
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Silver Bar', 1);
            $this->houseSimService->getState()->loseItem('Gold Key', 1);
            $this->houseSimService->getState()->loseItem('White Cloth', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Gold Keyblade.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 15)
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing', 'Crafting' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->inventoryService->petCollectsItem('Gold Keyblade', $pet, $pet->getName() . ' created this.', $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a keyblade, but couldn\'t get the hilt right...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing', 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createLightningAxe(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($pet->hasMerit(MeritEnum::SILVERBLOOD))
            $roll += 5;

        $makingItem = $this->itemRepository->findOneByName('Lightning Axe');

        if($roll <= 2)
        {
            $reRoll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

            if($reRoll >= 12)
                return $this->coinSmithingService->makeSilverCoins($petWithSkills, $makingItem);
            else
                return $this->spillSilver($petWithSkills, $makingItem);
        }
        else if($roll >= 22)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Silver Bar', 1);
            $this->houseSimService->getState()->loseItem('Wand of Lightning', 1);
            $pet->increaseEsteem($this->squirrel3->rngNextInt(2, 4));
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% added a silver-iron blade to a Wand of Lightning, creating a Lightning Axe.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 22)
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing' ]))
            ;
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->inventoryService->petCollectsItem('Lightning Axe', $pet, $pet->getName() . ' created this by adding a silver-iron blade to a Wand of Lightning.', $activityLog);

            return $activityLog;
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wanted to add an axe blade to a Wand of Lightning, but couldn\'t figure out how to make it work...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);

            return $activityLog;
        }
    }

    public function createSilveredMericarp(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($pet->hasMerit(MeritEnum::SILVERBLOOD))
            $roll += 5;

        if($roll >= 22)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Silver Bar', 1);
            $this->houseSimService->getState()->loseItem('Mericarp', 1);
            $pet->increaseEsteem(4);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Silvered Mericarp!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 22)
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing', 'Crafting' ]))
            ;
            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->inventoryService->petCollectsItem('Silvered Mericarp', $pet, $pet->getName() . ' created this by gilding a Mericarp.', $activityLog);
            return $activityLog;
        }
        else if($roll == 1)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
            $this->houseSimService->getState()->loseItem('Mericarp', 1);
            $pet->increaseEsteem(-4);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to silver a Mericarp, but messed up and reduced it to Charcoal! >:(', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 22)
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing', 'Crafting' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->inventoryService->petCollectsItem('Charcoal', $pet, $pet->getName() . ' accidentally reduced a Mericarp to this...', $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to silver a Mericarp, but almost accidentally burned it to a crisp, instead!', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Smithing', 'Crafting' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }
}
