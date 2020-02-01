<?php
namespace App\Service\PetActivity\Crafting;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Model\PetChanges;
use App\Repository\ItemRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;

class ProgrammingService
{
    private $responseService;
    private $inventoryService;
    private $itemRepository;
    private $petExperienceService;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, ItemRepository $itemRepository,
        PetExperienceService $petExperienceService
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->itemRepository = $itemRepository;
        $this->petExperienceService = $petExperienceService;
    }

    public function getCraftingPossibilities(Pet $pet): array
    {
        $quantities = $this->itemRepository->getInventoryQuantities($pet->getOwner(), LocationEnum::HOME, 'name');

        $possibilities = [];

        if(array_key_exists('Pointer', $quantities))
        {
            $possibilities[] = [ $this, 'createStringFromPointer' ];

            if(array_key_exists('Finite State Machine', $quantities))
                $possibilities[] = [ $this, 'createRegex' ];

            if(array_key_exists('NUL', $quantities) && array_key_exists('Plastic Fishing Rod', $quantities))
                $possibilities[] = [ $this, 'createPhishingRod' ];
        }

        if(array_key_exists('Regex', $quantities) && array_key_exists('XOR', $quantities) && array_key_exists('String', $quantities))
            $possibilities[] = [ $this, 'createL33tH4xx0r' ];

        if(array_key_exists('Hash Table', $quantities))
        {
            if(array_key_exists('Finite State Machine', $quantities) && array_key_exists('String', $quantities))
                $possibilities[] = [ $this, 'createCompiler' ];

            if(array_key_exists('Elvish Magnifying Glass', $quantities))
                $possibilities[] = [ $this, 'createRijndael' ];
        }

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
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getScience());
        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->inventoryService->loseItem('Pointer', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to dereference a String from a Pointer, but encountered a null exception :(', 'icons/activity-logs/null');
            $this->inventoryService->petCollectsItem('NUL', $pet, $pet->getName() . ' encountered a null exception when trying to dereference a pointer.', $activityLog);
            return $activityLog;
        }
        else if($roll >= 10)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->inventoryService->loseItem('Pointer', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' dereferenced a String from a Pointer.', 'items/resource/string');
            $this->inventoryService->petCollectsItem('String', $pet, $pet->getName() . ' dereferenced this from a Pointer.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to dereference a Pointer, but couldn\'t figure out all the syntax errors.', 'icons/activity-logs/confused');
        }
    }

    private function createRegex(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getScience());
        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);

            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('Pointer', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a Regex, but mis-scoped a Pointer :(', 'icons/activity-logs/null');
            }
            else
            {
                $this->inventoryService->loseItem('Finite State Machine', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a Regex, but lost a Finite State Machine to a stack overflow :(', 'icons/activity-logs/null');
            }
        }
        else if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->inventoryService->loseItem('Pointer', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Finite State Machine', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' upgraded a Finite State Machine into a Regex.', '');
            $this->inventoryService->petCollectsItem('Regex', $pet, $pet->getName() . ' build this from a Finite State Machine.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' started to implement a Regex, but it was taking forever. ' . $pet->getName() . ' saved and quit for now.', 'icons/activity-logs/confused');
        }
    }

    private function createCompiler(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getScience());
        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);

            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to bootstrap a Compiler, but accidentally de-allocated a String, leaving a useless Pointer behind :(', 'icons/activity-logs/null');
                $this->inventoryService->petCollectsItem('Pointer', $pet, $pet->getName() . ' accidentally de-allocated a String; all that remains is this Pointer.', $activityLog);
                return $activityLog;
            }
            else
            {
                $this->inventoryService->loseItem('Hash Table', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to bootstrap a Compiler, but accidentally caused a runaway hash collision, and lost their Hash Table :(', 'icons/activity-logs/null');
            }
        }
        else if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->inventoryService->loseItem('Hash Table', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Finite State Machine', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' bootstrapped a Compiler.', '');
            $this->inventoryService->petCollectsItem('Compiler', $pet, $pet->getName() . ' bootstrapped this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' started to bootstrap a Compiler, but only got so far.', 'icons/activity-logs/confused');
        }
    }

    private function createRijndael(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getScience());

        if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->inventoryService->loseItem('Hash Table', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Elvish Magnifying Glass', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' implemented Rijndael.', '');
            $this->inventoryService->petCollectsItem('Rijndael', $pet, $pet->getName() . ' implemented this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' started to implement Rijndael, but had trouble finding good documentation. ' . $pet->getName() . ' saved and quit for now.', 'icons/activity-logs/confused');
        }
    }

    private function createL33tH4xx0r(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getScience());
        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);

            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to become a l33t h4xx0r, but accidentally de-allocated a String, leaving a useless Pointer behind :(', 'icons/activity-logs/null');
                $this->inventoryService->petCollectsItem('Pointer', $pet, $pet->getName() . ' accidentally de-allocated a String; all that remains is this Pointer.', $activityLog);
                return $activityLog;
            }
            else
            {
                $this->inventoryService->loseItem('XOR', $pet->getOwner(), LocationEnum::HOME, 1);
                return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to become a l33t h4xx0r, but confused an XOR for an OR; the XOR was lost forever :(', 'icons/activity-logs/null');
            }
        }
        else if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->inventoryService->loseItem('Regex', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('XOR', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' became a l33t h4xx0r.', '');
            $this->inventoryService->petCollectsItem('l33t h4xx0r', $pet, $pet->getName() . ' made this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' wanted to become a l33t h4xx0r, but didn\'t have the right stuff.', 'icons/activity-logs/confused');
        }
    }

    private function createPhishingRod(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getScience());
        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);

            $this->inventoryService->loseItem('Pointer', $pet->getOwner(), LocationEnum::HOME, 1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to create a Phishing Rod, but lost their Pointer to garbage collection :(', 'icons/activity-logs/null');
            return $activityLog;
        }
        else if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->inventoryService->loseItem('Plastic Fishing Rod', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Pointer', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('NUL', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(1);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a Phishing Rod.', '');
            $this->inventoryService->petCollectsItem('Phishing Rod', $pet, $pet->getName() . ' made this.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' considered making a Phishing Rod, but ended up boondoggling.', 'icons/activity-logs/confused');
        }
    }
}
