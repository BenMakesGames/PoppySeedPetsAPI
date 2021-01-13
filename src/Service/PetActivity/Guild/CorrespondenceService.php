<?php
namespace App\Service\PetActivity\Guild;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\NumberFunctions;
use App\Model\ComputedPetSkills;
use App\Service\PetExperienceService;
use App\Service\ResponseService;

class CorrespondenceService
{
    private $petExperienceService;
    private $responseService;

    public function __construct(PetExperienceService $petExperienceService, ResponseService $responseService)
    {
        $this->petExperienceService = $petExperienceService;
        $this->responseService = $responseService;
    }

    public function doAdventure(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $member = $pet->getGuildMembership();

        $activity = mt_rand(1, $member->getLevel());
        $activity = NumberFunctions::clamp($activity, 1, 4);

        switch($activity)
        {
            case 1:
            case 2:
            case 3:
            case 4:
        }

        $member->increaseReputation();

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        $activityLog = $this->responseService->createActivityLog($pet, '', '');

        return $activityLog;
    }
}
