<?php
namespace App\Service;

use App\Entity\Pet;

class FishingService
{
    private $activityLogService;
    private $inventoryService;

    public function __construct(
        ActivityLogService $activityLogService, InventoryService $inventoryService, PetService $petService
    )
    {
        $this->activityLogService = $activityLogService;
        $this->inventoryService = $inventoryService;
        $this->petService = $petService;
    }

    public function adventure(Pet $pet)
    {
        $maxSkill = 5 + $pet->getSkills()->getDexterity() + $pet->getSkills()->getNature() - $pet->getWhack();

        if($maxSkill > 7) $maxSkill = 7;

        $roll = \mt_rand(1, $maxSkill);

        switch($roll)
        {
            case 1:
            case 2:
            case 3:
                $this->fishedSmallLake($pet);
                break;
            case 4:
            case 5:
                $this->fishedUnderBridge($pet);
                break;
            case 6:
                $this->fishedRoadsideCreek($pet);
                break;
            case 7:
                $this->fishedWaterfallBasin($pet);
                break;
        }
    }

    private function nothingBiting(Pet $pet, int $percentChance, string $atLocationName)
    {
        if(\mt_rand(1, 100) <= $percentChance)
        {
            $this->activityLogService->createActivityLog($pet, $pet->getName() . ' went fishing ' . $atLocationName . ', but nothing was biting.');

            $this->petService->gainExp($pet, 1, [ 'dexterity', 'nature' ]);

            $pet->spendTime(mt_rand(45, 60));

            return true;
        }

        return false;
    }

    private function fishedSmallLake(Pet $pet)
    {
        if($this->nothingBiting($pet, 20, 'at a Small Lake')) return;

        if(\mt_rand(1, 10 + $pet->getSkills()->getDexterity() + $pet->getSkills()->getNature() + $pet->getSkills()->getPerception()) >= 5)
        {
            $this->inventoryService->giveCopyOfItem('Fish', $pet->getOwner(), $pet->getOwner(), 'From a Mini Minnow that ' . $pet->getName() . ' fished at a Small Lake.');
            $this->activityLogService->createActivityLog($pet, $pet->getName() . ' went fishing at a Small Lake, and caught a Mini Minnow.');

            $this->petService->gainExp($pet, 1, ['dexterity', 'nature', 'perception']);
        }
        else
        {
            $this->activityLogService->createActivityLog($pet, $pet->getName() . ' went fishing at a Small Lake, and almost caught a Mini Minnow, but it got away.');

            $this->petService->gainExp($pet, 1, ['dexterity', 'nature', 'perception']);
        }

        $pet->spendTime(mt_rand(45, 60));
    }

    private function fishedUnderBridge(Pet $pet)
    {
        if($this->nothingBiting($pet, 15, 'Under a Bridge')) return;

        if(\mt_rand(1, 10 + $pet->getSkills()->getDexterity() + $pet->getSkills()->getNature() + $pet->getSkills()->getStrength()) >= 6)
        {
            $this->inventoryService->giveCopyOfItem('Fish', $pet->getOwner(), $pet->getOwner(), 'From a Muscly Trout that ' . $pet->getName() . ' fished Under a Bridge.');

            if(\mt_rand(1, 20 + $pet->getSkills()->getIntelligence()) >= 15)
                $this->inventoryService->giveCopyOfItem('Scales', $pet->getOwner(), $pet->getOwner(), 'From a Muscly Trout that ' . $pet->getName() . ' fished Under a Bridge.');

            $this->activityLogService->createActivityLog($pet, $pet->getName() . ' went fishing Under a Bridge, and caught a Muscly Trout.');

            $this->petService->gainExp($pet, 1, ['dexterity', 'nature', 'strength']);
        }
        else
        {
            $this->activityLogService->createActivityLog($pet, $pet->getName() . ' went fishing Under a Bridge, and almost caught a Muscly Trout, but it got away.');

            $this->petService->gainExp($pet, 1, ['dexterity', 'nature', 'strength']);
        }

        $pet->spendTime(mt_rand(45, 60));
    }

