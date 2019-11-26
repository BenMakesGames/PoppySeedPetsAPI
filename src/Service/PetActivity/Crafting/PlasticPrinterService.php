<?php
namespace App\Service\PetActivity\Crafting;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Enum\PetSkillEnum;
use App\Service\InventoryService;
use App\Service\PetService;
use App\Service\ResponseService;

class PlasticPrinterService
{
    private $inventoryService;
    private $petService;
    private $responseService;

    public function __construct(
        InventoryService $inventoryService, PetService $petService, ResponseService $responseService
    )
    {
        $this->inventoryService = $inventoryService;
        $this->petService = $petService;
        $this->responseService = $responseService;
    }

    public function getCraftingPossibilities(Pet $pet, array $quantities): array
    {
        $possibilities = [];

        if(array_key_exists('3D Printer', $quantities) && array_key_exists('Plastic', $quantities))
        {
            $possibilities[] = [ $this, 'createPlasticBucket' ];

            if(mt_rand(1, 3) === 1)
                $possibilities[] = [ $this, 'createPlasticIdol' ];

            if(array_key_exists('String', $quantities))
                $possibilities[] = [ $this, 'createPlasticFishingRod' ];

            if(array_key_exists('Black Feathers', $quantities))
                $possibilities[] = [ $this, 'createEvilFeatherDuster' ];
        }

        return $possibilities;
    }

    private function printerActingUp(Pet $pet): PetActivityLog
    {
        $this->petService->spendTime($pet, \mt_rand(30, 60));
        $this->petService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::COMPUTER ]);
        return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Plastic Fishing Rod, but the 3D Printer kept acting up.', 'icons/activity-logs/confused');
    }

    public function createPlasticFishingRod(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getComputer() + \max($pet->getCrafts(), $pet->getNature()));

        if($roll <= 3)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));
            if(\mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::COMPUTER, PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Plastic Fishing Rod, but broke the String :(', 'icons/activity-logs/broke-string');
            }
            else
            {
                $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petService->gainExp($pet, 1, [ PetSkillEnum::COMPUTER, PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Plastic Fishing Rod, but the base plate of the 3D Printer moved, jacking up the Plastic :(', '');

            }
        }
        else if($roll >= 12)
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60));
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::COMPUTER, PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Plastic Fishing Rod.', '');
            $this->inventoryService->petCollectsItem('Plastic Fishing Rod', $pet, $pet->getName() . ' created this from String and Plastic.', $activityLog);
            return $activityLog;
        }
        else
        {
            return $this->printerActingUp($pet);
        }
    }

    public function createEvilFeatherDuster(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getComputer() + \max($pet->getCrafts(), $pet->getNature()));

        if($roll <= 3)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));

            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::COMPUTER, PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make an Evil Feather Duster, but the base plate of the 3D Printer moved, jacking up the Plastic :(', '');
        }
        else if($roll >= 16)
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60));
            $this->inventoryService->loseItem('Black Feathers', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::COMPUTER, PetSkillEnum::CRAFTS, PetSkillEnum::NATURE ]);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created an Evil Feather Duster.', '');
            $this->inventoryService->petCollectsItem('Evil Feather Duster', $pet, $pet->getName() . ' created this from Black Feathers and Plastic.', $activityLog);
            return $activityLog;
        }
        else
        {
            return $this->printerActingUp($pet);
        }
    }

    public function createPlasticBucket(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getComputer() + $pet->getCrafts());

        if($roll <= 3)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));

            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::COMPUTER, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Plastic Fishing Rod, but the base plate of the 3D Printer moved, jacking up the Plastic :(', '');
        }
        else if($roll >= 10)
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60));
            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::COMPUTER, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Small Plastic Bucket.', '');
            $this->inventoryService->petCollectsItem('Small Plastic Bucket', $pet, $pet->getName() . ' created this from Plastic.', $activityLog);
            return $activityLog;
        }
        else
        {
            return $this->printerActingUp($pet);
        }
    }

    public function createPlasticIdol(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getComputer() + $pet->getCrafts());

        if($roll <= 3)
        {
            $this->petService->spendTime($pet, \mt_rand(30, 60));

            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::COMPUTER, PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Plastic Idol, but the base plate of the 3D Printer moved, jacking up the Plastic :(', '');
        }
        else if($roll >= 13)
        {
            $this->petService->spendTime($pet, \mt_rand(45, 60));
            $this->inventoryService->loseItem('Plastic', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::COMPUTER, PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Plastic Idol.', '');
            $this->inventoryService->petCollectsItem('Plastic Idol', $pet, $pet->getName() . ' created this from Plastic.', $activityLog);
            return $activityLog;
        }
        else
        {
            return $this->printerActingUp($pet);
        }
    }

}