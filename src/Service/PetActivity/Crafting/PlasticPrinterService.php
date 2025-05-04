<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Service\PetActivity\Crafting;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityLogTagEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetBadgeEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Model\ActivityCallback;
use App\Model\ComputedPetSkills;
use App\Model\IActivityCallback;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;

class PlasticPrinterService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly PetExperienceService $petExperienceService,
        private readonly IRandom $rng,
        private readonly HouseSimService $houseSimService,
        private readonly EntityManagerInterface $em
    )
    {
    }

    /**
     * @return IActivityCallback[]
     */
    public function getCraftingPossibilities(ComputedPetSkills $petWithSkills): array
    {
        $possibilities = [];

        if($this->houseSimService->hasInventory('3D Printer') && $this->houseSimService->hasInventory('Plastic'))
        {
            $possibilities[] = new ActivityCallback($this->createPlasticCraft(...), 10);
            $possibilities[] = new ActivityCallback($this->createPlasticIdol(...), 5);

            if($this->houseSimService->hasInventory('String', 2))
                $possibilities[] = new ActivityCallback($this->createJumpRope(...), 10);

            if($this->houseSimService->hasInventory('Iron Bar'))
                $possibilities[] = new ActivityCallback($this->createCompass(...), 10);

            if($this->houseSimService->hasInventory('String'))
                $possibilities[] = new ActivityCallback($this->createPlasticFishingRod(...), 10);

            if($this->houseSimService->hasInventory('Green Dye') && $this->houseSimService->hasInventory('Yellow Dye'))
                $possibilities[] = new ActivityCallback($this->createAlienLaser(...), 10);

            if($this->houseSimService->hasInventory('Black Feathers'))
                $possibilities[] = new ActivityCallback($this->createEvilFeatherDuster(...), 10);

            if($this->houseSimService->hasInventory('Plastic Boomerang', 2))
                $possibilities[] = new ActivityCallback($this->createNonsenserang(...), 10);

            if($this->houseSimService->hasInventory('String') && $this->houseSimService->hasInventory('Antenna') && $this->houseSimService->hasInventory('Green Dye'))
                $possibilities[] = new ActivityCallback($this->createDicerca(...), 12);

            if($this->houseSimService->hasInventory('Grabby Arm') && $this->houseSimService->hasInventory('Green Dye'))
                $possibilities[] = new ActivityCallback($this->createDinoGrabbyArm(...), 10);
        }

        return $possibilities;
    }

    private function printerActingUp(Pet $pet): PetActivityLog
    {
        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to print something out of Plastic, but the 3D Printer kept acting up.')
            ->setIcon('icons/activity-logs/confused')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                '3D Printing',
                PetActivityLogTagEnum::Location_At_Home
            ]))
        ;

        PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::STRUGGLED_WITH_A_3D_PRINTER, $activityLog);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::SCIENCE ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::PLASTIC_PRINT, false);

        return $activityLog;
    }

    public function createPlasticFishingRod(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + max($petWithSkills->getScience()->getTotal(), $petWithSkills->getCrafts()->getTotal()) + $petWithSkills->getNature()->getTotal() - $petWithSkills->getNature()->base);

        if($roll >= 12)
        {
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Plastic', 1);

            $pet->increaseEsteem(2);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Plastic Fishing Rod.')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 12)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    '3D Printing',
                    PetActivityLogTagEnum::Location_At_Home
                ]))
            ;
            $this->inventoryService->petCollectsItem('Plastic Fishing Rod', $pet, $pet->getName() . ' created this from String and Plastic.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::PLASTIC_PRINT, true);

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
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + max($petWithSkills->getScience()->getTotal(), $petWithSkills->getCrafts()->getTotal()));

        if($roll >= 14)
        {
            $this->houseSimService->getState()->loseItem('Plastic Boomerang', 2);
            $this->houseSimService->getState()->loseItem('Plastic', 1);

            $pet->increaseEsteem(2);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Nonsenserang!')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    '3D Printing',
                    PetActivityLogTagEnum::Location_At_Home
                ]))
            ;
            $this->inventoryService->petCollectsItem('Nonsenserang', $pet, $pet->getName() . ' fused two Plastic Boomerangs together, and printed up an extra set of blades, producing this ridiculous implement.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::PLASTIC_PRINT, true);

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
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + max($petWithSkills->getScience()->getTotal(), $petWithSkills->getCrafts()->getTotal()));

        if($roll >= 16)
        {
            $this->houseSimService->getState()->loseItem('Black Feathers', 1);
            $this->houseSimService->getState()->loseItem('Plastic', 1);

            $getExtraStuff = $this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal())
                >= 21
            ;

            $pet->increaseEsteem(3);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::PLASTIC_PRINT, true);

            if($getExtraStuff)
            {
                $extraLoot = ItemRepository::findOneByName($this->em, $this->rng->rngNextFromArray([
                    'Fluff', 'Feathers', 'Dark Matter', 'Aging Powder', 'Baking Powder', 'Spider', 'Moon Dust',
                ]));

                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created an Evil Feather Duster. While they were testing it out, they found ' . $extraLoot->getNameWithArticle() . '!')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 21)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                        '3D Printing',
                        PetActivityLogTagEnum::Location_At_Home
                    ]))
                ;

                $this->inventoryService->petCollectsItem($extraLoot, $pet, $pet->getName() . ' found this while trying out the Evil Feather Duster they just made.', $activityLog);
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created an Evil Feather Duster.')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                        '3D Printing',
                        PetActivityLogTagEnum::Location_At_Home
                    ]))
                ;
            }

            $this->inventoryService->petCollectsItem('Evil Feather Duster', $pet, $pet->getName() . ' created this from Black Feathers and Plastic.', $activityLog);
            $this->petExperienceService->gainExp($pet, $getExtraStuff ? 3 : 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ], $activityLog);

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
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + max($petWithSkills->getScience()->getTotal(), $petWithSkills->getCrafts()->getTotal()));

        if($roll >= 12)
        {
            $this->houseSimService->getState()->loseItem('Iron Bar', 1);
            $this->houseSimService->getState()->loseItem('Plastic', 1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Compass.')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 12)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    '3D Printing',
                    PetActivityLogTagEnum::Location_At_Home
                ]))
            ;
            $this->inventoryService->petCollectsItem('Compass', $pet, $pet->getName() . ' created this from Plastic and an Iron Bar.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::PLASTIC_PRINT, true);

            return $activityLog;
        }
        else
        {
            return $this->printerActingUp($pet);
        }
    }

    public function createJumpRope(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + max($petWithSkills->getScience()->getTotal(), $petWithSkills->getCrafts()->getTotal()));

        if($roll >= 12)
        {
            $this->houseSimService->getState()->loseItem('Plastic', 1);
            $this->houseSimService->getState()->loseItem('String', 2);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Jump Rope.')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 12)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    '3D Printing',
                    'Crafting',
                    PetActivityLogTagEnum::Location_At_Home
                ]))
            ;
            $this->inventoryService->petCollectsItem('Jump Rope', $pet, $pet->getName() . ' created this from Plastic and a couple Strings tightly wound together.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::PLASTIC_PRINT, true);

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
            (int)floor(($petWithSkills->getScience()->getTotal() + $petWithSkills->getCrafts()->getTotal()) / 2)
        ;

        $roll = $this->rng->rngNextInt(1, 20 + $skill);

        if($roll >= 18)
        {
            $this->houseSimService->getState()->loseItem('Green Dye', 1);
            $this->houseSimService->getState()->loseItem('Plastic', 1);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Antenna', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% printed up and decorated a Dicerca mask.')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 18)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    '3D Printing',
                    'Crafting',
                    PetActivityLogTagEnum::Location_At_Home
                ]))
            ;
            $this->inventoryService->petCollectsItem('Dicerca', $pet, $pet->getName() . ' printed this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(120, 150), PetActivityStatEnum::PLASTIC_PRINT, true);

            return $activityLog;
        }
        else
            return $this->printerActingUp($pet);
    }

    public function createAlienLaser(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + max($petWithSkills->getScience()->getTotal(), $petWithSkills->getCrafts()->getTotal()));

        if($roll >= 14)
        {
            $this->houseSimService->getState()->loseItem('Green Dye', 1);
            $this->houseSimService->getState()->loseItem('Yellow Dye', 1);
            $this->houseSimService->getState()->loseItem('Plastic', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% printed a Toy Alien Gun.')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 14)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    '3D Printing',
                    PetActivityLogTagEnum::Location_At_Home
                ]))
            ;
            $this->inventoryService->petCollectsItem('Toy Alien Gun', $pet, $pet->getName() . ' printed this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::PLASTIC_PRINT, true);

            return $activityLog;
        }
        else
            return $this->printerActingUp($pet);
    }

    public function createPlasticCraft(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + max($petWithSkills->getScience()->getTotal(), $petWithSkills->getCrafts()->getTotal()));

        if($roll < 10)
            return $this->printerActingUp($pet);

        $itemToCraft = ItemRepository::findOneByName($this->em, $this->rng->rngNextFromArray([
            'Small Plastic Bucket',
            'Plastic Shovel',
            'Egg Carton',
            'Ruler',
            'Plastic Boomerang',
        ]));

        $this->houseSimService->getState()->loseItem('Plastic', 1);

        $pet->increaseEsteem(2);

        if($roll >= 30 && $pet->hasMerit(MeritEnum::BEHATTED))
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created ' . $itemToCraft->getNameWithArticle() . '... and a pair of Googly Eyes with bits of leftover Plastic!')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 30)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    '3D Printing',
                    PetActivityLogTagEnum::Location_At_Home
                ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);

            $this->inventoryService->petCollectsItem('Googly Eyes', $pet, $pet->getName() . ' created this from Plastic.', $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created ' . $itemToCraft->getNameWithArticle() . '.')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 10)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    '3D Printing',
                    PetActivityLogTagEnum::Location_At_Home
                ]))
            ;
        }

        $this->inventoryService->petCollectsItem($itemToCraft, $pet, $pet->getName() . ' created this from Plastic.', $activityLog);

        $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::PLASTIC_PRINT, true);

        return $activityLog;
    }

    public function createPlasticIdol(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + max($petWithSkills->getScience()->getTotal(), $petWithSkills->getCrafts()->getTotal()));

        if($roll < 13)
            return $this->printerActingUp($pet);

        $this->houseSimService->getState()->loseItem('Plastic', 1);

        $pet->increaseEsteem(2);

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Plastic Idol.')
            ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                '3D Printing',
                PetActivityLogTagEnum::Location_At_Home
            ]))
        ;
        $this->inventoryService->petCollectsItem('Plastic Idol', $pet, $pet->getName() . ' created this from Plastic.', $activityLog);

        $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::PLASTIC_PRINT, true);

        return $activityLog;
    }

    public function createDinoGrabbyArm(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + max($petWithSkills->getScience()->getTotal(), $petWithSkills->getCrafts()->getTotal()));

        if($roll >= 13)
        {
            $this->houseSimService->getState()->loseItem('Plastic', 1);
            $this->houseSimService->getState()->loseItem('Green Dye', 1);
            $this->houseSimService->getState()->loseItem('Grabby Arm', 1);

            $pet->increaseEsteem(2);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% zhuzhed up a Grabby Arm, turning it into a Dino Grabby Arm.')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 13)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    '3D Printing',
                    PetActivityLogTagEnum::Location_At_Home
                ]))
            ;
            $this->inventoryService->petCollectsItem('Dino Grabby Arm', $pet, $pet->getName() . ' created this by zhuzhing up a Grabby Arm.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(90, 120), PetActivityStatEnum::PLASTIC_PRINT, true);

            return $activityLog;
        }
        else
        {
            return $this->printerActingUp($pet);
        }
    }
}
