<?php
namespace App\Service\PetActivity\Guild;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\NumberFunctions;
use App\Service\PetExperienceService;
use App\Service\ResponseService;

class LightAndShadowService
{
    private $petExperienceService;
    private $responseService;

    public function __construct(PetExperienceService $petExperienceService, ResponseService $responseService)
    {
        $this->petExperienceService = $petExperienceService;
        $this->responseService = $responseService;
    }

    public function doAdventure(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        $activity = mt_rand(1, $member->getLevel());
        $activity = NumberFunctions::constrain($activity, 1, 4);

        switch($activity)
        {
            case 1:
            case 2:
            case 3:
            case 4:
        }

        $member->increaseReputation();

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GROUP_ACTIVITY, false);

        $activityLog = $this->responseService->createActivityLog($pet, '', '');

        return $activityLog;
    }
}
