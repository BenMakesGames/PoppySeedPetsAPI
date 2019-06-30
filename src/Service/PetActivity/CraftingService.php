<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Model\PetChanges;
use App\Repository\ItemRepository;
use App\Service\InventoryService;
use App\Service\PetService;
use App\Service\ResponseService;

class CraftingService
{
    private $responseService;
    private $inventoryService;
    private $petService;
    private $itemRepository;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, PetService $petService,
        ItemRepository $itemRepository
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petService = $petService;
        $this->itemRepository = $itemRepository;
    }

    public function adventure(Pet $pet)
    {
        $quantities = $this->itemRepository->getInventoryQuantities($pet->getOwner(), 'name');

        $possibilities = [];

        if(array_key_exists('Fluff', $quantities))
        {
            $possibilities[] = 'createStringFromFluff';
        }


        if(array_key_exists('String', $quantities) && array_key_exists('Crooked Stick', $quantities))
            $possibilities[] = 'createCrookedFishingRod';

        if(count($possibilities) === 0)
        {
            $pet->spendTime(\mt_rand(30, 60));

            $this->responseService->createActivityLog($pet, $pet->getName() . ' wanted to make something, but couldn\'t find any materials to work with.');
            return;
        }

        $method = $possibilities[\mt_rand(0, count($possibilities) - 1)];

        $activityLog = null;
        $changes = new PetChanges($pet);

        $activityLog = call_user_func([ $this, $method ], [ $pet ]);

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));
    }

    private function createStringFromFluff(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getSkills()->getIntelligence() + $pet->getSkills()->getDexterity() + $pet->getSkills()->getCrafts());
        if($roll <= 2)
        {
            $this->inventoryService->loseItem('Fluff', 1);
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'dexterity', 'crafts' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to spin some Fluff into String, but messed it up; the Fluff was wasted.');
        }
        else if($roll >= 10)
        {
            $this->inventoryService->loseItem('Fluff', 1);
            $this->inventoryService->petCollectsItem('String', $pet, $pet->getName() . ' spun this from Fluff.');
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'dexterity', 'crafts' ]);
            $pet->increaseEsteem(1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' spun some Fluff into String.');
        }
        else
        {
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'dexterity', 'crafts' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to spin some Fluff into String, but couldn\'t figure it out.');
        }
    }

    private function createCrookedFishingRod(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getSkills()->getIntelligence() + $pet->getSkills()->getDexterity() + \max($pet->getSkills()->getCrafts(), $pet->getSkills()->getNature()));

        if($roll <= 3)
        {
            if(\mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', 1);
                $this->petService->gainExp($pet, 1, [ 'intelligence', 'dexterity', 'crafts', 'nature' ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Crooked Fishing Rod, but broke the String :(');
            }
            else
            {
                $this->inventoryService->loseItem('Crooked Stick', 1);
                $this->petService->gainExp($pet, 1, [ 'intelligence', 'dexterity', 'crafts',  'nature' ]);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Crooked Fishing Rod, but broke the Crooked Stick :(');

            }
        }
        else if($roll >= 12)
        {
            $this->inventoryService->loseItem('String', 1);
            $this->inventoryService->loseItem('Crooked Stick', 1);
            $this->inventoryService->petCollectsItem('Crooked Fishing Rod', $pet, $pet->getName() . ' created this from String and a Crooked Stick.');
            $this->petService->gainExp($pet, 2, [ 'intelligence', 'dexterity', 'crafts',  'nature' ]);
            $pet->increaseEsteem(2);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Crooked Fishing Rod.');
        }
        else
        {
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'dexterity', 'crafts',  'nature' ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a Crooked Fishing Rod, but couldn\'t figure it out.');
        }
    }
}