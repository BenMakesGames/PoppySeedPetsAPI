<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Enum\UserStatEnum;
use App\Functions\GrammarFunctions;
use App\Functions\NumberFunctions;
use App\Model\PetChanges;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\PetService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;

class TreasureMapService
{
    private $responseService;
    private $inventoryService;
    private $userStatsRepository;
    private $em;
    private $petExperienceService;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, UserStatsRepository $userStatsRepository,
        EntityManagerInterface $em, PetExperienceService $petExperienceService
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->userStatsRepository = $userStatsRepository;
        $this->em = $em;
        $this->petExperienceService = $petExperienceService;
    }

    public function doCetguelisTreasureMap(Pet $pet)
    {
        $activityLog = null;
        $changes = new PetChanges($pet);

        $followMapCheck = mt_rand(1, 10 + $pet->getPerception() + $pet->getSkills()->getNature() + $pet->getIntelligence());

        if($followMapCheck < 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 90), PetActivityStatEnum::GATHER, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to decipher Cetgueli\'s Treasure Map, but couldn\'t make sense of it.', 'icons/activity-logs/confused');
            $pet->increaseEsteem(-1);

            if(mt_rand(1, 3) === 1)
            {
                $activityLog->setEntry($activityLog->getEntry() . ' ' . $pet->getName() . ' put the treasure map down.');
                $pet->setTool(null);
            }
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 90), PetActivityStatEnum::GATHER, true);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::NATURE ]);
            $pet->increaseEsteem(5);

            $prize = 'Outrageously Strongbox';

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' followed Cetgueli\'s Treasure Map, and found a ' . $prize . '! (Also, the map was lost, because video games.)', 'items/map/cetgueli');

            $this->em->remove($pet->getTool());
            $pet->setTool(null);

            $this->inventoryService->petCollectsItem($prize, $pet, $pet->getName() . ' found this by following Cetgueli\'s Treasure Map!', $activityLog);
        }

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));

        $activityLog->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY);

        if(mt_rand(1, 5) === 1)
            $this->inventoryService->petAttractsRandomBug($pet);
    }

    public function doGoldIdol(Pet $pet)
    {
        $activityLog = null;
        $changes = new PetChanges($pet);

        $this->petExperienceService->spendTime($pet, mt_rand(30, 45), PetActivityStatEnum::OTHER, null);
        $pet->increaseEsteem(5);

        $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found that Thieving Magpie, and offered it a "Gold" Idol in exchange for something else. The magpie eagerly accepted.', 'items/treasure/magpie-deal')
            ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
        ;

        $this->em->remove($pet->getTool());
        $pet->setTool(null);

        $this->inventoryService->petCollectsItem('Magpie\'s Deal', $pet, $pet->getName() . ' got this from a Thieving Magpie in exchange for a "Gold" Idol!', $activityLog);

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));

        if(mt_rand(1, 20) === 1)
            $this->inventoryService->petAttractsRandomBug($pet);
    }

    public function doKeybladeTower(Pet $pet)
    {
        $changes = new PetChanges($pet);

        $skill = 3 * ($pet->getBrawl() * 2 + $pet->getStamina() * 2 + $pet->getDexterity() + $pet->getStrength() + $pet->getLevel());

        $floor = mt_rand(max(1, ceil($skill / 2)), 20 + $skill);
        $floor = NumberFunctions::constrain($floor, 1, 100);

        $keybladeName = $pet->getTool()->getItem()->getName();

        if($floor === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' took their ' . $keybladeName . ' to the Tower of Trials, but couldn\'t even get past the first floor...', '');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
            $pet
                ->increaseEsteem(-2)
                ->increaseFood(-1)
            ;
        }
        else if($floor < 25)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' took their ' . $keybladeName . ' to the Tower of Trials, but had to retreat after only the ' . GrammarFunctions::ordinal($floor) . ' floor.', '');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
            $pet
                ->increaseFood(-2)
            ;
        }
        else if($floor < 50)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' took their ' . $keybladeName . ' to the Tower of Trials, but got tired and had to quit after the ' . GrammarFunctions::ordinal($floor) . ' floor.', '');
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ]);
            $pet
                ->increaseFood(-3)
            ;
        }
        else if($floor < 75)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' took their ' . $keybladeName . ' to the Tower of Trials, and got as far as the ' . GrammarFunctions::ordinal($floor) . ' floor before they had to quit. (Not bad!)', '');
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ]);
            $pet
                ->increaseFood(-4)
                ->increaseEsteem(2)
            ;
        }
        else if($floor < 100)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' took their ' . $keybladeName . ' to the Tower of Trials, and got all the way to the ' . GrammarFunctions::ordinal($floor) . ' floor, but couldn\'t get any further. (Pretty good, though!)', '');
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::BRAWL ]);
            $pet
                ->increaseFood(-5)
                ->increaseEsteem(3)
            ;
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' took their ' . $keybladeName . ' to the Tower of Trials, and beat the 100th floor! They plunged the keyblade into the pedestal, unlocking the door to the treasure room, and claimed a Tower Chest!', '');
            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::BRAWL ]);
            $pet
                ->increaseFood(-6)
                ->increaseEsteem(5)
                ->increaseSafety(2)
            ;

            $this->inventoryService->petCollectsItem('Tower Chest', $pet, $pet->getName() . ' got this by defeating the 100th floor of the Tower of Trials!', $activityLog);
            $this->em->remove($pet->getTool());
            $pet->setTool(null);
        }

        $activityLog
            ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY + $floor)
            ->setChanges($changes->compare($pet))
        ;

        if(mt_rand(1, 20) === 1)
            $this->inventoryService->petAttractsRandomBug($pet);
    }
}
