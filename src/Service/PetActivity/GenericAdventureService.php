<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\BirdBathBirdEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Functions\ActivityHelpers;
use App\Functions\DragonHelpers;
use App\Functions\ItemRepository;
use App\Functions\MeritRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\UserUnlockedFeatureHelpers;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\DragonRepository;
use App\Repository\EnchantmentRepository;
use App\Repository\SpiceRepository;
use App\Repository\UserQuestRepository;
use App\Service\DragonHostageService;
use App\Service\FieldGuideService;
use App\Service\HattierService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\TransactionService;
use App\Service\WeatherService;
use Doctrine\ORM\EntityManagerInterface;

class GenericAdventureService
{
    private InventoryService $inventoryService;
    private PetExperienceService $petExperienceService;
    private UserQuestRepository $userQuestRepository;
    private TransactionService $transactionService;
    private IRandom $rng;
    private HattierService $hattierService;
    private UserBirthdayService $userBirthdayService;
    private DragonHostageService $dragonHostageService;
    private EntityManagerInterface $em;
    private FieldGuideService $fieldGuideService;

    public function __construct(
        InventoryService $inventoryService,
        PetExperienceService $petExperienceService, UserQuestRepository $userQuestRepository,
        TransactionService $transactionService, IRandom $rng, HattierService $hattierService,
        UserBirthdayService $userBirthdayService, DragonHostageService $dragonHostageService,
        EntityManagerInterface $em, FieldGuideService $fieldGuideService
    )
    {
        $this->inventoryService = $inventoryService;
        $this->userQuestRepository = $userQuestRepository;
        $this->petExperienceService = $petExperienceService;
        $this->transactionService = $transactionService;
        $this->rng = $rng;
        $this->hattierService = $hattierService;
        $this->userBirthdayService = $userBirthdayService;
        $this->dragonHostageService = $dragonHostageService;
        $this->em = $em;
        $this->fieldGuideService = $fieldGuideService;
    }

    public function adventure(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::OTHER, null);

        if($pet->getHat() && $pet->getHat()->getItem()->getName() === 'Red')
        {
            $pet->getHat()->changeItem(ItemRepository::findOneByName($this->em, 'William, Shush'));

            return PetActivityLogFactory::createUnreadLog($this->em, $pet, 'While ' . '%pet:' . $pet->getId() . '.name% was thinking about what to do, some random dude jumped out of nowhere and shot an arrow in %pet:' . $pet->getId() . '.name%\'s Red!')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ;
        }

        if($pet->getIsGrandparent() && !$pet->getClaimedGrandparentMerit())
        {
            /** @var string $newMerit */
            $newMerit = $this->rng->rngNextFromArray([
                MeritEnum::NEVER_EMBARRASSED, MeritEnum::EVERLASTING_LOVE, MeritEnum::NOTHING_TO_FEAR
            ]);

            $changes = new PetChanges($pet);

            $pet
                ->addMerit(MeritRepository::findOneByName($this->em, $newMerit))
                ->setClaimedGrandparentMerit()
            ;

            switch($newMerit)
            {
                case MeritEnum::NEVER_EMBARRASSED: $pet->increaseEsteem(72); break;
                case MeritEnum::EVERLASTING_LOVE: $pet->increaseLove(72); break;
                case MeritEnum::NOTHING_TO_FEAR: $pet->increaseSafety(72); break;
            }

            return PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name%, having become a grandparent, has been thinking about their life up until this point, and adopted a new philosophy: ' . $newMerit . '!')
                ->setChanges($changes->compare($pet))
                ->addInterestingness(PetActivityLogInterestingnessEnum::LEVEL_UP)
            ;
        }

        // check for birthday event
        $birthdayEvent = $this->userBirthdayService->doBirthday($petWithSkills);
        if($birthdayEvent)
            return $birthdayEvent;

        $rescuedAFairy = $this->userQuestRepository->findOrCreate($pet->getOwner(), 'Rescued a House Fairy from a Raccoon', null);
        if(!$rescuedAFairy->getValue())
        {
            $rescuedAFairy->setValue((new \DateTimeImmutable())->format('Y-m-d H:i:s'));

            $changes = new PetChanges($pet);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, 'While %pet:' . $pet->getId() . '.name% was thinking about what to do, they saw a raccoon carrying a House Fairy in its mouth. The raccoon stared at %pet:' . $pet->getId() . '.name% for a moment, then dropped the House Fairy and scurried away.')
                ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Fae-kind' ]))
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
            if($pet->getHat() && WeatherService::getWeather(new \DateTimeImmutable(), $pet)->getRainfall() > 0)
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

        if($pet->getOwner()->getGreenhouse() && $pet->getOwner()->getGreenhouse()->getHasBirdBath() && !$pet->getOwner()->getGreenhouse()->getVisitingBird() && $this->rng->rngNextInt(1, 20) === 1)
        {
            $bird = BirdBathBirdEnum::getRandomValue($this->rng);

            $pet->getOwner()->getGreenhouse()->setVisitingBird($bird);

            return PetActivityLogFactory::createUnreadLog($this->em, $pet, 'While ' . '%pet:' . $pet->getId() . '.name% was thinking about what to do, they saw a huge ' . $bird . ' swoop into the Greenhouse and land on the Bird Bath!')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Greenhouse' ]))
            ;
        }

