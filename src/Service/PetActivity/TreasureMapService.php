<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\PetSkillEnum;
use App\Enum\UserStatEnum;
use App\Model\PetChanges;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\PetService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;

class TreasureMapService
{
    private $responseService;
    private $inventoryService;
    private $petService;
    private $userStatsRepository;
    private $em;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, PetService $petService,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petService = $petService;
        $this->userStatsRepository = $userStatsRepository;
        $this->em = $em;
    }

    public function doCetguelisTreasureMap(Pet $pet)
    {
        $activityLog = null;
        $changes = new PetChanges($pet);

        $followMapCheck = mt_rand(1, 10 + $pet->getPerception() + $pet->getSkills()->getNature() + $pet->getIntelligence());

        if($followMapCheck < 15)
        {
            $pet->spendTime(mt_rand(30, 90));
            $this->petService->gainExp($pet, 1, [ PetSkillEnum::PERCEPTION, PetSkillEnum::NATURE, PetSkillEnum::INTELLIGENCE ]);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to decipher Cetgueli\'s Treasure Map, but couldn\'t make sense of it.', 'icons/activity-logs/confused');
            $pet->increaseEsteem(-1);

            if(mt_rand(1, 3) === 1)
            {
                $this->responseService->createActivityLog($pet, $pet->getName() . ' put the treasure map down.', 'icons/activity-logs/confused');
                $pet->setTool(null);
            }
        }
        else
        {
            $pet->spendTime(mt_rand(60, 90));
            $this->petService->gainExp($pet, 3, [ PetSkillEnum::PERCEPTION, PetSkillEnum::NATURE, PetSkillEnum::INTELLIGENCE ]);
            $pet->increaseEsteem(5);

            if(mt_rand(1, 5) === 1)
                $prize = 'Outrageously Strongbox';
            else
                $prize = 'Very Strongbox';

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' followed Cetgueli\'s Treasure Map, and found a ' . $prize . '! (Also, the map was lost, because video games.)', 'items/map/cetgueli');

            $this->em->remove($pet->getTool());
            $pet->setTool(null);

            $this->inventoryService->petCollectsItem($prize, $pet, $pet->getName() . ' found this by following Cetgueli\'s Treasure Map!', $activityLog);
        }

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));

        if(mt_rand(1, 5) === 1)
            $this->inventoryService->petAttractsRandomBug($pet);
    }
}
