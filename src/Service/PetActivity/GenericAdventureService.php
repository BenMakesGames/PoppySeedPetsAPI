<?php
namespace App\Service\PetActivity;

use App\Entity\PetActivityLog;
use App\Enum\BirdBathBirdEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\ItemRepository;
use App\Repository\MeritRepository;
use App\Repository\SpiceRepository;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\TransactionService;

class GenericAdventureService
{
    private $responseService;
    private $inventoryService;
    private $petExperienceService;
    private $userQuestRepository;
    private $transactionService;
    private $meritRepository;
    private $itemRepository;
    private $squirrel3;
    private $spiceRepository;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, PetExperienceService $petExperienceService,
        UserQuestRepository $userQuestRepository, TransactionService $transactionService, MeritRepository $meritRepository,
        ItemRepository $itemRepository, Squirrel3 $squirrel3, SpiceRepository $spiceRepository
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->userQuestRepository = $userQuestRepository;
        $this->petExperienceService = $petExperienceService;
        $this->transactionService = $transactionService;
        $this->meritRepository = $meritRepository;
        $this->itemRepository = $itemRepository;
        $this->squirrel3 = $squirrel3;
        $this->spiceRepository = $spiceRepository;
    }

    public function adventure(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::OTHER, null);

        if($pet->getHat() && $pet->getHat()->getItem()->getName() === 'Red')
        {
            $pet->getHat()->changeItem($this->itemRepository->findOneByName('William, Shush'));

            $activityLog = $this->responseService->createActivityLog($pet, 'While ' . '%pet:' . $pet->getId() . '.name% was thinking about what to do, some random dude jumped out of nowhere and shot an arrow in %pet:' . $pet->getId() . '.name%\'s Red!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ;

            return $activityLog;
        }

        if($pet->getIsGrandparent() && !$pet->getClaimedGrandparentMerit())
        {
            /** @var string $newMerit */
            $newMerit = $this->squirrel3->rngNextFromArray([
                MeritEnum::NEVER_EMBARRASSED, MeritEnum::EVERLASTING_LOVE, MeritEnum::NOTHING_TO_FEAR
            ]);

            $changes = new PetChanges($pet);

            $pet
                ->addMerit($this->meritRepository->findOneByName($newMerit))
                ->setClaimedGrandparentMerit()
            ;

            switch($newMerit)
            {
                case MeritEnum::NEVER_EMBARRASSED: $pet->increaseEsteem(72); break;
                case MeritEnum::EVERLASTING_LOVE: $pet->increaseLove(72); break;
                case MeritEnum::NOTHING_TO_FEAR: $pet->increaseSafety(72); break;
            }

            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name%, having become a grandparent, has been thinking about their life up until this point, and adopted a new philosophy: ' . $newMerit . '!', '', $changes->compare($pet))
                ->addInterestingness(PetActivityLogInterestingnessEnum::LEVEL_UP)
            ;
        }

        $level = $pet->getLevel();
        $activityLog = null;
        $changes = new PetChanges($pet);

        $rescuedAFairy = $this->userQuestRepository->findOrCreate($pet->getOwner(), 'Rescued a House Fairy from a Raccoon', null);
        if(!$rescuedAFairy->getValue())
        {
            $rescuedAFairy->setValue((new \DateTimeImmutable())->format('Y-m-d H:i:s'));

            $activityLog = $this->responseService->createActivityLog($pet, 'While %pet:' . $pet->getId() . '.name% was thinking about what to do, they saw a raccoon carrying a House Fairy in its mouth. The raccoon stared at %pet:' . $pet->getId() . '.name% for a moment, then dropped the House Fairy and scurried away.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
            ;
            $inventory = $this->inventoryService->petCollectsItem('House Fairy', $pet, 'A startled raccoon dropped this while ' . $pet->getName() . ' was out.', $activityLog);

            $inventory->setLockedToOwner(true);

            $pet->increaseEsteem(4);

            $activityLog->setChanges($changes->compare($pet));

            return $activityLog;
        }

        if($pet->getOwner()->getGreenhouse() && $pet->getOwner()->getGreenhouse()->getHasBirdBath() && !$pet->getOwner()->getGreenhouse()->getVisitingBird() && $this->squirrel3->rngNextInt(1, 20) === 1)
        {
            $bird = BirdBathBirdEnum::getRandomValue($this->squirrel3);

            $pet->getOwner()->getGreenhouse()->setVisitingBird($bird);

            return $this->responseService->createActivityLog($pet, 'While ' . '%pet:' . $pet->getId() . '.name% was thinking about what to do, they saw a huge ' . $bird . ' swoop into the Greenhouse and land on the Bird Bath!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ;
        }

        if($level >= 10 && $this->squirrel3->rngNextInt(1, 130) === 1)
            $reward = [ 'a ', 'Secret Seashell' ];
        else
        {
            $possibleRewards = [
                [ 'a ', 'Crooked Stick' ],
                [ 'some ', 'Spicy Peps' ],
                [ 'some ', 'Mixed Nuts' ],
            ];

            if($level >= 5)
            {
                $possibleRewards[] = [ 'a ', 'Sand Dollar' ];
                $possibleRewards[] = [ '', 'Ants on a Log' ];
            }

            if($level >= 10)
            {
                $possibleRewards[] = [ '', 'Iron Ore' ];
                $possibleRewards[] = [ 'a packet of ', 'Instant Ramen (Dry)' ];
            }

            if($level >= 15)
            {
                $possibleRewards[] = [ 'a ', 'Jar of Fireflies' ];
                $possibleRewards[] = [ 'a ', 'Fishkebab' ];
            }

            if($level >= 20)
            {
                $possibleRewards[] = [ '', 'Silver Ore' ];

                if($pet->hasMerit(MeritEnum::BEHATTED))
                    $possibleRewards[] = [ 'a ', 'Tinfoil Hat' ];
                else
                    $possibleRewards[] = [ $this->squirrel3->rngNextInt(4, 8), 'moneys' ];
            }

            if($level >= 25)
            {
                $possibleRewards[] = [ '', 'Gold Ore' ];
                $possibleRewards[] = [ 'a ', 'Fruit Basket' ];
            }

            if($level >= 30)
            {
                $possibleRewards[] = [ 'a chunk of ', 'Dark Matter' ];

                if($this->squirrel3->rngNextInt(1, 20) === 1)
                    $possibleRewards[] = [ 'a ', 'Species Transmigration Serum' ];
                else
                    $possibleRewards[] = [ $this->squirrel3->rngNextInt(8, 12), 'moneys' ];
            }

            $reward = $this->squirrel3->rngNextFromArray($possibleRewards);
        }

        if($reward[1] === 'moneys')
            $describeReward = $reward[0] . '~~m~~';
        else
            $describeReward = $reward[0] . $reward[1];

        $event = $this->squirrel3->rngNextInt(1, 4);
        if($event === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While ' . '%pet:' . $pet->getId() . '.name% was thinking about what to do, they spotted a bunch of ants carrying ' . $describeReward . '! %pet:' . $pet->getId() . '.name% took the ' . $reward[1] . ', brushed the ants off, and returned home.', 'items/bug/ant-conga');
            $comment = $pet->getName() . ' stole this from some ants.';
        }
        else if($event === 2)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While ' . '%pet:' . $pet->getId() . '.name% was thinking about what to do, they saw ' . $describeReward . ' floating downstream on a log! %pet:' . $pet->getId() . '.name% caught up to the log, and took the ' . $reward[1] . '.', '');
            $comment = $pet->getName() . ' found this floating on a log.';
        }
        else if($event === 3)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While ' . '%pet:' . $pet->getId() . '.name% was thinking about what to do, they saw ' . $describeReward . ' poking out of a bag near a dumpster! %pet:' . $pet->getId() . '.name% took the ' . $reward[1] . ', and returned home.', '');
            $comment = $pet->getName() . ' found this near a dumpster.';
        }
        else //if($event === 4)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While ' . '%pet:' . $pet->getId() . '.name% was thinking about what to do, they saw a raccoon carrying ' . $describeReward . ' in its mouth. The raccoon stared at %pet:' . $pet->getId() . '.name% for a moment, then dropped the ' . $reward[1] . ' and scurried away.', '');
            $comment = 'A startled raccoon dropped this while ' . $pet->getName() . ' was out.';
        }

        if($reward[1] === 'moneys')
        {
            $this->transactionService->getMoney($pet->getOwner(), $reward[0], $comment);
        }
        else
        {
            if($reward[1] === 'Fishkebab')
            {
                $spice = $this->spiceRepository->findOneByName($this->squirrel3->rngNextFromArray([
                    'Spicy', 'with Ketchup', 'Cheesy', 'Fishy', 'Ducky', 'Onion\'d',
                ]));
            }
            else
                $spice = null;

            $this->inventoryService->petCollectsEnhancedItem($reward[1], null, $spice, $pet, $comment, $activityLog);
        }

        $activityLog
            ->setChanges($changes->compare($pet))
            ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
        ;

        return $activityLog;
    }
}
