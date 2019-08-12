<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Model\PetChanges;
use App\Repository\ItemRepository;
use App\Service\InventoryService;
use App\Service\PetActivity\Crafting\RefiningService;
use App\Service\PetActivity\Crafting\ScrollMakingService;
use App\Service\PetService;
use App\Service\ResponseService;

class ProgrammingService
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

    public function getCraftingPossibilities(Pet $pet): array
    {
        $quantities = $this->itemRepository->getInventoryQuantities($pet->getOwner(), 'name');

        $possibilities = [];

        if(array_key_exists('Pointer', $quantities))
        {
            $possibilities[] = [ $this, 'createStringFromPointer' ];

            if(array_key_exists('Finite State Machine', $quantities))
                $possibilities[] = [ $this, 'createRegex' ];
        }

        if(array_key_exists('Hash Table', $quantities) && array_key_exists('Finite State Machine', $quantities) && array_key_exists('String', $quantities))
            $possibilities[] = [ $this, 'createCompiler' ];

        return $possibilities;
    }

    public function adventure(Pet $pet, array $possibilities): PetActivityLog
    {
        if(count($possibilities) === 0)
            throw new \InvalidArgumentException('possibilities must contain at least one item.');

        $method = ArrayFunctions::pick_one($possibilities);

        $activityLog = null;
        $changes = new PetChanges($pet);

        /** @var PetActivityLog $activityLog */
        $activityLog = $method($pet);

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));

        return $activityLog;
    }

    private function createStringFromPointer(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getComputer());
        if($roll <= 2)
        {
            $pet->spendTime(\mt_rand(30, 60));
            $this->inventoryService->loseItem('Pointer', $pet->getOwner(), 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::COMPUTER ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to dereference a String from a Pointer, but encountered a null exception :(', 'icons/activity-logs/null');
        }
        else if($roll >= 10)
        {
            $pet->spendTime(\mt_rand(45, 60));
            $this->inventoryService->loseItem('Pointer', $pet->getOwner(), 1);
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::COMPUTER ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' dereferenced a String from a Pointer.', 'items/resource/string');
            $this->inventoryService->petCollectsItem('String', $pet, $pet->getName() . ' dereferenced this from a Pointer.', $activityLog);
            return $activityLog;
        }
        else
        {
            $pet->spendTime(\mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::COMPUTER ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to dereference a Pointer, but couldn\'t figure out all the syntax errors.', 'icons/activity-logs/confused');
        }
    }

    private function createRegex(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getComputer());
        if($roll <= 2)
        {
            $pet->spendTime(\mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::COMPUTER ]);

            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('Pointer', $pet->getOwner(), 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a Regex, but mis-scoped a Pointer :(', 'icons/activity-logs/null');
            }
            else
            {
                $this->inventoryService->loseItem('Finite State Machine', $pet->getOwner(), 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a Regex, but lost a Finite State Machine to a stack overflow :(', 'icons/activity-logs/null');
            }
        }
        else if($roll >= 14)
        {
            $pet->spendTime(\mt_rand(45, 60));
            $this->inventoryService->loseItem('Pointer', $pet->getOwner(), 1);
            $this->inventoryService->loseItem('Finite State Machine', $pet->getOwner(), 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::COMPUTER ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' upgraded a Finite State Machine into a Regex.', '');
            $this->inventoryService->petCollectsItem('Regex', $pet, $pet->getName() . ' build this from a Finite State Machine.', $activityLog);
            return $activityLog;
        }
        else
        {
            $pet->spendTime(\mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::COMPUTER ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' wanted to create a Regex, but all the documentation they found online was too old.', 'icons/activity-logs/confused');
        }
    }

    private function createCompiler(Pet $pet): PetActivityLog
    {
        $roll = \mt_rand(1, 20 + $pet->getIntelligence() + $pet->getComputer());
        if($roll <= 2)
        {
            $pet->spendTime(\mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::COMPUTER ]);

            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), 1);
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to bootstrap a Compiler, but accidentally de-allocated a String, leaving a useless Pointer behind :(', 'icons/activity-logs/null');
                $this->inventoryService->petCollectsItem('Pointer', $pet, $pet->getName() . ' accidentally de-allocated a String; all that remains is this Pointer.', $activityLog);
                return $activityLog;
            }
            else
            {
                $this->inventoryService->loseItem('Hash Table', $pet->getOwner(), 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to bootstrap a Compiler, but accidentally caused a runaway hash collision, and lost their Hash Table :(', 'icons/activity-logs/null');
            }
        }
        else if($roll >= 16)
        {
            $pet->spendTime(\mt_rand(45, 60));
            $this->inventoryService->loseItem('Hash Table', $pet->getOwner(), 1);
            $this->inventoryService->loseItem('Finite State Machine', $pet->getOwner(), 1);
            $this->inventoryService->loseItem('String', $pet->getOwner(), 1);
            $this->petService->gainExp($pet, 2, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::COMPUTER ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' bootstrapped a Compiler.', '');
            $this->inventoryService->petCollectsItem('Compiler', $pet, $pet->getName() . ' bootstrapped this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $pet->spendTime(\mt_rand(30, 60));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::INTELLIGENCE, PetSkillEnum::COMPUTER ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' started to bootstrap a Compiler, but only got so far.', 'icons/activity-logs/confused');
        }
    }
}