<?php
namespace App\Service;

use App\Entity\Pet;

class GatheringService
{
    private $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    public function adventure(Pet $pet)
    {
        $maxSkill = 10 + $pet->getSkills()->getPerception() + $pet->getSkills()->getNature() - $pet->getWhack() - $pet->getJunk();

        if($maxSkill > 12) $maxSkill = 12;

        $roll = mt_rand(1, $maxSkill);

        switch($roll)
        {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
                $this->foundNothing($pet, $roll);
                break;
            case 6:
            case 7:
                $this->foundBerryBush($pet);
                break;
            case 8:
                $this->foundAbandonedGarden($pet);
                break;
            case 9:
            case 10:
                $this->foundHollowLog($pet);
                break;
            case 11:
                $this->foundNothing($pet, $roll);
                break;
            case 12:
                $this->foundBirdNest($pet);
                break;
        }
    }
}