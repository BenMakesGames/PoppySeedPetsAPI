<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Functions\ArrayFunctions;
use App\Model\PetChanges;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\PetService;
use App\Service\ResponseService;

class GenericAdventureService
{
    private $responseService;
    private $inventoryService;
    private $petService;
    private $userQuestRepository;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, PetService $petService,
        UserQuestRepository $userQuestRepository
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petService = $petService;
        $this->userQuestRepository = $userQuestRepository;
    }

    public function adventure(Pet $pet): PetActivityLog
    {
        $level = $pet->getLevel();
        $activityLog = null;
        $changes = new PetChanges($pet);

        $this->petService->spendTime($pet, \mt_rand(30, 60), PetActivityStatEnum::OTHER, null);

        $rescuedAFairy = $this->userQuestRepository->findOrCreate($pet->getOwner(), 'Rescued a House Fairy from a Raccoon', null);
        if(!$rescuedAFairy->getValue())
        {
            $rescuedAFairy->setValue((new \DateTimeImmutable())->format('Y-m-d H:i:s'));

            $activityLog = $this->responseService->createActivityLog($pet, 'While ' . $pet->getName() . ' was thinking about what to do, they saw a raccoon carrying a House Fairy in its mouth. The raccoon stared at ' . $pet->getName() . ' for a moment, then dropped the House Fairy and scurried away.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
            ;
            $inventory = $this->inventoryService->petCollectsItem('House Fairy', $pet, 'A startled raccoon dropped this while ' . $pet->getName() . ' was out.', $activityLog);

            $inventory->setLockedToOwner(true);

            $pet->increaseEsteem(4);

            $activityLog->setChanges($changes->compare($pet));

            return $activityLog;
        }

        $possibleRewards = [
            [ 'a ', 'Crooked Stick' ],
            [ 'some ', 'Spicy Peps' ],
            [ mt_rand(2, 5), 'moneys' ],
        ];

        if($level >= 5)
            $possibleRewards[] = [ 'a ', 'Sand Dollar' ];

        if($level >= 10)
        {
            $possibleRewards[] = [ '', 'Iron Ore' ];
            $possibleRewards[] = [ mt_rand(4, 8), 'moneys' ];
        }

        if($level >= 15)
        {
            $possibleRewards[] = [ 'a ', 'Baker\'s Box' ];
            $possibleRewards[] = [ 'a ', 'Fishkebab' ];
        }

        if($level >= 20)
        {
            $possibleRewards[] = [ '', 'Silver Ore' ];

            if($pet->hasMerit(MeritEnum::BEHATTED))
                $possibleRewards[] = [ 'a', 'Tinfoil Hat' ];

            $possibleRewards[] = [ mt_rand(6, 12), 'moneys' ];
        }

        if($level >= 30)
        {
            $possibleRewards[] = [ '', 'Gold Ore' ];
            $possibleRewards[] = [ 'a', 'Fruit Basket' ];
        }

        if($level >= 40)
            $possibleRewards[] = [ mt_rand(10, 20), 'moneys' ];

        $reward = ArrayFunctions::pick_one($possibleRewards);

        if($reward[1] === 'moneys')
            $describeReward = $reward[0] . '~~m~~';
        else
            $describeReward = $reward[0] . $reward[1];

        $event = mt_rand(1, 4);
        if($event === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While ' . $pet->getName() . ' was thinking about what to do, they spotted a bunch of ants carrying ' . $describeReward . '! ' . $pet->getName() . ' took the ' . $reward[1] . ', brushed the ants off, and returned home.', 'items/bug/ant-conga');
            $comment = $pet->getName() . ' stole this from some ants.';
        }
        else if($event === 2)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While ' . $pet->getName() . ' was thinking about what to do, they saw ' . $describeReward . ' floating downstream on a log! ' . $pet->getName() . ' caught up to the log, and took the ' . $reward[1] . '.', '');
            $comment = $pet->getName() . ' found this floating on a log.';
        }
        else if($event === 3)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While ' . $pet->getName() . ' was thinking about what to do, they saw ' . $describeReward . ' poking out of a bag near a dumpster! ' . $pet->getName() . ' took the ' . $reward[1] . ', and returned home.', '');
            $comment = $pet->getName() . ' found this near a dumpster.';
        }
        else //if($event === 4)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While ' . $pet->getName() . ' was thinking about what to do, they saw a raccoon carrying ' . $describeReward . ' in its mouth. The raccoon stared at ' . $pet->getName() . ' for a moment, then dropped the ' . $reward[1] . ' and scurried away.', '');
            $comment = 'A startled raccoon dropped this while ' . $pet->getName() . ' was out.';
        }

        if($reward[1] === 'moneys')
            $pet->getOwner()->increaseMoneys($reward[0]);
        else
            $this->inventoryService->petCollectsItem($reward[1], $pet, $comment, $activityLog);

        $activityLog
            ->setChanges($changes->compare($pet))
            ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
        ;

        return $activityLog;
    }

}