    private function fishedRoadsideCreek(Pet $pet)
    {
        if($this->nothingBiting($pet, 20, 'at a Roadside Creek')) return;

        if(mt_rand(1, 3) === 1)
        {
            // toad
            if(\mt_rand(1, 10 + $pet->getSkills()->getStamina() + $pet->getSkills()->getDexterity() + $pet->getSkills()->getStrength()) >= 7)
            {
                $this->inventoryService->giveCopyOfItem('Toad Legs', $pet->getOwner(), $pet->getOwner(), 'From a Huge Toad that ' . $pet->getName() . ' fished at a Roadside Creek.');

                if(\mt_rand(1, 20 + $pet->getSkills()->getNature()) >= 15)
                    $this->inventoryService->giveCopyOfItem('Toadstool', $pet->getOwner(), $pet->getOwner(), 'From a Huge Toad that ' . $pet->getName() . ' fished at a Roadside Creek.');

                $this->activityLogService->createActivityLog($pet, $pet->getName() . ' went fishing at a Roadside Creek, and a Huge Toad bit the line! ' . $pet->getName() . ' used all their strength to reel it in!');

                $this->petService->gainExp($pet, 2, [ 'dexterity', 'nature', 'stamina', 'strength' ]);

            }
            else
            {
                $this->activityLogService->createActivityLog($pet, $pet->getName() . ' went fishing at a Roadside Creek, and a Huge Toad bit the line! ' . $pet->getName() . ' tried to reel it in, but it was too strong, and got away.');

                $this->petService->gainExp($pet, 1, ['dexterity', 'nature', 'stamina', 'strength']);
            }

            $pet->spendTime(mt_rand(45, 75));
        }
        else
        {
            // singing fish
            if(\mt_rand(1, 10 + $pet->getSkills()->getDexterity() + $pet->getSkills()->getNature() + $pet->getSkills()->getPerception()) >= 6)
            {
                $this->inventoryService->giveCopyOfItem(mt_rand(1, 2) === 1 ? 'Plastic' : 'Fish', $pet->getOwner(), $pet->getOwner(), 'From a Singing Fish that ' . $pet->getName() . ' fished at a Roadside Creek.');

                if(\mt_rand(1, 20 + $pet->getSkills()->getPerception()) >= 15)
                    $this->inventoryService->giveCopyOfItem('Musical Scales', $pet->getOwner(), $pet->getOwner(), 'From a Singing Fish that ' . $pet->getName() . ' fished at a Roadside Creek.');

                $this->petService->gainExp($pet, 2, ['dexterity', 'nature', 'perception']);
            }
            else
            {
                $this->activityLogService->createActivityLog($pet, $pet->getName() . ' went fishing at a Roadside Creek, and almost caught a Singing Fish, but it got away.');

                $this->petService->gainExp($pet, 1, ['dexterity', 'nature', 'perception']);
            }

            $pet->spendTime(mt_rand(30, 60));
        }
    }

    private function fishedWaterfallBasin(Pet $pet)
    {
        if($this->nothingBiting($pet, 20, 'in a Waterfall Basin')) return;

        if(\mt_rand(1, 5) === 1)
        {
            $this->inventoryService->giveCopyOfItem('Mermaid Egg', $pet->getOwner(), $pet->getOwner(), $pet->getName() . ' was fishing in a Waterfall Basin, and one of these got caught on the line!');
            $this->petService->gainExp($pet, 1, ['nature', 'perception']);

            $pet->spendTime(mt_rand(30, 45));
        }
        else
        {
            if(\mt_rand(1, 10 + $pet->getSkills()->getDexterity() + $pet->getSkills()->getNature() + $pet->getSkills()->getPerception()) >= 7)
            {
                $this->inventoryService->giveCopyOfItem('Fish', $pet->getOwner(), $pet->getOwner(), 'From a Medium Minnow that ' . $pet->getName() . ' fished in a Waterfall Basin.');

                if(\mt_rand(1, 20 + $pet->getSkills()->getNature()) >= 10)
                    $this->inventoryService->giveCopyOfItem('Fish', $pet->getOwner(), $pet->getOwner(), 'From a Medium Minnow that ' . $pet->getName() . ' fished in a Waterfall Basin.');

                $this->activityLogService->createActivityLog($pet, $pet->getName() . ' went fishing in a Waterfall Basin, and caught a Medium Minnow.');

                $this->petService->gainExp($pet, 2, ['dexterity', 'nature', 'perception']);
            }
            else
            {
                $this->activityLogService->createActivityLog($pet, $pet->getName() . ' went fishing in a Waterfall Basin, and almost caught a Medium Minnow, but it got away.');

                $this->petService->gainExp($pet, 1, ['dexterity', 'nature', 'perception']);
            }

            $pet->spendTime(mt_rand(45, 60));
        }
    }
}