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
use App\Service\Squirrel3;

class InnerSanctumService
{
    private $petExperienceService;
    private $responseService;
    private $squirrel3;

    public function __construct(
        PetExperienceService $petExperienceService, ResponseService $responseService, Squirrel3 $squirrel3
    )
    {
        $this->petExperienceService = $petExperienceService;
        $this->responseService = $responseService;
        $this->squirrel3 = $squirrel3;
    }

    public function doAdventure(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $member = $pet->getGuildMembership();

        $activity = $this->squirrel3->rngNextInt(1, $member->getLevel());
        $activity = NumberFunctions::clamp($activity, 1, 4);

        switch($activity)
        {
            case 1:
            case 2:
            case 3:
            case 4:
        }

        $member->increaseReputation();

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        $activityLog = $this->responseService->createActivityLog($pet, '', '');

        return $activityLog;
    }
}
