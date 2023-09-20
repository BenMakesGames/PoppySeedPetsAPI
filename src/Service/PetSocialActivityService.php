<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetGroup;
use App\Entity\PetRelationship;
use App\Enum\EnumInvalidValueException;
use App\Enum\HolidayEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetLocationEnum;
use App\Enum\PetSkillEnum;
use App\Enum\SocialTimeWantEnum;
use App\Enum\SpiritCompanionStarEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ArrayFunctions;
use App\Functions\GrammarFunctions;
use App\Model\PetChanges;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\PetRelationshipRepository;
use App\Service\PetActivity\Holiday\AwaOdoriService;
use App\Service\PetActivity\Holiday\HoliService;
use App\Service\PetActivity\PregnancyService;
use Doctrine\ORM\EntityManagerInterface;

class PetSocialActivityService
{
    private EntityManagerInterface $em;
    private ResponseService $responseService;
    private PetRelationshipService $petRelationshipService;
    private PetGroupService $petGroupService;
    private PetExperienceService $petExperienceService;
    private PetRelationshipRepository $petRelationshipRepository;
    private IRandom $squirrel3;
    private HoliService $holiService;
    private AwaOdoriService $awaOdoriService;
    private PregnancyService $pregnancyService;

    public function __construct(
        EntityManagerInterface $em, ResponseService $responseService, PetRelationshipService $petRelationshipService,
        Squirrel3 $squirrel3, PetGroupService $petGroupService, PetExperienceService $petExperienceService,
        PetRelationshipRepository $petRelationshipRepository, HoliService $holiService,
        PregnancyService $pregnancyService, AwaOdoriService $awaOdoriService
    )
    {
        $this->em = $em;
        $this->squirrel3 = $squirrel3;
        $this->responseService = $responseService;
        $this->petRelationshipService = $petRelationshipService;
        $this->petGroupService = $petGroupService;
        $this->petExperienceService = $petExperienceService;
        $this->petRelationshipRepository = $petRelationshipRepository;
        $this->holiService = $holiService;
        $this->pregnancyService = $pregnancyService;
        $this->awaOdoriService = $awaOdoriService;
    }

    public function runSocialTime(Pet $pet): bool
    {
        if($pet->hasMerit(MeritEnum::AFFECTIONLESS))
        {
            $pet->getHouseTime()->setSocialEnergy(-365 * 24 * 60);
            return true;
        }

        if($pet->getFood() + $pet->getAlcohol() + $pet->getJunk() < 0)
        {
            $pet->getHouseTime()->setCanAttemptSocialHangoutAfter((new \DateTimeImmutable())->modify('+5 minutes'));
            return false;
        }

        if(!$pet->hasStatusEffect(StatusEffectEnum::WEREFORM) && $this->meetRoommates($pet))
        {
            $this->petExperienceService->spendSocialEnergy($pet, PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT);
            return true;
        }

        $weather = WeatherService::getWeather(new \DateTimeImmutable(), $pet);

        if(!$pet->hasStatusEffect(StatusEffectEnum::WEREFORM))
        {
            if($weather->isHoliday(HolidayEnum::HOLI) && $this->holiService->adventure($pet))
                return true;
        }

        // were creatures can dance with other were creatures
        if($pet->getFood() > 0 && $weather->isHoliday(HolidayEnum::AWA_ODORI) && $this->awaOdoriService->adventure($pet))
            return true;

        $wants = [];

        $wants[] = [ 'activity' => SocialTimeWantEnum::HANG_OUT, 'weight' => 60 ];

        if(!$pet->hasStatusEffect(StatusEffectEnum::WEREFORM))
        {
            $availableGroups = $pet->getGroups()->filter(function(PetGroup $g) {
                return $g->getSocialEnergy() >= PetGroupService::SOCIAL_ENERGY_PER_MEET;
            });

            if(count($availableGroups) > 0)
                $wants[] = [ 'activity' => SocialTimeWantEnum::GROUP, 'weight' => 30 ];

            if(count($pet->getGroups()) < $pet->getMaximumGroups())
                $wants[] = [ 'activity' => SocialTimeWantEnum::CREATE_GROUP, 'weight' => 5 ];
        }

        while(count($wants) > 0)
        {
            $want = ArrayFunctions::pick_one_weighted($wants, fn($want) => $want['weight']);

            $activity = $want['activity'];

            $wants = array_filter($wants, fn($want) => $want['activity'] !== $activity);

            switch($activity)
            {
                case SocialTimeWantEnum::HANG_OUT:
                    if($this->hangOutWithFriend($pet))
                        return true;
                    break;

                case SocialTimeWantEnum::GROUP:
                    $this->petGroupService->doGroupActivity($this->squirrel3->rngNextFromArray($availableGroups->toArray()));
                    return true;

                case SocialTimeWantEnum::CREATE_GROUP:
                    if($this->petGroupService->createGroup($pet) !== null)
                        return true;
                    break;
            }
        }

        $pet->getHouseTime()->setCanAttemptSocialHangoutAfter((new \DateTimeImmutable())->modify('+15 minutes'));

        return false;
    }