        if($pet->getOwner()->hasUnlockedFeature(UnlockableFeatureEnum::DragonDen) && $this->rng->rngNextInt(1, 20) === 1)
        {
            $dragon = DragonHelpers::getAdultDragon($this->em, $pet->getOwner());

            if($dragon && !$dragon->getHostage())
            {
                $hostage = $this->dragonHostageService->generateHostage();

                $this->em->persist($hostage);

                $dragon->setHostage($hostage);
            }
        }

        if($activityLog)
            return $activityLog;

        return $this->doGenericAdventure($petWithSkills);
    }

    private function doGenericAdventure(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $level = $pet->getLevel();

        $changes = new PetChanges($pet);

        if($level >= 10 && $this->rng->rngNextInt(1, 130) === 1)
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
                    $possibleRewards[] = [ $this->rng->rngNextInt(4, 8), 'moneys' ];
            }

            if($level >= 25)
            {
                $possibleRewards[] = [ '', 'Gold Ore' ];
                $possibleRewards[] = [ 'a ', 'Fruit Basket' ];
            }

            if($level >= 30)
            {
                $possibleRewards[] = [ 'a chunk of ', 'Dark Matter' ];

                if($this->rng->rngNextInt(1, 20) === 1)
                    $possibleRewards[] = [ 'a ', 'Species Transmigration Serum' ];
                else
                    $possibleRewards[] = [ $this->rng->rngNextInt(8, 12), 'moneys' ];
            }

            $reward = $this->rng->rngNextFromArray($possibleRewards);
        }

        if($reward[1] === 'moneys')
            $describeReward = $reward[0] . '~~m~~';
        else
            $describeReward = $reward[0] . $reward[1];

        $event = $this->rng->rngNextInt(1, 4);

        if($event === 1)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, 'While ' . '%pet:' . $pet->getId() . '.name% was thinking about what to do, they spotted a bunch of ants carrying ' . $describeReward . '! %pet:' . $pet->getId() . '.name% took the ' . $reward[1] . ', brushed the ants off, and returned home.')
                ->setIcon('items/bug/ant-conga')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering' ]))
            ;
            $comment = $pet->getName() . ' stole this from some ants.';
        }
        else if($event === 2)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, 'While ' . '%pet:' . $pet->getId() . '.name% was thinking about what to do, they saw ' . $describeReward . ' floating downstream on a log! %pet:' . $pet->getId() . '.name% caught up to the log, and took the ' . $reward[1] . '.')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering' ]))
            ;
            $comment = $pet->getName() . ' found this floating on a log.';
        }
        else if($event === 3)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, 'While ' . '%pet:' . $pet->getId() . '.name% was thinking about what to do, they saw ' . $describeReward . ' poking out of a bag near a dumpster! %pet:' . $pet->getId() . '.name% took the ' . $reward[1] . ', and returned home.')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Dumpster-diving' ]))
            ;
            $comment = $pet->getName() . ' found this near a dumpster.';
        }
        else //if($event === 4)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, 'While ' . '%pet:' . $pet->getId() . '.name% was thinking about what to do, they saw a raccoon carrying ' . $describeReward . ' in its mouth. The raccoon stared at %pet:' . $pet->getId() . '.name% for a moment, then dropped the ' . $reward[1] . ' and scurried away.')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering' ]))
            ;
            $comment = 'A startled raccoon dropped this while ' . $pet->getName() . ' was out.';
        }

        if($reward[1] === 'moneys')
        {
            $this->transactionService->getMoney($pet->getOwner(), $reward[0], $comment);

            $activityLog->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Moneys' ]));
        }
        else
        {
            if($reward[1] === 'Fishkebab')
            {
                $spice = SpiceRepository::findOneByName($this->em, $this->rng->rngNextFromArray([
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

    public function discoverFeature(Pet $pet, string $feature, string $description): PetActivityLog
    {
        UserUnlockedFeatureHelpers::create($this->em, $pet->getOwner(), $feature);

        return PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' explored the town a bit, and stumbled upon a ' . $description . '! (Check it out in the menu!)')
            ->addInterestingness(PetActivityLogInterestingnessEnum::ONE_TIME_QUEST_ACTIVITY)
        ;
    }

    private function maybeHaveBirthdayCelebrated(Pet $pet): ?PetActivityLog
    {
        if($pet->getBirthDate() >= (new \DateTimeImmutable())->modify('-372 days'))
            return null;

        $partyEnchantment = EnchantmentRepository::findOneByName($this->em, 'Party');

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

            $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Tell Samarzhoustia', 'Your pet received a Paper Boat from a Tell Samarzhoustian representative.');
        }

        $message = 'While walking along a riverbank, ' . ActivityHelpers::PetName($pet) . ' was showered with confetti' . $givenAHat . '! A fish (apparently a representative from Tell Samarazhoustia) wished them a happy birthday... it\'s a little late, but still nice...? It would have been nicer if the fish didn\'t also remind ' . ActivityHelpers::PetName($pet) . ' to visit the Trader often...';

        $activityLog = $this->hattierService->petMaybeUnlockAura(
            $pet,
            $partyEnchantment,
            $message,
            $message,
            ActivityHelpers::PetName($pet) . '\'s got so much confetti on them, they were finding bits of confetti on their body all day...'
        );

        if($activityLog)
            $activityLog->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Special Event', 'Birthday' ]));

        return $activityLog;
    }
}
