<?php
namespace App\Service\PetActivity;

use App\Entity\PetActivityLog;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ItemRepository;
use App\Model\ComputedPetSkills;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;

class GizubisGardenService
{
    private PetExperienceService $petExperienceService;
    private ResponseService $responseService;
    private InventoryService $inventoryService;
    private EntityManagerInterface $em;
    private IRandom $squirrel3;

    public function __construct(
        PetExperienceService $petExperienceService, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $squirrel3
    )
    {
        $this->petExperienceService = $petExperienceService;
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->em = $em;
        $this->squirrel3 = $squirrel3;
    }

    public function adventure(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $member = $petWithSkills->getPet()->getGuildMembership();

        if($member->getTitle() === 0)
            return $this->doRandomSeedlingAdventure($petWithSkills);

        switch($this->squirrel3->rngNextInt(1, 3))
        {
            case 1: return $this->doRandomSeedlingAdventure($petWithSkills);
            case 2: return $this->doWaterTreeOfLife($petWithSkills);
            case 3: return $this->doCook($petWithSkills);

            default:
                throw new \Exception('Ben failed to code a Gizubi\'s Garden activity! Agk!');
        }
    }

    private function doRandomSeedlingadventure(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $member = $pet->getGuildMembership();

        switch($this->squirrel3->rngNextInt(1, 3))
        {
            case 1:
                $message = '%pet:' . $pet->getId() . '.name% helped one of their seniors tend to ' . $member->getGuild()->getName() . ' gardens.';
                $skill = PetSkillEnum::NATURE;
                break;
            case 2:
                $message = '%pet:' . $pet->getId() . '.name% assisted one of ' . $member->getGuild()->getName() . '\'s chefs for a feast.';
                $skill = PetSkillEnum::CRAFTS;
                break;
            case 3:
                $message = '%pet:' . $pet->getId() . '.name% participated in an impromptu ' . $member->getGuild()->getName() . ' jam session.';
                $skill = PetSkillEnum::MUSIC;
                break;
            default:
                throw new \Exception('Ben poorly-coded a switch statement in a Gizubi\'s Garden guild activity!');
        }

        $member->increaseReputation();

        $activityLog = $this->responseService->createActivityLog($pet, $message, '');

        $this->petExperienceService->gainExp($pet, 1, [ $skill ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $activityLog;
    }

    private function doWaterTreeOfLife(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getNature()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getPerception()->getTotal());

        if($roll === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went to water the Tree of Life for Gizubi\'s Garden, but tripped and spilled the sacred water!', '');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::GATHER, false);
            $pet->increaseEsteem(-$this->squirrel3->rngNextInt(2, 4));

            return $activityLog;
        }
        else if($roll >= 13)
        {
            $loot = ItemRepository::findOneByName($this->em, $this->squirrel3->rngNextFromArray([
                'Red', 'Crooked Stick', 'Apricot', 'Orange', 'Naner', 'Pamplemousse', 'Avocado'
            ]));

            $activityLog = $this->responseService->createActivityLog($pet, 'While watering the Tree of Life for Gizubi\'s Garden, ' . '%pet:' . $pet->getId() . '.name% found ' . $loot->getNameWithArticle() . '.', '');

            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' found this while watering the Tree of Life for Gizubi\'s Garden.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);

            return $activityLog;
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% watered the Tree of Life for Gizubi\'s Garden.', '');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::GATHER, false);

            return $activityLog;
        }
    }

    private function doCook(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal());

        if($this->squirrel3->rngNextInt(1, 2) === 1)
        {
            $loot = $this->squirrel3->rngNextFromArray([
                'Brownie', 'Laufabrauð', 'Orange Fish', 'Potato-mushroom Stuffed Onion',
                'Pumpkin Bread', 'Smashed Potatoes', 'Super-simple Spaghet', 'Tomato Soup'
            ]);

            $cooking = 'cooking';
            $cook = 'cook';
            $howRuined = 'burned';
        }
        else
        {
            $loot = $this->squirrel3->rngNextFromArray([
                'Blackberry Wine',
                'Blueberry Wine',
                'Red Wine',
                'Kilju',
                'Kumis',
            ]);

            $cooking = 'preparing';
            $cook = 'prepare';
            $howRuined = 'spilled';
        }

        if($roll <= 2)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to help ' . $cook . ' for a feast for Gizubi\'s Garden, but ' . $howRuined . ' the ' . $loot . '! :(', '');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $pet->increaseEsteem(-$this->squirrel3->rngNextInt(2, 4));

            return $activityLog;
        }
        else if($roll >= 14)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% helped ' . $cook . ' for a feast for Gizubi\'s Garden. They made ' . $loot . '; everyone liked it, and there was enough left over that ' . $pet->getName() . ' got to take some home!', '');

            $pet->increaseEsteem($this->squirrel3->rngNextInt(2, 4));
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, false);

            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' made this while ' . $cooking . ' for a feast for Gizubi\'s Garden!', $activityLog);

            return $activityLog;
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% helped ' . $cook . ' for a feast for Gizubi\'s Garden. They made ' . $loot . '; everyone liked it!', '');

            $pet->increaseEsteem($this->squirrel3->rngNextInt(2, 4));
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);

            return $activityLog;
        }
    }
}