    public function recomputeFriendRatings(Pet $pet)
    {
        $friends = $this->petRelationshipRepository->getFriends($pet);

        if(count($friends) == 0)
            return;

        $commitmentLevels = array_map(fn(PetRelationship $r) => $r->getCommitment(), $friends);

        $minCommitment = min($commitmentLevels);
        $maxCommitment = max($minCommitment + 1, max($commitmentLevels));
        $commitmentRange = $maxCommitment - $minCommitment;

        $interpolate = fn($x) => 1 + (int)(9 * ($x - $minCommitment) / $commitmentRange);

        foreach($friends as $friend)
            $friend->setRating($interpolate($friend->getCommitment()));
    }

    private function hangOutWithFriend(Pet $pet): bool
    {
        $relationships = $this->petRelationshipRepository->getRelationshipsToHangOutWith($pet);

        $spiritCompanionAvailable = $pet->hasMerit(MeritEnum::SPIRIT_COMPANION) && ($pet->getSpiritCompanion()->getLastHangOut() === null || $pet->getSpiritCompanion()->getLastHangOut() < (new \DateTimeImmutable())->modify('-12 hours'));

        // no friends available? no spirit companion? GIT OUTTA' HE'E!
        if(count($relationships) === 0 && !$spiritCompanionAvailable)
            return false;

        // maybe hang out with a spirit companion, if you have one
        if($spiritCompanionAvailable && (count($relationships) === 0 || $this->squirrel3->rngNextInt(1, count($relationships) + 1) === 1))
        {
            $this->hangOutWithSpiritCompanion($pet);
            return true;
        }

        $friendRelationshipsByFriendId = $this->getFriendRelationships($pet, $relationships);
        $chosenRelationship = $this->pickRelationshipToHangOutWith($relationships, $friendRelationshipsByFriendId);

        if(!$chosenRelationship)
            return false;

        $friend = $chosenRelationship->getRelationship();

        $friendRelationship = $friendRelationshipsByFriendId[$friend->getId()];

        $skipped = $this->squirrel3->rngNextInt(0, 5);

        foreach($relationships as $r)
        {
            if($r->getId() === $chosenRelationship->getId())
            {
                $r->increaseCommitment($skipped);
                break;
            }

            $r->increaseCommitment(-1);
            $skipped++;
        }

        // hang out with selected pet
        $this->hangOutWithOtherPet($chosenRelationship, $friendRelationship);

        $dislikedRelationships = $this->petRelationshipRepository->getDislikedRelationshipsWithCommitment($pet);

        foreach($dislikedRelationships as $r)
            $r->increaseCommitment(-2);

        return true;
    }

    /**
     * @param PetRelationship[] $relationships
     * @return PetRelationship[]
     */
    private function getFriendRelationships(Pet $pet, array $relationships): array
    {
        /** @var PetRelationship[] $friendRelationships */
        $friendRelationships = $this->petRelationshipRepository->findBy([
            'pet' => array_map(fn(PetRelationship $r) => $r->getRelationship(), $relationships),
            'relationship' => $pet
        ]);

        /** @var $friendRelationshipsByFriendId[] $friendRelationships */
        $friendRelationshipsByFriendId = [];

        foreach($friendRelationships as $fr)
            $friendRelationshipsByFriendId[$fr->getPet()->getId()] = $fr;

        return $friendRelationshipsByFriendId;
    }

