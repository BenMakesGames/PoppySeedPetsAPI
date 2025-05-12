<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetGroup;
use App\Entity\PetRelationship;
use App\Enum\EnumInvalidValueException;
use App\Enum\HolidayEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityLogTagEnum;
use App\Enum\PetSkillEnum;
use App\Enum\RelationshipEnum;
use App\Enum\SocialTimeWantEnum;
use App\Enum\SpiritCompanionStarEnum;
use App\Enum\StatusEffectEnum;
use App\Exceptions\UnreachableException;
use App\Functions\ArrayFunctions;
use App\Functions\GrammarFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Model\PetChanges;
use App\Service\PetActivity\Holiday\AwaOdoriService;
use App\Service\PetActivity\Holiday\HoliService;
use App\Service\PetActivity\PregnancyService;
use Doctrine\ORM\EntityManagerInterface;

class PetSocialActivityService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PetRelationshipService $petRelationshipService,
        private readonly IRandom $rng,
        private readonly PetGroupService $petGroupService,
        private readonly PetExperienceService $petExperienceService,
        private readonly HoliService $holiService,
        private readonly PregnancyService $pregnancyService,
        private readonly AwaOdoriService $awaOdoriService
    )
    {
    }

    /**
     * @param Pet[] $roommates
     * @throws EnumInvalidValueException
     */
    public function runSocialTime(Pet $pet, array $roommates): bool
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

        if(!$pet->hasStatusEffect(StatusEffectEnum::WEREFORM) && $this->meetRoommates($pet, $roommates))
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

        $availableGroups = [];

        $wants = [
            [ 'activity' => SocialTimeWantEnum::HANG_OUT, 'weight' => 60 ]
        ];

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
                    $this->petGroupService->doGroupActivity($this->rng->rngNextFromArray($availableGroups->toArray()));
                    return true;

                case SocialTimeWantEnum::CREATE_GROUP:
                    if($this->petGroupService->createGroup($pet))
                        return true;
                    break;
            }
        }

        $pet->getHouseTime()->setCanAttemptSocialHangoutAfter((new \DateTimeImmutable())->modify('+15 minutes'));

        return false;
    }

    public function recomputeFriendRatings(Pet $pet): void
    {
        $friends = $this->getFriends($pet);

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

    /**
     * @return PetRelationship[]
     */
    public function getFriends(Pet $pet): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('r')->from(PetRelationship::class, 'r')
            ->leftJoin('r.relationship', 'friend')
            ->andWhere('r.pet=:pet')
            ->addOrderBy('r.commitment', 'DESC')
            ->setMaxResults($pet->getMaximumFriends())
            ->setParameter('pet', $pet)
        ;

        return $qb->getQuery()->execute();
    }

    private function hangOutWithFriend(Pet $pet): bool
    {
        $relationships = $this->getRelationshipsToHangOutWith($pet);

        $spiritCompanionAvailable = $pet->hasMerit(MeritEnum::SPIRIT_COMPANION) && ($pet->getSpiritCompanion()->getLastHangOut() === null || $pet->getSpiritCompanion()->getLastHangOut() < (new \DateTimeImmutable())->modify('-12 hours'));

        // no friends available? no spirit companion? GIT OUTTA' HE'E!
        if(count($relationships) === 0 && !$spiritCompanionAvailable)
            return false;

        // maybe hang out with a spirit companion, if you have one
        if($spiritCompanionAvailable && (count($relationships) === 0 || $this->rng->rngNextInt(1, count($relationships) + 1) === 1))
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

        $skipped = $this->rng->rngNextInt(0, 5);

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

        $dislikedRelationships = $this->getDislikedRelationshipsWithCommitment($pet);

        foreach($dislikedRelationships as $r)
            $r->increaseCommitment(-2);

        return true;
    }

    /**
     * @return PetRelationship[]
     */
    public function getDislikedRelationshipsWithCommitment(Pet $pet): array
    {
        $qb = $this->em->getRepository(PetRelationship::class)
            ->createQueryBuilder('r')
            ->leftJoin('r.pet', 'pet')
            ->leftJoin('r.relationship', 'friend')
            ->andWhere('r.currentRelationship IN (:dislikedRelationshipTypes)')
            ->andWhere('r.commitment>0')
            ->andWhere('pet.id=:petId')
            ->setParameter('petId', $pet->getId())
            ->setParameter('dislikedRelationshipTypes', [ RelationshipEnum::DISLIKE, RelationshipEnum::BROKE_UP ])
        ;

        return $qb->getQuery()->execute();
    }

    /**
     * @param PetRelationship[] $relationships
     * @return PetRelationship[]
     */
    private function getFriendRelationships(Pet $pet, array $relationships): array
    {
        /** @var PetRelationship[] $friendRelationships */
        $friendRelationships = $this->em->getRepository(PetRelationship::class)->findBy([
            'pet' => array_map(fn(PetRelationship $r) => $r->getRelationship(), $relationships),
            'relationship' => $pet
        ]);

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

            return $this->rng->rngNextInt(0, 999) < $chanceToHangOut;
        });

        if(count($relationships) === 0)
            return null;

        return ArrayFunctions::pick_one_weighted($relationships, fn(PetRelationship $r) => $r->getCommitment() + 1);
    }

    private static function getPregnancyViaSpiritCompanion(Pet $pet, IRandom $rng): bool
    {
        $companion = $pet->getSpiritCompanion();

        if(!$companion)
            return false;

        if($pet->getPregnancy() || !$pet->getIsFertile() || !$pet->hasMerit(MeritEnum::VOLAGAMY))
            return false;

        if($companion->getStar() === SpiritCompanionStarEnum::Sagittarius)
            return $rng->rngNextInt(1, 1000) === 1;

        return $rng->rngNextInt(1, 2000) === 1;
    }

    private function hangOutWithSpiritCompanion(Pet $pet): void
    {
        $teachingStat = null;
        $activityTags = [ 'Spirit Companion' ];
        $activityInterestingness = PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT;

        $changes = new PetChanges($pet);

        $this->petExperienceService->spendSocialEnergy($pet, PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT);

        $companion = $pet->getSpiritCompanion();

        $companion->setLastHangOut();

        $adjectives = [ 'bizarre', 'impressive', 'surprisingly-graphic', 'whirlwind' ];

        if($this->rng->rngNextInt(1, 3) !== 1 || ($pet->getSafety() > 0 && $pet->getLove() > 0 && $pet->getEsteem() > 0))
        {
            switch($companion->getStar())
            {
                case SpiritCompanionStarEnum::Altair:
                    // the flying/fighting eagle
                    if($this->rng->rngNextInt(1, 3) === 1)
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

                case SpiritCompanionStarEnum::Cassiopeia:
                    // sneaky snake
                    if($this->rng->rngNextInt(1, 3) === 1)
                    {
                        $teachingStat = PetSkillEnum::STEALTH;
                        $message = $companion->getName() . ' showed %pet:' . $pet->getId() . '.name% how to take advantage of their surroundings to hide their presence.';
                    }
                    else
                    {
                        if($this->rng->rngNextInt(1, 4) === 1)
                            $message = '%pet:' . $pet->getId() . '.name% listened to ' . $companion->getName() . ' for a little while. They had many, strange secrets to tell, but none really seemed that useful.';
                        else
                            $message = '%pet:' . $pet->getId() . '.name% listened to ' . $companion->getName() . ' for a little while.';
                    }
                    break;

                case SpiritCompanionStarEnum::Cepheus:
                    // a king
                    if($this->rng->rngNextInt(1, 3) === 1)
                    {
                        $teachingStat = PetSkillEnum::ARCANA;
                        $message = '%pet:' . $pet->getId() . '.name% listened to ' . $companion->getName() . '\'s stories about the various lands of the near and far Umbra...';
                    }
                    else
                    {
                        $adjective = $this->rng->rngNextFromArray($adjectives);
                        $message = $companion->getName() . ' told ' . GrammarFunctions::indefiniteArticle($adjective) . ' ' . $adjective . ' story they made just for %pet:' . $pet->getId() . '.name%!';
                    }
                    break;

                case SpiritCompanionStarEnum::Gemini:
                    $message = '%pet:' . $pet->getId() . '.name% played ' . $this->rng->rngNextFromArray([
                        'hide-and-go-seek tag',
                        'hacky sack',
                        'soccer',
                        'three-player checkers',
                        'charades',
                    ]) . ' with the ' . $companion->getName() . ' twins!';
                    break;

                case SpiritCompanionStarEnum::Hydra:
                    // scary monster; depicted as basically a friendly guard dog
                    $message = '%pet:' . $pet->getId() . '.name% played catch with ' . $companion->getName() . '!';
                    break;

                case SpiritCompanionStarEnum::Sagittarius:
                    // satyr-adjacent
                    if($this->rng->rngNextInt(1, 3) === 1)
                    {
                        // teaches music
                        $teachingStat = PetSkillEnum::MUSIC;
                        $message = '%pet:' . $pet->getId() . '.name% ' . $this->rng->rngNextFromArray([ 'played music', 'danced', 'sang' ]) . ' with ' . $companion->getName() . '!';
                    }
                    else
                    {
                        // hanging-out
                        $message = '%pet:' . $pet->getId() . '.name% went riding with ' . $companion->getName() . ' for a while!';
                    }
                    break;

                default:
                    throw new UnreachableException('Unknown Spirit Companion Star "' . $companion->getStar()->name . '"');
            }

            if($teachingStat)
            {
                $pet
                    ->increaseSafety($this->rng->rngNextInt(1, 2))
                    ->increaseLove($this->rng->rngNextInt(1, 2))
                    ->increaseEsteem($this->rng->rngNextInt(1, 2))
                ;
            }
            else
            {
                $pet
                    ->increaseSafety($this->rng->rngNextInt(2, 4))
                    ->increaseLove($this->rng->rngNextInt(2, 4))
                    ->increaseEsteem($this->rng->rngNextInt(2, 4))
                ;
            }

            if(self::getPregnancyViaSpiritCompanion($pet, $this->rng))
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
                case SpiritCompanionStarEnum::Altair:
                case SpiritCompanionStarEnum::Cepheus:
                    $pet
                        ->increaseSafety($this->rng->rngNextInt(6, 10))
                        ->increaseLove($this->rng->rngNextInt(2, 4))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling nervous, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' told a ' . $this->rng->rngNextFromArray($adjectives) . ' story about victory in combat, and swore to protect %pet:' . $pet->getId() . '.name%!';
                    break;
                case SpiritCompanionStarEnum::Cassiopeia:
                    $pet
                        ->increaseSafety($this->rng->rngNextInt(2, 4))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling nervous, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' whispered odd prophecies, then stared at %pet:' . $pet->getId() . '.name% expectantly. (It\'s the thought that counts...)';
                    break;
                case SpiritCompanionStarEnum::Gemini:
                    $pet
                        ->increaseSafety($this->rng->rngNextInt(4, 8))
                        ->increaseLove($this->rng->rngNextInt(2, 4))
                        ->increaseEsteem($this->rng->rngNextInt(2, 4))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling nervous, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' smiled, and split into multiple copies of itself, each defending %pet:' . $pet->getId() . '.name% from another angle. They all turned to %pet:' . $pet->getId() . '.name% and gave a sincere thumbs up before recombining.';
                    break;
                case SpiritCompanionStarEnum::Sagittarius:
                    $pet
                        ->increaseSafety($this->rng->rngNextInt(2, 4))
                        ->increaseLove($this->rng->rngNextInt(2, 4))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling nervous, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' tried to distract %pet:' . $pet->getId() . '.name% with ' . $this->rng->rngNextFromArray($adjectives) . ' stories about lavish parties. It kind of worked...';
                    break;
                case SpiritCompanionStarEnum::Hydra:
                    $pet
                        ->increaseSafety($this->rng->rngNextInt(4, 8))
                        ->increaseLove($this->rng->rngNextInt(4, 8))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling nervous, so talked to ' . $companion->getName() . '. Sensing %pet:' . $pet->getId() . '.name%\'s unease, ' . $companion->getName() . ' looked around for potential threats, and roared menacingly.';
                    break;
                default:
                    throw new UnreachableException('Unknown Spirit Companion Star "' . $companion->getStar()->name . '"');
            }
        }
        else if($pet->getLove() <= 0)
        {
            switch($companion->getStar())
            {
                case SpiritCompanionStarEnum::Altair:
                case SpiritCompanionStarEnum::Cepheus:
                    $pet
                        ->increaseSafety($this->rng->rngNextInt(2, 4))
                        ->increaseLove($this->rng->rngNextInt(2, 4))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling lonely, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' rambled some ' . $this->rng->rngNextFromArray($adjectives) . ' story about victory in combat... (It\'s the thought that counts...)';
                    break;
                case SpiritCompanionStarEnum::Cassiopeia:
                    $pet
                        ->increaseSafety($this->rng->rngNextInt(2, 4))
                        ->increaseLove($this->rng->rngNextInt(2, 4))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling lonely, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' whispered odd prophecies, then stared at %pet:' . $pet->getId() . '.name% expectantly. (It\'s the thought that counts...)';
                    break;
                case SpiritCompanionStarEnum::Gemini:
                    $pet
                        ->increaseSafety($this->rng->rngNextInt(4, 8))
                        ->increaseLove($this->rng->rngNextInt(4, 8))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling lonely, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' smiled, and split into multiple copies of itself, and they all played games together!';
                    break;
                case SpiritCompanionStarEnum::Sagittarius:
                    $pet
                        ->increaseSafety($this->rng->rngNextInt(2, 4))
                        ->increaseLove($this->rng->rngNextInt(4, 8))
                        ->increaseEsteem($this->rng->rngNextInt(2, 4))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling lonely, so talked to ' . $companion->getName() . '. The two hosted a party for themselves; %pet:' . $pet->getId() . '.name% had a lot of fun.';
                    break;
                case SpiritCompanionStarEnum::Hydra:
                    $pet
                        ->increaseSafety($this->rng->rngNextInt(4, 8))
                        ->increaseLove($this->rng->rngNextInt(4, 8))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling lonely, so talked to ' . $companion->getName() . '. Sensing %pet:' . $pet->getId() . '.name%\'s unease, ' . $companion->getName() . ' settled into %pet:' . $pet->getId() . '.name%\'s lap.';
                    break;
                default:
                    throw new UnreachableException('Unknown Spirit Companion Star "' . $companion->getStar()->name . '"');
            }
        }
        else // low on esteem
        {
            switch($companion->getStar())
            {
                case SpiritCompanionStarEnum::Altair:
                case SpiritCompanionStarEnum::Cepheus:
                    $pet
                        ->increaseSafety($this->rng->rngNextInt(2, 4))
                        ->increaseLove($this->rng->rngNextInt(2, 4))
                        ->increaseEsteem($this->rng->rngNextInt(2, 4))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling down, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' listened patiently; in the end, %pet:' . $pet->getId() . '.name% felt a little better.';
                    break;
                case SpiritCompanionStarEnum::Cassiopeia:
                    $pet
                        ->increaseEsteem($this->rng->rngNextInt(4, 8))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling down, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' whispered odd prophecies, then stared at %pet:' . $pet->getId() . '.name% expectantly. Somehow, that actually helped!';
                    break;
                case SpiritCompanionStarEnum::Gemini:
                    $pet
                        ->increaseLove($this->rng->rngNextInt(2, 4))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling down, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' tried to entertain %pet:' . $pet->getId() . '.name% by splitting into copies and dancing around, but it didn\'t really help...';
                    break;
                case SpiritCompanionStarEnum::Sagittarius:
                    $pet
                        ->increaseSafety($this->rng->rngNextInt(2, 4))
                        ->increaseLove($this->rng->rngNextInt(2, 4))
                        ->increaseEsteem($this->rng->rngNextInt(4, 8))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling down, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' empathized completely, having been in similar situations themselves. It was really nice to hear!';
                    break;
                case SpiritCompanionStarEnum::Hydra:
                    $pet
                        ->increaseSafety($this->rng->rngNextInt(2, 4))
                        ->increaseLove($this->rng->rngNextInt(2, 4))
                        ->increaseEsteem($this->rng->rngNextInt(4, 8))
                    ;
                    $message = $pet->getName() . ' was feeling down, so talked to ' . $companion->getName() . '. Sensing %pet:' . $pet->getId() . '.name%\'s unease, ' . $companion->getName() . ' settled into %pet:' . $pet->getId() . '.name%\'s lap.';
                    break;
                default:
                    throw new UnreachableException('Unknown Spirit Companion Star "' . $companion->getStar()->name . '"');
            }
        }

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $message)
            ->setIcon('companions/' . $companion->getImage())
            ->setChanges($changes->compare($pet))
            ->addInterestingness($activityInterestingness)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, $activityTags))
        ;

        if($teachingStat)
            $this->petExperienceService->gainExp($pet, 1, [ $teachingStat ], $activityLog);
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function hangOutWithOtherPet(PetRelationship $pet, PetRelationship $friend): void
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
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::One_on_One_Hangout ]))
        ;

        $friendLog
            ->setChanges($friendChanges->compare($friend->getPet()))
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::One_on_One_Hangout ]))
        ;
    }

    /**
     * @param Pet[] $roommates
     */
    private function meetRoommates(Pet $pet, array $roommates): bool
    {
        $metNewPet = false;

        foreach($roommates as $roommate)
        {
            if($roommate->hasMerit(MeritEnum::AFFECTIONLESS))
                continue;

            if(!$pet->hasRelationshipWith($roommate))
            {
                $metNewPet = true;
                $this->petRelationshipService->meetRoommate($pet, $roommate);
            }

            if(!$roommate->hasRelationshipWith($pet))
            {
                $metNewPet = true;
                $this->petRelationshipService->meetRoommate($roommate, $pet);
            }
        }

        return $metNewPet;
    }

    /**
     * @return PetRelationship[]
     */
    public function getRelationshipsToHangOutWith(Pet $pet): array
    {
        $maxFriendsToConsider = $pet->getMaximumFriends();

        $qb = $this->em->getRepository(PetRelationship::class)
            ->createQueryBuilder('r')
            ->leftJoin('r.pet', 'pet')
            ->leftJoin('r.relationship', 'friend')
            ->leftJoin('friend.houseTime', 'friendHouseTime')
            ->leftJoin('friend.statusEffects', 'friendStatusEffects')
            ->andWhere('pet.id=:petId')
            ->andWhere('friend.food + friend.alcohol + friend.junk > 0')
            ->andWhere('r.currentRelationship NOT IN (:excludedRelationshipTypes)')
            ->andWhere('friendHouseTime.socialEnergy >= :minimumFriendSocialEnergy')
            ->addOrderBy('r.commitment', 'DESC')
            ->setMaxResults($maxFriendsToConsider)
            ->setParameter('petId', $pet->getId())
            ->setParameter('excludedRelationshipTypes', [ RelationshipEnum::DISLIKE, RelationshipEnum::BROKE_UP ])
            ->setParameter('minimumFriendSocialEnergy', (PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT * 3) / 2)
        ;

        $friends = $qb->getQuery()->execute();

        if($pet->hasStatusEffect(StatusEffectEnum::WEREFORM))
        {
            // pets in Wereform only hang out with other pets in Wereform
            $friends = array_values(array_filter($friends, function(PetRelationship $r) {
                return $r->getRelationship()->hasStatusEffect(StatusEffectEnum::WEREFORM);
            }));
        }
        else
        {
            // pets NOT in Wereform only hang out with other pets NOT in Wereform
            $friends = array_values(array_filter($friends, function(PetRelationship $r) {
                return !$r->getRelationship()->hasStatusEffect(StatusEffectEnum::WEREFORM);
            }));
        }

        return $friends;
    }
}
