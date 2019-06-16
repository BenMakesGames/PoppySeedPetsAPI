<?php
namespace App\Service;

use App\Entity\Pet;
use function App\Functions\array_pick_one;

class FishingService
{
    private $activityLogService;
    private $inventoryService;

    public function __construct(
        ActivityLogService $activityLogService, InventoryService $inventoryService
    )
    {
        $this->activityLogService = $activityLogService;
        $this->inventoryService = $inventoryService;
    }

    public function adventure(Pet $pet)
    {
        $maxSkill = 5 + $pet->getSkills()->getDexterity() + $pet->getSkills()->getNature() - $pet->getWhack();

        if($maxSkill > 7) $maxSkill = 7;

        $roll = \mt_rand(1, $maxSkill);

        $exp = 0;

        switch($roll)
        {
            case 1:
            case 2:
                $exp = $this->fishedSmallLake($pet);
                break;
            case 3:
                $exp = $this->fishedUnderBridge($pet);
                break;
            case 4:
            case 5:
            case 6:
                $exp = $this->fishedRoadsideCreek($pet);
                break;
            case 7:
                $exp = $this->fishedWaterfallBasin($pet);
                break;
        }

        $this->gainExp($pet, $exp);
    }

    private function gainExp(Pet $pet, array $exp)
    {
        if($exp['exp'] === 0) return;

        $pet->increaseExperience($exp['exp']);
        while($pet->getExperience() >= $pet->getExperienceToLevel())
        {
            $pet->decreaseExperience($pet->getExperienceToLevel());
            $stat = array_pick_one($exp['stats']);
            $pet->getSkills()->increaseStat($stat);
        }
    }

    private function fishedSmallLake(Pet $pet): array
    {
        if(\mt_rand(1, 5) === 1)
        {
            $this->activityLogService->createActivityLog($pet, $pet->getName() . ' went fishing at The Small Lake, but nothing was biting.');
            return [ 'exp' => 1, 'stats' => [ 'dexterity', 'nature' ] ];
        }

        if(\mt_rand(1, 10) < 5 + $pet->getSkills()->getDexterity() + $pet->getSkills()->getNature() + $pet->getSkills()->getPerception())
        {
            $this->inventoryService->giveCopyOfItem('Fish', $pet->getOwner(), $pet->getOwner(), 'From a Mini Minnow that ' . $pet->getName() . ' fished at The Small Lake.');
            $this->activityLogService->createActivityLog($pet, $pet->getName() . ' went fishing at The Small Lake, and caught a Mini Minnow.');

            return ['exp' => 1, 'stats' => ['dexterity', 'nature', 'perception']];
        }
        else
        {
            $this->activityLogService->createActivityLog($pet, $pet->getName() . ' went fishing at The Small Lake, and almost caught a Mini Minnow, but it got away.');

            return ['exp' => 1, 'stats' => ['dexterity', 'nature', 'perception']];
        }
    }
}