    /**
     * @param PetRelationship[] $relationships
     * @param PetRelationship[] $friendRelationshipsByFriendId
     */
    private function pickRelationshipToHangOutWith(array $relationships, array $friendRelationshipsByFriendId): ?PetRelationship
    {
        $relationships = array_filter($relationships, function(PetRelationship $r) use($friendRelationshipsByFriendId) {
            // sanity check (the game isn't always sane...)
            if(!array_key_exists($r->getRelationship()->getId(), $friendRelationshipsByFriendId))
                throw new \Exception($r->getPet()->getName() . ' (#' . $r->getPet()->getId() . ') knows ' . $r->getRelationship()->getName() . ' (#' . $r->getRelationship()->getId() . '), but not the other way around! This is a bug, and should never happen! Make Ben fix it!');

            if($r->getRelationship()->getHouseTime()->getSocialEnergy() >= PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT * 2)
                return true;

            if($friendRelationshipsByFriendId[$r->getRelationship()->getId()]->getCommitment() >= $r->getCommitment())
                return true;

            $chanceToHangOut = $friendRelationshipsByFriendId[$r->getRelationship()->getId()]->getCommitment() * 1000 / $r->getCommitment();

            return $this->squirrel3->rngNextInt(0, 999) < $chanceToHangOut;
        });

        if(count($relationships) === 0)
            return null;

        return ArrayFunctions::pick_one_weighted($relationships, fn(PetRelationship $r) => $r->getCommitment() + 1);
    }

    private static function getPregnancyViaSpiritCompanion(Pet $pet, IRandom $rng): bool
    {
        $companion = $pet->getSpiritCompanion();

        if($pet->getPregnancy() || !$pet->getIsFertile())
            return false;

        if($companion->getStar() === SpiritCompanionStarEnum::SAGITTARIUS)
            return $rng->rngNextInt(1, 1000) === 1;

        return $rng->rngNextInt(1, 2000) === 1;
    }

