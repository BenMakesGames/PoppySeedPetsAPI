<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\BirdBathBirdEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Functions\ActivityHelpers;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\DragonRepository;
use App\Repository\EnchantmentRepository;
use App\Repository\ItemRepository;
use App\Repository\MeritRepository;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\SpiceRepository;
use App\Repository\UserQuestRepository;
use App\Service\DragonHostageService;
use App\Service\HattierService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\TransactionService;
use App\Service\WeatherService;
use Doctrine\ORM\EntityManagerInterface;

class GenericAdventureService
{
    private $responseService;
    private $inventoryService;
    private $petExperienceService;
    private $userQuestRepository;
    private $transactionService;
    private $meritRepository;
    private $itemRepository;
    private $spiceRepository;
    private IRandom $squirrel3;
    private WeatherService $weatherService;
    private EnchantmentRepository $enchantmentRepository;
    private HattierService $hattierService;
    private UserBirthdayService $userBirthdayService;
    private DragonRepository $dragonRepository;
    private DragonHostageService $dragonHostageService;
    private EntityManagerInterface $em;
    private PetActivityLogTagRepository $petActivityLogTagRepository;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, PetExperienceService $petExperienceService,
        UserQuestRepository $userQuestRepository, TransactionService $transactionService, MeritRepository $meritRepository,
        ItemRepository $itemRepository, Squirrel3 $squirrel3, SpiceRepository $spiceRepository,
        WeatherService $weatherService, EnchantmentRepository $enchantmentRepository, HattierService $hattierService,
        UserBirthdayService $userBirthdayService, DragonRepository $dragonRepository, DragonHostageService $dragonHostageService,
        EntityManagerInterface $em, PetActivityLogTagRepository $petActivityLogTagRepository
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
        $this->weatherService = $weatherService;
        $this->enchantmentRepository = $enchantmentRepository;
        $this->hattierService = $hattierService;
        $this->userBirthdayService = $userBirthdayService;
        $this->dragonRepository = $dragonRepository;
        $this->dragonHostageService = $dragonHostageService;
        $this->em = $em;
        $this->petActivityLogTagRepository = $petActivityLogTagRepository;
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

        // check for birthday event
        $birthdayEvent = $this->userBirthdayService->doBirthday($petWithSkills);
        if($birthdayEvent)
            return $birthdayEvent;

        $level = $pet->getLevel();
        $activityLog = null;
        $changes = new PetChanges($pet);

