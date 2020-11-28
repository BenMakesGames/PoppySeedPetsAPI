<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\ItemRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;

class PetSummonedAwayService
{
    private $responseService;
    private $inventoryService;
    private $petExperienceService;
    private $itemRepository;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService,
        PetExperienceService $petExperienceService, ItemRepository $itemRepository
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
        $this->itemRepository = $itemRepository;
    }

    public function adventure(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        /** @var PetActivityLog $activityLog */
        $activityLog = null;
        $changes = new PetChanges($pet);

        $pet->increaseSafety(-mt_rand(2, 4));

        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::OTHER, null);

        switch(mt_rand(1, 4))
        {
            case 1:
                $activityLog = $this->doSummonedToFight($petWithSkills);
                break;
            case 2:
                $activityLog = $this->doSummonedToCleanAndHost($petWithSkills);
                break;
            case 3:
                $activityLog = $this->doSummonedToAssistWithRitual($petWithSkills);
                break;
            case 4:
                $activityLog = $this->doSummonedToAssistWithGathering($petWithSkills);
                break;
        }

        if($activityLog)
        {
            $activityLog
                ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
                ->setChanges($changes->compare($pet))
            ;
        }

        return $activityLog;
    }

    private function doSummonedToFight(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $message = 'While ' . $pet->getName() . ' was thinking about what to do, they were magically summoned! The wizard that summoned them made them fight a monster they\'d never seen before';

        if(mt_rand(1, 3) === 1)
            $message .= '; a creature with ' . (mt_rand(1, 4) * 2) . ' eyes, and a very wrong number of limbs! ';
        else
            $message .= '! ';

        if(mt_rand(1, 2) === 1)
        {
            $message .= $pet->getName() . ' lost the fight, and was returned home!';
            $pet->increaseSafety(-mt_rand(2, 4));
        }
        else
        {
            $message .= $pet->getName() . ' defeated the creature, and was returned home!';
            $pet
                ->increaseSafety(mt_rand(2, 4))
                ->increaseEsteem(mt_rand(2, 4))
            ;
        }

        $this->petExperienceService->gainExp($pet, mt_rand(1, 3), [ PetSkillEnum::BRAWL, PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);

        return $this->responseService->createActivityLog($pet, $message, 'icons/activity-logs/summoned');
    }

    private function doSummonedToCleanAndHost(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        switch(mt_rand(1, 3))
        {
            case 1:
                $activity = 'help clean a mansion the day before a fancy ball';
                $loot = ArrayFunctions::pick_one([ 'Fluff', 'Cobweb' ]);
                $skill = null;
                break;
            case 2:
                $activity = 'serve food to guests at a fancy party in a mansion';
                $loot = ArrayFunctions::pick_one([
                    'Tomato "Sushi"', 'Sweet Roll', 'Slice of Red Pie',
                    'Potato-mushroom Stuffed Onion', 'Meringue', 'Minestrone',
                    'Mixed Nut Brittle', 'Largish Bowl of Smallish Pumpkin Soup',
                    'Grilled Fish', 'Everlasting Syllabub', 'Blueberry Wine',
                    'Fig Wine', 'Bizet Cake', 'Red Wine', 'Zongzi'
                ]);
                $skill = null;
                break;
            case 3:
                $activity = 'play in a band at a fancy party in a mansion';
                $skill = PetSkillEnum::MUSIC;
                $loot = ArrayFunctions::pick_one([
                    'Fungal Clarinet', 'Decorated Flute',
                    'Gold Triangle', 'Melodica'
                ]);
                break;
        }

        $lootItem = $this->itemRepository->findOneByName($loot);
        $message = 'While ' . $pet->getName() . ' was thinking about what to do, they were magically summoned! The wizard that summoned them made them ' . $activity . '. Once the task was completed, ' . $pet->getName() . ' returned home, still holding ' . $lootItem->getNameWithArticle() . '!';

        $activityLog = $this->responseService->createActivityLog($pet, $message, 'icons/activity-logs/summoned');

        $this->inventoryService->petCollectsItem($lootItem, $pet, $pet->getName() . ' was summoned by a wizard to ' . $activity . '; they returned home with this!', $activityLog);

        if($skill !== null)
            $this->petExperienceService->gainExp($pet, mt_rand(1, 2), [ $skill ]);

        return $activityLog;

    }

    private function doSummonedToAssistWithRitual(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        switch(mt_rand(1, 7))
        {
            case 1:
                $activity = 'hold a weird-looking mirror while a laser was focused on it';
                $skill = PetSkillEnum::SCIENCE;
                break;
            case 2:
                $activity = 'mix various chemical together while the wizard watched (from a distance, for some reason...)';
                $skill = PetSkillEnum::SCIENCE;
                break;
            case 3:
                $activity = 'look through almost a hundred photographs of the night sky, looking for bright spots';
                $skill = PetSkillEnum::SCIENCE;
                break;
            case 4:
                $activity = 'perform a very-specific dance while holding some candles';
                $skill = PetSkillEnum::UMBRA;
                break;
            case 5:
                $activity = 'draw a series of symbols in geometric shapes on the ground';
                $skill = PetSkillEnum::UMBRA;
                break;
            case 6:
                $activity = 'keep an eye on a trapped spirit while the wizard tended to other things';
                $skill = PetSkillEnum::UMBRA;
                break;
            case 7:
                $activity = 'stand motionless, "like a gargoyle", and watch out for intruders for nearly one full hour';
                $skill = PetSkillEnum::STEALTH;
                break;
        }

        $message = 'While ' . $pet->getName() . ' was thinking about what to do, they were magically summoned! The wizard that summoned them made them ' . $activity . '. Once the task was completed, ' . $pet->getName() . ' returned home!';

        $activityLog = $this->responseService->createActivityLog($pet, $message, 'icons/activity-logs/summoned');

        $this->petExperienceService->gainExp($pet, mt_rand(1, 2), [ $skill ]);

        return $activityLog;
    }

    private function doSummonedToAssistWithGathering(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $location = null;
        $description = null;
        $skill = null;
        $loot = null;

        switch(mt_rand(1, 2))
        {
            case 1:
                $location = 'a farm';
                $description = 'work a farm';
                $descriptioning = 'working a farm';
                $loot = ArrayFunctions::pick_one([ 'Rice', 'Wheat', 'Egg', 'Creamy Milk' ]);
                break;

            case 2:
                $location = 'a mine';
                [$description, $descriptioning, $loot] = $this->getMiningDescriptionAndLoot();
                break;
        }

        $message = 'While ' . $pet->getName() . ' was thinking about what to do, they were magically summoned to ' . $location . '. The wizard that summoned them made them ' . $description . ' until the spell ended, and they were returned home! (At least they managed to pocket some ' . $loot . ' before the spell ended!)';

        $activityLog = $this->responseService->createActivityLog($pet, $message, 'icons/activity-logs/summoned');

        $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' was summoned by a wizard to do hard labor; while ' . $descriptioning . ', they stole this!', $activityLog);

        return $activityLog;
    }

    private function getMiningDescriptionAndLoot(): array
    {
        $r = mt_rand(1, 10);

        if($r <= 5)
        {
            return [ 'mine for iron', 'mining for iron', 'Iron Ore' ];
        }
        else if($r <= 8)
        {
            return [ 'mine for silver', 'mining for silver', 'Silver Ore' ];
        }
        else
        {
            return [ 'mine for gold', 'mining for gold', 'Gold Ore' ];
        }
    }
}