    private function hangOutWithSpiritCompanion(Pet $pet)
    {
        $teachingStat = null;
        $activityTags = [ 'Spirit Companion' ];
        $activityInterestingness = PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT;

        $changes = new PetChanges($pet);

        $this->petExperienceService->spendSocialEnergy($pet, PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT);

        $companion = $pet->getSpiritCompanion();

        $companion->setLastHangOut();

        $adjectives = [ 'bizarre', 'impressive', 'surprisingly-graphic', 'whirlwind' ];

        if($this->squirrel3->rngNextInt(1, 3) !== 1 || ($pet->getSafety() > 0 && $pet->getLove() > 0 && $pet->getEsteem() > 0))
        {
            switch($companion->getStar())
            {
                case SpiritCompanionStarEnum::ALTAIR:
                    // the flying/fighting eagle
                    if($this->squirrel3->rngNextInt(1, 3) === 1)
                    {
                        $teachingStat = PetSkillEnum::BRAWL;
                        $message = '%pet:' . $pet->getId() . '.name% practiced hunting with ' . $companion->getName() . '!';
                    }
                    else
                    {
                        // hanging-out
                        $message = '%pet:' . $pet->getId() . '.name% taught ' . $companion->getName() . ' more about the physical world.';
                    }
                    break;

                case SpiritCompanionStarEnum::CASSIOPEIA:
                    // sneaky snake
                    if($this->squirrel3->rngNextInt(1, 3) === 1)
                    {
                        $teachingStat = PetSkillEnum::STEALTH;
                        $message = $companion->getName() . ' showed %pet:' . $pet->getId() . '.name% how to take advantage of their surroundings to hide their presence.';
                    }
                    else
                    {
                        if($this->squirrel3->rngNextInt(1, 4) === 1)
                            $message = '%pet:' . $pet->getId() . '.name% listened to ' . $companion->getName() . ' for a little while. They had many, strange secrets to tell, but none really seemed that useful.';
                        else
                            $message = '%pet:' . $pet->getId() . '.name% listened to ' . $companion->getName() . ' for a little while.';
                    }
                    break;

                case SpiritCompanionStarEnum::CEPHEUS:
                    // a king
                    if($this->squirrel3->rngNextInt(1, 3) === 1)
                    {
                        $teachingStat = PetSkillEnum::UMBRA;
                        $message = '%pet:' . $pet->getId() . '.name% listened to ' . $companion->getName() . '\'s stories about the various lands of the near and far Umbra...';
                    }
                    else
                    {
                        $adjective = $this->squirrel3->rngNextFromArray($adjectives);
                        $message = $companion->getName() . ' told ' . GrammarFunctions::indefiniteArticle($adjective) . ' ' . $adjective . ' story they made just for %pet:' . $pet->getId() . '.name%!';
                    }
                    break;

                case SpiritCompanionStarEnum::GEMINI:
                    $message = '%pet:' . $pet->getId() . '.name% played ' . $this->squirrel3->rngNextFromArray([
                        'hide-and-go-seek tag',
                        'hacky sack',
                        'soccer',
                        'three-player checkers',
                        'charades',
                    ]) . ' with the ' . $companion->getName() . ' twins!';
                    break;

                case SpiritCompanionStarEnum::HYDRA:
                    // scary monster; depicted as basically a friendly guard dog
                    $message = '%pet:' . $pet->getId() . '.name% played catch with ' . $companion->getName() . '!';
                    break;

                case SpiritCompanionStarEnum::SAGITTARIUS:
                    // satyr-adjacent
                    if($this->squirrel3->rngNextInt(1, 3) === 1)
                    {
                        // teaches music
                        $teachingStat = PetSkillEnum::MUSIC;
                        $message = '%pet:' . $pet->getId() . '.name% ' . $this->squirrel3->rngNextFromArray([ 'played music', 'danced', 'sang' ]) . ' with ' . $companion->getName() . '!';
                    }
                    else
                    {
                        // hanging-out
                        $message = '%pet:' . $pet->getId() . '.name% went riding with ' . $companion->getName() . ' for a while!';
                    }
                    break;

                default:
                    throw new \Exception('Unknown Spirit Companion Star "' . $companion->getStar() . '"');
            }

            if($teachingStat)
            {
                $pet
                    ->increaseSafety($this->squirrel3->rngNextInt(1, 2))
                    ->increaseLove($this->squirrel3->rngNextInt(1, 2))
                    ->increaseEsteem($this->squirrel3->rngNextInt(1, 2))
                ;
            }
            else
            {
                $pet
                    ->increaseSafety($this->squirrel3->rngNextInt(2, 4))
                    ->increaseLove($this->squirrel3->rngNextInt(2, 4))
                    ->increaseEsteem($this->squirrel3->rngNextInt(2, 4))
                ;
            }

            if(self::getPregnancyViaSpiritCompanion($pet, $this->squirrel3))
            {
                $this->pregnancyService->getPregnantViaSpiritCompanion($pet);
                $activityTags[] = 'Pregnancy';
                $activityInterestingness = PetActivityLogInterestingnessEnum::RARE_ACTIVITY;
                $message .= ' When the two touched, they felt a mysterious spark of energy! Somehow, %pet:' . $pet->getId() . '.name% knows... they\'re going to have a baby!';
            }

        }
        else if($pet->getSafety() <= 0)
        {
            switch($companion->getStar())
            {
                case SpiritCompanionStarEnum::ALTAIR:
                case SpiritCompanionStarEnum::CEPHEUS:
                    $pet
                        ->increaseSafety($this->squirrel3->rngNextInt(6, 10))
                        ->increaseLove($this->squirrel3->rngNextInt(2, 4))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling nervous, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' told a ' . $this->squirrel3->rngNextFromArray($adjectives) . ' story about victory in combat, and swore to protect %pet:' . $pet->getId() . '.name%!';
                    break;
                case SpiritCompanionStarEnum::CASSIOPEIA:
                    $pet
                        ->increaseSafety($this->squirrel3->rngNextInt(2, 4))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling nervous, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' whispered odd prophecies, then stared at %pet:' . $pet->getId() . '.name% expectantly. (It\'s the thought that counts...)';
                    break;
                case SpiritCompanionStarEnum::GEMINI:
                    $pet
                        ->increaseSafety($this->squirrel3->rngNextInt(4, 8))
                        ->increaseLove($this->squirrel3->rngNextInt(2, 4))
                        ->increaseEsteem($this->squirrel3->rngNextInt(2, 4))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling nervous, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' smiled, and split into multiple copies of itself, each defending %pet:' . $pet->getId() . '.name% from another angle. They all turned to %pet:' . $pet->getId() . '.name% and gave a sincere thumbs up before recombining.';
                    break;
                case SpiritCompanionStarEnum::SAGITTARIUS:
                    $pet
                        ->increaseSafety($this->squirrel3->rngNextInt(2, 4))
                        ->increaseLove($this->squirrel3->rngNextInt(2, 4))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling nervous, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' tried to distract %pet:' . $pet->getId() . '.name% with ' . $this->squirrel3->rngNextFromArray($adjectives) . ' stories about lavish parties. It kind of worked...';
                    break;
                case SpiritCompanionStarEnum::HYDRA:
                    $pet
                        ->increaseSafety($this->squirrel3->rngNextInt(4, 8))
                        ->increaseLove($this->squirrel3->rngNextInt(4, 8))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling nervous, so talked to ' . $companion->getName() . '. Sensing %pet:' . $pet->getId() . '.name%\'s unease, ' . $companion->getName() . ' looked around for potential threats, and roared menacingly.';
                    break;
                default:
                    throw new \Exception('Unknown Spirit Companion Star "' . $companion->getStar() . '"');
            }
        }
        else if($pet->getLove() <= 0)
        {
            switch($companion->getStar())
            {
                case SpiritCompanionStarEnum::ALTAIR:
                case SpiritCompanionStarEnum::CEPHEUS:
                    $pet
                        ->increaseSafety($this->squirrel3->rngNextInt(2, 4))
                        ->increaseLove($this->squirrel3->rngNextInt(2, 4))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling lonely, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' rambled some ' . $this->squirrel3->rngNextFromArray($adjectives) . ' story about victory in combat... (It\'s the thought that counts...)';
                    break;
                case SpiritCompanionStarEnum::CASSIOPEIA:
                    $pet
                        ->increaseSafety($this->squirrel3->rngNextInt(2, 4))
                        ->increaseLove($this->squirrel3->rngNextInt(2, 4))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling lonely, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' whispered odd prophecies, then stared at %pet:' . $pet->getId() . '.name% expectantly. (It\'s the thought that counts...)';
                    break;
                case SpiritCompanionStarEnum::GEMINI:
                    $pet
                        ->increaseSafety($this->squirrel3->rngNextInt(4, 8))
                        ->increaseLove($this->squirrel3->rngNextInt(4, 8))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling lonely, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' smiled, and split into multiple copies of itself, and they all played games together!';
                    break;
                case SpiritCompanionStarEnum::SAGITTARIUS:
                    $pet
                        ->increaseSafety($this->squirrel3->rngNextInt(2, 4))
                        ->increaseLove($this->squirrel3->rngNextInt(4, 8))
                        ->increaseEsteem($this->squirrel3->rngNextInt(2, 4))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling lonely, so talked to ' . $companion->getName() . '. The two hosted a party for themselves; %pet:' . $pet->getId() . '.name% had a lot of fun.';
                    break;
                case SpiritCompanionStarEnum::HYDRA:
                    $pet
                        ->increaseSafety($this->squirrel3->rngNextInt(4, 8))
                        ->increaseLove($this->squirrel3->rngNextInt(4, 8))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling lonely, so talked to ' . $companion->getName() . '. Sensing %pet:' . $pet->getId() . '.name%\'s unease, ' . $companion->getName() . ' settled into %pet:' . $pet->getId() . '.name%\'s lap.';
                    break;
                default:
                    throw new \Exception('Unknown Spirit Companion Star "' . $companion->getStar() . '"');
            }
        }
        else // low on esteem
        {
            switch($companion->getStar())
            {
                case SpiritCompanionStarEnum::ALTAIR:
                case SpiritCompanionStarEnum::CEPHEUS:
                    $pet
                        ->increaseSafety($this->squirrel3->rngNextInt(2, 4))
                        ->increaseLove($this->squirrel3->rngNextInt(2, 4))
                        ->increaseEsteem($this->squirrel3->rngNextInt(2, 4))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling down, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' listened patiently; in the end, %pet:' . $pet->getId() . '.name% felt a little better.';
                    break;
                case SpiritCompanionStarEnum::CASSIOPEIA:
                    $pet
                        ->increaseEsteem($this->squirrel3->rngNextInt(4, 8))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling down, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' whispered odd prophecies, then stared at %pet:' . $pet->getId() . '.name% expectantly. Somehow, that actually helped!';
                    break;
                case SpiritCompanionStarEnum::GEMINI:
                    $pet
                        ->increaseLove($this->squirrel3->rngNextInt(2, 4))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling down, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' tried to entertain %pet:' . $pet->getId() . '.name% by splitting into copies and dancing around, but it didn\'t really help...';
                    break;
                case SpiritCompanionStarEnum::SAGITTARIUS:
                    $pet
                        ->increaseSafety($this->squirrel3->rngNextInt(2, 4))
                        ->increaseLove($this->squirrel3->rngNextInt(2, 4))
                        ->increaseEsteem($this->squirrel3->rngNextInt(4, 8))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling down, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' empathized completely, having been in similar situations themselves. It was really nice to hear!';
                    break;
                case SpiritCompanionStarEnum::HYDRA:
                    $pet
                        ->increaseSafety($this->squirrel3->rngNextInt(2, 4))
                        ->increaseLove($this->squirrel3->rngNextInt(2, 4))
                        ->increaseEsteem($this->squirrel3->rngNextInt(4, 8))
                    ;
                    $message = $pet->getName() . ' was feeling down, so talked to ' . $companion->getName() . '. Sensing %pet:' . $pet->getId() . '.name%\'s unease, ' . $companion->getName() . ' settled into %pet:' . $pet->getId() . '.name%\'s lap.';
                    break;
                default:
                    throw new \Exception('Unknown Spirit Companion Star "' . $companion->getStar() . '"');
            }
        }

        $activityLog = $this->responseService->createActivityLog($pet, $message, 'companions/' . $companion->getImage(), $changes->compare($pet))
            ->addInterestingness($activityInterestingness)
            ->addTags(PetActivityLogTagRepository::findByNames($this->em, $activityTags))
        ;

        if($teachingStat)
            $this->petExperienceService->gainExp($pet, 1, [ $teachingStat ], $activityLog);
    }