        $rescuedAFairy = $this->userQuestRepository->findOrCreate($pet->getOwner(), 'Rescued a House Fairy from a Raccoon', null);
        if(!$rescuedAFairy->getValue())
        {
            $rescuedAFairy->setValue((new \DateTimeImmutable())->format('Y-m-d H:i:s'));

            $activityLog = $this->responseService->createActivityLog($pet, 'While %pet:' . $pet->getId() . '.name% was thinking about what to do, they saw a raccoon carrying a House Fairy in its mouth. The raccoon stared at %pet:' . $pet->getId() . '.name% for a moment, then dropped the House Fairy and scurried away.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fae-kind' ]))
            ;
            $inventory = $this->inventoryService->petCollectsItem('House Fairy', $pet, 'A startled raccoon dropped this while ' . $pet->getName() . ' was out.', $activityLog);

            $inventory->setLockedToOwner(true);

            $pet->increaseEsteem(4);

            $activityLog->setChanges($changes->compare($pet));

            return $activityLog;
        }

        if($pet->hasMerit(MeritEnum::BEHATTED))
        {
            // party!
            $activityLog = $this->maybeHaveBirthdayCelebrated($pet);

            if($activityLog)
                return $activityLog;

            // if it's raining, and a pet is wearing a hat...
            if($pet->getHat() && $this->weatherService->getWeather(new \DateTimeImmutable(), $pet)->getRainfall() > 0)
            {
                $activityLog = $this->hattierService->petMaybeUnlockAura(
                    $pet,
                    'Rainy',
                    'Immediately after stepping outside, %pet:' . $pet->getId() . '.name% was drenched with rainwater that fell from the leaves of an enormous tree! Their ' . $pet->getHat()->getItem()->getName() . ' became waterlogged, and a puddle formed at their feet...',
                    'Immediately after stepping outside, %pet:' . $pet->getId() . '.name% was drenched with rainwater that fell from the leaves of an enormous tree! A puddle formed at their feet...',
                    ActivityHelpers::PetName($pet) . '\'s ' . $pet->getHat()->getItem()->getName() . ' got wet in the rain, and a puddle formed at their feet...'
                );

                if($activityLog)
                    return $activityLog;
            }
        }

        if($pet->getOwner()->getGreenhouse() && $pet->getOwner()->getGreenhouse()->getHasBirdBath() && !$pet->getOwner()->getGreenhouse()->getVisitingBird() && $this->squirrel3->rngNextInt(1, 20) === 1)
        {
            $bird = BirdBathBirdEnum::getRandomValue($this->squirrel3);

            $pet->getOwner()->getGreenhouse()->setVisitingBird($bird);

            return $this->responseService->createActivityLog($pet, 'While ' . '%pet:' . $pet->getId() . '.name% was thinking about what to do, they saw a huge ' . $bird . ' swoop into the Greenhouse and land on the Bird Bath!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Greenhouse' ]))
            ;
        }

        if($pet->getOwner()->getUnlockedDragonDen() && $this->squirrel3->rngNextInt(1, 20) === 1)
        {
            $dragon = $this->dragonRepository->findAdult($pet->getOwner());

            if($dragon && !$dragon->getHostage())
            {
                $hostage = $this->dragonHostageService->generateHostage();

                $this->em->persist($hostage);

                $dragon->setHostage($hostage);
            }
        }

        if($level >= 10 && $this->squirrel3->rngNextInt(1, 130) === 1)
            $reward = [ 'a ', 'Secret Seashell' ];
        else
        {
            $possibleRewards = [
                [ 'a ', 'Crooked Stick' ],
                [ 'some ', 'Spicy Peps' ],
                [ '', 'Ants on a Log' ],
            ];

            if($level >= 5)
            {
                $possibleRewards[] = [ 'a ', 'Sand Dollar' ];
                $possibleRewards[] = [ 'some ', 'Mixed Nuts' ];
            }

            if($level >= 10)
            {
                $possibleRewards[] = [ 'a ', 'Fishkebab' ];
                $possibleRewards[] = [ 'a packet of ', 'Instant Ramen (Dry)' ];
            }

            if($level >= 15)
            {
                $possibleRewards[] = [ 'a ', 'Jar of Fireflies' ];
                $possibleRewards[] = [ '', 'Iron Ore' ];
                //$possibleRewards[] = [ 'some ', 'Variety' ];
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
            $activityLog = $this->responseService->createActivityLog($pet, 'While ' . '%pet:' . $pet->getId() . '.name% was thinking about what to do, they spotted a bunch of ants carrying ' . $describeReward . '! %pet:' . $pet->getId() . '.name% took the ' . $reward[1] . ', brushed the ants off, and returned home.', 'items/bug/ant-conga')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering' ]))
            ;
            $comment = $pet->getName() . ' stole this from some ants.';
        }
        else if($event === 2)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While ' . '%pet:' . $pet->getId() . '.name% was thinking about what to do, they saw ' . $describeReward . ' floating downstream on a log! %pet:' . $pet->getId() . '.name% caught up to the log, and took the ' . $reward[1] . '.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering' ]))
            ;
            $comment = $pet->getName() . ' found this floating on a log.';
        }
        else if($event === 3)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While ' . '%pet:' . $pet->getId() . '.name% was thinking about what to do, they saw ' . $describeReward . ' poking out of a bag near a dumpster! %pet:' . $pet->getId() . '.name% took the ' . $reward[1] . ', and returned home.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Dumpster-diving' ]))
            ;
            $comment = $pet->getName() . ' found this near a dumpster.';
        }
        else //if($event === 4)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While ' . '%pet:' . $pet->getId() . '.name% was thinking about what to do, they saw a raccoon carrying ' . $describeReward . ' in its mouth. The raccoon stared at %pet:' . $pet->getId() . '.name% for a moment, then dropped the ' . $reward[1] . ' and scurried away.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Gathering' ]))
            ;
            $comment = 'A startled raccoon dropped this while ' . $pet->getName() . ' was out.';
        }

        if($reward[1] === 'moneys')
        {
            $this->transactionService->getMoney($pet->getOwner(), $reward[0], $comment);

            $activityLog->addTags($this->petActivityLogTagRepository->findByNames([ 'Moneys' ]));
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

    private function maybeHaveBirthdayCelebrated(Pet $pet): ?PetActivityLog
    {
        if($pet->getBirthDate() >= (new \DateTimeImmutable())->modify('-372 days'))
            return null;

        $partyEnchantment = $this->enchantmentRepository->findOneByName('Party');

        if($this->hattierService->userHasUnlocked($pet->getOwner(), $partyEnchantment))
            return null;

        $givenAHat = '';

        if(!$pet->getHat())
        {
            $givenAHat = ', and a Paper Boat was placed on their head';
            $paperHat = $this->inventoryService->petCollectsItem('Paper Boat', $pet, $pet->getName() . ' received this for their birthday from a Tell Samarzhoustian representative.', null)
                ->setLocation(LocationEnum::WARDROBE)
            ;

            $pet->setHat($paperHat);
        }

        $message = 'While walking along a riverbank, ' . ActivityHelpers::PetName($pet) . ' was showered with confetti' . $givenAHat . '! A fish (apparently a representative from Tell Samarazhoustia) wished them a happy birthday... it\'s a little late, but still nice...? It would have been nicer if the fish didn\'t also remind ' . ActivityHelpers::PetName($pet) . ' to visit the Trader often...';

        $activityLog = $this->hattierService->petMaybeUnlockAura(
            $pet,
            $partyEnchantment,
            $message,
            $message,
            ActivityHelpers::PetName($pet) . '\'s got so much confetti on them, they were finding bits of confetti on their body all day...'
        );

        $activityLog->addTags($this->petActivityLogTagRepository->findByNames([ 'Special Event', 'Birthday' ]));

        return $activityLog;
    }
}
