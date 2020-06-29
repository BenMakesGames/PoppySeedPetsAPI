<?php
namespace App\Service\PetActivity\Guild;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Functions\GrammarFunctions;
use App\Functions\NumberFunctions;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;

class GizubisGardenService
{
    private $petExperienceService;
    private $responseService;
    private $inventoryService;

    public function __construct(
        PetExperienceService $petExperienceService, ResponseService $responseService, InventoryService $inventoryService
    )
    {
        $this->petExperienceService = $petExperienceService;
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
    }

    public function doAdventure(Pet $pet): PetActivityLog
    {
        $member = $pet->getGuildMembership();

        $activity = mt_rand(1, $member->getTitle());
        $activity = NumberFunctions::constrain($activity, 1, 3);

        switch($activity)
        {
            case 1: return $this->doRandomSeedlingAdventure($pet);
            case 2: return $this->doWaterTreeOfLife($pet);
            case 3: return $this->doCook($pet);

            default:
                throw new \Exception('Ben failed to code Gizubi\'s Garden activity #' . $activity . '! Agk!');
        }
    }

    private function doRandomSeedlingAdventure(Pet $pet)
    {
        $member = $pet->getGuildMembership();

        switch(mt_rand(1, 3))
        {
            case 1:
                $message = $pet->getName() . ' helped one of their seniors tend to ' . $member->getGuild()->getName() . ' gardens.';
                $skill = PetSkillEnum::NATURE;
                break;
            case 2:
                $message = $pet->getName() . ' assisted one of ' . $member->getGuild()->getName() . '\'s chefs for a feast.';
                $skill = PetSkillEnum::CRAFTS;
                break;
            case 3:
                $message = $pet->getName() . ' participated in an impromptu ' . $member->getGuild()->getName() . ' jam session.';
                $skill = PetSkillEnum::MUSIC;
                break;
            default:
                throw new \Exception('Ben poorly-coded a switch statement in a Gizubi\'s Garden guild activity!');
        }

        $member->increaseReputation();

        $this->petExperienceService->gainExp($pet, 1, [ $skill ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::PROTOCOL_7, true);

        return $this->responseService->createActivityLog($pet, $message, '');
    }

    private function doWaterTreeOfLife(Pet $pet)
    {
        $roll = mt_rand(1, 20 + $pet->getNature() + $pet->getDexterity() + $pet->getPerception());

        if($roll === 1)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::GATHER, false);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went to water the Tree of Life for Gizubi\'s Garden, but tripped and spilled the sacred water!', '');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            $pet->increaseEsteem(-mt_rand(2, 4));

            return $activityLog;
        }
        else if($roll >= 13)
        {
            $loot = ArrayFunctions::pick_one([
                'Red', 'Crooked Stick', 'Apricot', 'Orange', 'Naner', 'Pamplemousse'
            ]);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GATHER, true);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            $activityLog = $this->responseService->createActivityLog($pet, 'While watering the Tree of Life for Gizubi\'s Garden, ' . $pet->getName() . ' found ' . GrammarFunctions::indefiniteArticle($loot) . ' ' . $loot . '.', '');
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' found this while watering the Tree of Life for Gizubi\'s Garden.', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::GATHER, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' watered the Tree of Life for Gizubi\'s Garden.', '');
        }
    }

    private function doCook(Pet $pet)
    {
        $roll = mt_rand(1, 20 + $pet->getCrafts() + $pet->getDexterity() + $pet->getIntelligence());

        if(mt_rand(1, 2) === 1)
        {
            $loot = ArrayFunctions::pick_one([
                'Brownie', 'Laufabrauð', 'Orange Fish', 'Potato-mushroom Stuffed Onion',
                'Pumpkin Bread', 'Smashed Potatoes', 'Super-simple Spaghet', 'Tomato Soup'
            ]);

            $cooking = 'cooking';
            $cook = 'cook';
            $howRuined = 'burned';
        }
        else
        {
            $loot = ArrayFunctions::pick_one([
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
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to help ' . $cook . ' for a feast for Gizubi\'s Garden, but ' . $howRuined . ' the ' . $loot . '! :(', '');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-mt_rand(2, 4));

            return $activityLog;
        }
        else if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, false);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' helped ' . $cook . ' for a feast for Gizubi\'s Garden. They made ' . $loot . '; everyone liked it, and there was enough left over that ' . $pet->getName() . ' got to take some home!', '');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(mt_rand(2, 4));

            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' made this while ' . $cooking . ' for a feast for Gizubi\'s Garden!', $activityLog);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' helped ' . $cook . ' for a feast for Gizubi\'s Garden. They made ' . $loot . '; everyone liked it!', '');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(mt_rand(2, 4));

            return $activityLog;
        }
    }
}