    /**
     * @param PetRelationship $pet
     * @param PetRelationship $friend
     * @throws EnumInvalidValueException
     */
    private function hangOutWithOtherPet(PetRelationship $pet, PetRelationship $friend)
    {
        $petChanges = new PetChanges($pet->getPet());
        $friendChanges = new PetChanges($friend->getPet());

        $this->petExperienceService->spendSocialEnergy($pet->getPet(), PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT);
        $this->petExperienceService->spendSocialEnergy($friend->getPet(), PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT);

        $petPreviousRelationship = $pet->getCurrentRelationship();
        $friendPreviousRelationship = $friend->getCurrentRelationship();

        [ $petLog, $friendLog ] = $this->petRelationshipService->hangOutPrivately($pet, $friend);

        if($petPreviousRelationship !== $pet->getCurrentRelationship())
        {
            $relationshipMovement =
                abs(PetRelationshipService::calculateRelationshipDistance($pet->getRelationshipGoal(), $petPreviousRelationship)) -
                abs(PetRelationshipService::calculateRelationshipDistance($pet->getRelationshipGoal(), $pet->getCurrentRelationship()))
            ;

            $pet->getPet()
                ->increaseLove($relationshipMovement * 2)
                ->increaseEsteem($relationshipMovement)
            ;
        }

        if($friendPreviousRelationship !== $friend->getCurrentRelationship())
        {
            $relationshipMovement =
                abs(PetRelationshipService::calculateRelationshipDistance($friend->getRelationshipGoal(), $friendPreviousRelationship)) -
                abs(PetRelationshipService::calculateRelationshipDistance($friend->getRelationshipGoal(), $friend->getCurrentRelationship()))
            ;

            $friend->getPet()
                ->increaseLove($relationshipMovement * 2)
                ->increaseEsteem($relationshipMovement)
            ;
        }

        $petLog
            ->setChanges($petChanges->compare($pet->getPet()))
            ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ '1-on-1 Hangout' ]))
        ;

        $friendLog
            ->setChanges($friendChanges->compare($friend->getPet()))
            ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ '1-on-1 Hangout' ]))
        ;

        if($petLog->getPet()->getOwner()->getId() === $friendLog->getPet()->getOwner()->getId())
            $friendLog->setViewed();

        $this->em->persist($petLog);
        $this->em->persist($friendLog);
    }

    private function meetRoommates(Pet $pet): bool
    {
        /** @var Pet[] $otherPets */
        $otherPets = $this->em->getRepository(Pet::class)->createQueryBuilder('p')
            ->andWhere('p.owner = :owner')
            ->andWhere('p.location = :home')
            ->andWhere('p.id != :thisPet')
            ->setParameter('owner', $pet->getOwner())
            ->setParameter('thisPet', $pet->getId())
            ->setParameter('home', PetLocationEnum::HOME)
            ->getQuery()
            ->getResult()
        ;

        $metNewPet = false;

        foreach($otherPets as $otherPet)
        {
            if($otherPet->hasMerit(MeritEnum::AFFECTIONLESS))
                continue;

            if(!$pet->hasRelationshipWith($otherPet))
            {
                $metNewPet = true;
                $this->petRelationshipService->meetRoommate($pet, $otherPet);
            }

            if(!$otherPet->hasRelationshipWith($pet))
            {
                $metNewPet = true;
                $this->petRelationshipService->meetRoommate($otherPet, $pet);
            }
        }

        return $metNewPet;
    }
}
