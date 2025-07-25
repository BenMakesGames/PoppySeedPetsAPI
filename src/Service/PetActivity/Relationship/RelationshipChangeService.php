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


namespace App\Service\PetActivity\Relationship;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetRelationship;
use App\Enum\EnumInvalidValueException;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\RelationshipEnum;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Service\IRandom;
use Doctrine\ORM\EntityManagerInterface;

class RelationshipChangeService
{
    public function __construct(
        private readonly LoveService $loveService,
        private readonly IRandom $rng,
        private readonly EntityManagerInterface $em
    )
    {
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    public function hangOutPrivatelySuggestingRelationshipChange(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p1->getCurrentRelationship())
        {
            case RelationshipEnum::FriendlyRival:
                $logs = $this->hangOutPrivatelySuggestingRelationshipChangeAsFriendlyRival($p1, $p2);
                break;

            case RelationshipEnum::Friend:
                $logs = $this->hangOutPrivatelySuggestingRelationshipChangeAsFriends($p1, $p2);
                break;

            case RelationshipEnum::BFF:
                $logs = $this->hangOutPrivatelySuggestingRelationshipChangeAsBFFs($p1, $p2);
                break;

            case RelationshipEnum::FWB:
                $logs = $this->hangOutPrivatelySuggestingRelationshipChangeAsFWBs($p1, $p2);
                break;

            case RelationshipEnum::Mate:
                $logs = $this->hangOutPrivatelySuggestingRelationshipChangeAsMates($p1, $p2);
                break;

            default:
                throw new \Exception('Current relationship is of an unexpected type, "' . $p1->getCurrentRelationship() . '"');
        }

        $p1->setTimeUntilChange();
        $p2->setTimeUntilChange();

        foreach($logs as $log)
            $log->addInterestingness(PetActivityLogInterestingness::RelationshipDiscussion);

        return $logs;
    }

    /**
     * @return PetActivityLog[]
     */
    private static function createLogs(
        EntityManagerInterface $em,
        Pet $p1, string $p1Message,
        Pet $p2, string $p2Message,
        string $icon = ''
    ): array
    {
        $log1 = PetActivityLogFactory::createUnreadLog($em, $p1, $p1Message);

        $log2 = $p1->getOwner()->getId() === $p2->getOwner()->getId()
            ? PetActivityLogFactory::createReadLog($em, $p2, $p2Message)
            : PetActivityLogFactory::createUnreadLog($em, $p2, $p2Message);

        if($icon)
        {
            $log1->setIcon($icon);
            $log2->setIcon($icon);
        }

        return [ $log1, $log2 ];
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelySuggestingRelationshipChangeAsFriendlyRival(PetRelationship $p1, PetRelationship $p2): array
    {
        if($p1->getRelationshipGoal() === RelationshipEnum::Dislike)
        {
            $p1->setCurrentRelationship(RelationshipEnum::Dislike);
            $p2->setCurrentRelationship(RelationshipEnum::Dislike);

            if($p2->getRelationshipGoal() === RelationshipEnum::Dislike)
            {
                $log2Message = $p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s shennanigans! The feeling is mutual! They are no longer friendly rivals!';
            }
            else
            {
                $p2->setRelationshipGoal(RelationshipEnum::Dislike);

                $log2Message = $p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s shennanigans! They don\'t want to be friendly rivals any more! (How rude!)';
            }

            return self::createLogs($this->em,
                $p1->getPet(), $p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s shennanigans! They are no longer friendly rivals.',
                $p2->getPet(), $log2Message
            );
        }

        if($p2->getRelationshipGoal() === RelationshipEnum::Dislike)
        {
            $p1
                ->setCurrentRelationship(RelationshipEnum::BrokeUp)
                ->setRelationshipGoal($this->rng->rngNextFromArray([
                    RelationshipEnum::FriendlyRival, RelationshipEnum::Dislike, RelationshipEnum::Dislike, RelationshipEnum::Dislike
                ]))
            ;

            $p2->setCurrentRelationship(RelationshipEnum::BrokeUp);

            return self::createLogs(
                $this->em,
                $p1->getPet(), $p1->getPet()->getName() . ' wanted to be friends with ' . $p2->getPet()->getName() . ', but ' . $p2->getPet()->getName() . ' apparently wants nothing to do with ' . $p1->getPet()->getName() . ' anymore! :(',
                $p2->getPet(), $p1->getPet()->getName() . ' wanted to be friends; ' . $p2->getPet()->getName() . ' rejected, wanting nothing to do with with ' . $p2->getPet()->getName() . '!'
            );
        }

        $p1->setCurrentRelationship(RelationshipEnum::Friend);
        $p2->setCurrentRelationship(RelationshipEnum::Friend);

        if($this->rng->rngNextInt(1, 3) === 1)
            $mostly = ' (Well, mostly!)';
        else
            $mostly = '';

        $log1Message = $p1->getRelationshipGoal() === RelationshipEnum::FriendlyRival
            ? $p1->getPet()->getName() . ' wanted to be friends with ' . $p2->getPet()->getName() . '; ' . $p2->getPet()->getName() . ' accepted...'
            : $p1->getPet()->getName() . ' wanted to be friends with ' . $p2->getPet()->getName() . '; ' . $p2->getPet()->getName() . ' happily accepted! No more of this silly rivalry stuff!' . $mostly;

        $log2Message = $p2->getRelationshipGoal() === RelationshipEnum::FriendlyRival
            ? $p1->getPet()->getName() . ' wanted to be friends with ' . $p2->getPet()->getName() . '; they accepted...'
            : $p1->getPet()->getName() . ' wanted to be friends with ' . $p2->getPet()->getName() . '; they happily accepted! No more of this silly rivalry stuff!' . $mostly;

        return self::createLogs(
            $this->em,
            $p1->getPet(), $log1Message,
            $p2->getPet(), $log2Message
        );
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelySuggestingRelationshipChangeAsFriends(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p1->getRelationshipGoal())
        {
            case RelationshipEnum::Dislike:
                return $this->hangOutPrivatelyFromFriendsToDisliked($p1, $p2);

            case RelationshipEnum::FriendlyRival:
                return $this->hangOutPrivatelyFromFriendsToFriendlyRivals($p1, $p2);

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelyFromFriendsToBFFs($p1, $p2);

            case RelationshipEnum::FWB:
                if($this->rng->rngNextInt(1, 4) === 1)
                    return $this->hangOutPrivatelyFromFriendsToBFFs($p1, $p2);
                else
                    return $this->hangOutPrivatelyFromFriendsToFWBs($p1, $p2);

            case RelationshipEnum::Mate:
                if($this->rng->rngNextInt(1, 4) === 1)
                    return $this->hangOutPrivatelyFromFriendsToMates($p1, $p2);
                else
                    return $this->hangOutPrivatelyFromFriendsToBFFs($p1, $p2);

            default:
                throw new \InvalidArgumentException('p1 relationship goal is of an unexpected type, "' . $p1->getRelationshipGoal() . '"');
        }
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromFriendsToBFFs(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::Dislike:
                $p1->setCurrentRelationship(RelationshipEnum::BrokeUp);
                $p2->setCurrentRelationship(RelationshipEnum::BrokeUp);
                $p1->setRelationshipGoal(RelationshipEnum::Dislike);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' said that they consider ' . $p2->getPet()->getName() . ' a best friend; ' . $p2->getPet()->getName() . ' revealed that they don\'t actually like hanging out with ' . $p1->getPet()->getName() . '! They are no longer friends :(',
                    $p2->getPet(), $p1->getPet()->getName() . ' said that they consider ' . $p2->getPet()->getName() . ' a best friend; ' . $p2->getPet()->getName() . ' revealed that they don\'t actually like hanging out with ' . $p1->getPet()->getName() . '! They are no longer friends :|'
                );

            case RelationshipEnum::Friend:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 45, 45);

            case RelationshipEnum::FriendlyRival:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 40, 40);

            case RelationshipEnum::BFF:
            case RelationshipEnum::FWB:
                $p1->setCurrentRelationship(RelationshipEnum::BFF);
                $p2->setCurrentRelationship(RelationshipEnum::BFF);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' said that they consider ' . $p2->getPet()->getName() . ' a best friend; ' . $p2->getPet()->getName() . ' feels the same way! The two are now BFFs! :D',
                    $p2->getPet(), $p1->getPet()->getName() . ' said that they consider ' . $p2->getPet()->getName() . ' a best friend; ' . $p2->getPet()->getName() . ' feels the same way! The two are now BFFs! :D',
                    'icons/activity-logs/friend'
                );

            case RelationshipEnum::Mate:
                return $this->hangOutPrivatelyFromBFFsToMates($p2, $p1);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromFriendsToFWBs(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::Dislike:
                $p1->setCurrentRelationship(RelationshipEnum::BrokeUp);
                $p2->setCurrentRelationship(RelationshipEnum::BrokeUp);
                $p1->setRelationshipGoal(RelationshipEnum::Dislike);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' said that they want to be FWBs with ' . $p2->getPet()->getName() . '; ' . $p2->getPet()->getName() . ' revealed that they don\'t actually like hanging out with ' . $p1->getPet()->getName() . '! They are no longer friends :(',
                    $p2->getPet(), $p1->getPet()->getName() . ' said that they want to be FWBs with ' . $p2->getPet()->getName() . '; ' . $p2->getPet()->getName() . ' revealed that they don\'t actually like hanging out with ' . $p1->getPet()->getName() . '! They are no longer friends >:(',
                    'icons/activity-logs/breakup'
                );

            case RelationshipEnum::Friend:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 60, 25);

            case RelationshipEnum::FriendlyRival:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 10, 10);

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 80, 15);

            case RelationshipEnum::FWB:
                $p1->setCurrentRelationship(RelationshipEnum::FWB);
                $p2->setCurrentRelationship(RelationshipEnum::FWB);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' said that they\'d like to be FWBs with ' . $p2->getPet()->getName() . '; ' . $p2->getPet()->getName() . ' feels the same way ' . $this->loveService->sexyTimesEmoji($p1->getPet(), $p2->getPet()),
                    $p2->getPet(), $p1->getPet()->getName() . ' said that they\'d like to be FWBs with ' . $p2->getPet()->getName() . '; ' . $p2->getPet()->getName() . ' feels the same way ' . $this->loveService->sexyTimesEmoji($p1->getPet(), $p2->getPet()),
                    'icons/activity-logs/friend-cute'
                );

            case RelationshipEnum::Mate:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 65, 25);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelySuggestingMatesWithCompleteRejection(PetRelationship $p1, PetRelationship $p2): array
    {
        $p1
            ->setCurrentRelationship(RelationshipEnum::BrokeUp)
            ->setRelationshipGoal(RelationshipEnum::Dislike)
        ;

        $p2->setCurrentRelationship(RelationshipEnum::BrokeUp);

        return self::createLogs(
            $this->em,
            $p1->getPet(), $p1->getPet()->getName() . ' wanted to date ' . $p2->getPet()->getName() . ', but ' . $p2->getPet()->getName() . ' revealed that they don\'t actually like hanging out with ' . $p1->getPet()->getName() . '! They are no longer friends :\'(',
            $p2->getPet(), $p1->getPet()->getName() . ' wanted to date ' . $p2->getPet()->getName() . ', but ' . $p2->getPet()->getName() . ' revealed that they don\'t actually like hanging out with ' . $p1->getPet()->getName() . '! They are no longer friends! >:(',
            'icons/activity-logs/breakup'
        );
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromFriendsToMates(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::Dislike:
                return $this->hangOutPrivatelySuggestingMatesWithCompleteRejection($p1, $p2);

            case RelationshipEnum::Friend:
                if($this->rng->rngNextInt(1, 4) === 1)
                    return $this->hangOutPrivatelyFromFriendsToBFFs($p1, $p2);
                else
                    return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 25, 45);

            case RelationshipEnum::FriendlyRival:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 10, 10);

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 45, 45);

            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 25, 45);

            case RelationshipEnum::Mate:
                $p1->setCurrentRelationship(RelationshipEnum::Mate);
                $p2->setCurrentRelationship(RelationshipEnum::Mate);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wants to date ' . $p2->getPet()->getName() . '! ' . $p2->getPet()->getName() . ' feels the same way! The two are now dating! <3',
                    $p2->getPet(), $p1->getPet()->getName() . ' wants to date ' . $p2->getPet()->getName() . '! ' . $p2->getPet()->getName() . ' feels the same way! The two are now dating! <3',
                    'icons/activity-logs/friend-cute'
                );

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromBFFsToMates(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::Dislike:
                return $this->hangOutPrivatelySuggestingMatesWithCompleteRejection($p1, $p2);

            case RelationshipEnum::Friend:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p2, $p1, 5, 30);

            case RelationshipEnum::FriendlyRival:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 10, 10);

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 45, 45);

            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 25, 45);

            case RelationshipEnum::Mate:
                $p1->setCurrentRelationship(RelationshipEnum::Mate);
                $p2->setCurrentRelationship(RelationshipEnum::Mate);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wants to date ' . $p2->getPet()->getName() . '! ' . $p2->getPet()->getName() . ' feels the same way! The two are now dating! <3',
                    $p2->getPet(), $p1->getPet()->getName() . ' wants to date ' . $p2->getPet()->getName() . '! ' . $p2->getPet()->getName() . ' feels the same way! The two are now dating! <3',
                    'icons/activity-logs/friend-cute'
                );

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama(
        PetRelationship $p1, PetRelationship $p2,
        int $chanceP1ChangesMind, int $chanceP2ChangesMind
    ): array
    {
        $downgradeDescription = [
            RelationshipEnum::Dislike => 'break up entirely',
            RelationshipEnum::Friend => 'just be friends',
            RelationshipEnum::BFF => 'just be BFFs',
            RelationshipEnum::FWB => 'just be friends, but maybe still, you know, _do stuff_',
            RelationshipEnum::FriendlyRival => 'just be friendly rivals',
            RelationshipEnum::Mate => 'date',
        ];

        $p1IsFriendOfTheWorld = $p1->getPet()->hasMerit(MeritEnum::FRIEND_OF_THE_WORLD);
        $p2IsFriendOfTheWorld = $p2->getPet()->hasMerit(MeritEnum::FRIEND_OF_THE_WORLD);

        [ $p1ChangesMind, $p2ChangesMind ] = $this->determineWhoChangesTheirMind($p1, $p2, $chanceP1ChangesMind, $chanceP2ChangesMind);

        $tags = [ 'Relationship Change' ];

        if($p1ChangesMind)
        {
            $originalGoal = $p1->getRelationshipGoal();

            $p1->setCurrentRelationship($p2->getRelationshipGoal());
            $p2->setCurrentRelationship($p2->getRelationshipGoal());

            if($this->rng->rngNextInt(1, 4) !== 1)
                $p1->setRelationshipGoal($p2->getRelationshipGoal());

            if($p1IsFriendOfTheWorld)
                $message = $p1->getPet()->getName() . ' wanted to ' . $downgradeDescription[$originalGoal] . ', but ' . $p2->getPet()->getName() . ' asked to ' . $downgradeDescription[$p2->getCurrentRelationship()] . ', instead. ' . $p1->getPet()->getName() . ' agreed immediately!';
            else
                $message = $p1->getPet()->getName() . ' wanted to ' . $downgradeDescription[$originalGoal] . ', but ' . $p2->getPet()->getName() . ' was upset, and asked to ' . $downgradeDescription[$p2->getCurrentRelationship()] . '. After talking for a while, ' . $p1->getPet()->getName() . ' agreed...';
        }
        else if($p2ChangesMind)
        {
            $p1->setCurrentRelationship($p1->getRelationshipGoal());
            $p2->setCurrentRelationship($p1->getRelationshipGoal());

            if($this->rng->rngNextInt(1, 4) !== 1)
                $p2->setRelationshipGoal($p1->getRelationshipGoal());

            if($p2IsFriendOfTheWorld)
                $message = $p1->getPet()->getName() . ' wanted to ' . $downgradeDescription[$p1->getRelationshipGoal()] . '. ' . $p2->getPet()->getName() . ' agreed immediately!';
            else
                $message = $p1->getPet()->getName() . ' wanted to ' . $downgradeDescription[$p1->getRelationshipGoal()] . '. ' . $p2->getPet()->getName() . ' was upset, but after talking for a while said that it would be okay...';
        }
        else // break up
        {
            $message = $p1->getPet()->getName() . ' wanted to ' . $downgradeDescription[$p1->getRelationshipGoal()] . '; ' . $p2->getPet()->getName() . ' was really upset! After arguing for a while, the two broke up entirely! :(';
            $tags[] = 'Break-up';

            $p1->setCurrentRelationship(RelationshipEnum::BrokeUp);
            $p2->setCurrentRelationship(RelationshipEnum::BrokeUp);
        }

        [ $p1Log, $p2Log ] = self::createLogs(
            $this->em,
            $p1->getPet(), $message,
            $p2->getPet(), $message
        );

        $p1Log->addTags(PetActivityLogTagHelpers::findByNames($this->em, $tags));
        $p2Log->addTags(PetActivityLogTagHelpers::findByNames($this->em, $tags));

        return [ $p1Log, $p2Log ];
    }

    private function determineWhoChangesTheirMind(
        PetRelationship $p1, PetRelationship $p2, int $chanceP1ChangesMind, int $chanceP2ChangesMind
    ): array
    {
        $p1IsFriendOfTheWorld = $p1->getPet()->hasMerit(MeritEnum::FRIEND_OF_THE_WORLD);
        $p2IsFriendOfTheWorld = $p2->getPet()->hasMerit(MeritEnum::FRIEND_OF_THE_WORLD);

        if($p1IsFriendOfTheWorld && $p2IsFriendOfTheWorld)
        {
            $chanceP1ChangesMind = 50;
            $chanceP2ChangesMind = 50;
        }
        else
        {
            if($p1->getPet()->hasMerit(MeritEnum::INTROSPECTIVE))
                $chanceP1ChangesMind = (int)ceil($chanceP1ChangesMind / 4);

            if($p2->getPet()->hasMerit(MeritEnum::INTROSPECTIVE))
                $chanceP2ChangesMind = (int)ceil($chanceP2ChangesMind / 4);
        }

        $r = $this->rng->rngNextInt(1, 100);

        $p1ChangesMind = $r <= $chanceP1ChangesMind;
        $p2ChangesMind = $r > $chanceP1ChangesMind && $r <= $chanceP1ChangesMind + $chanceP2ChangesMind;

        if(!$p1ChangesMind && !$p2ChangesMind)
        {
            if($p1IsFriendOfTheWorld)
                $p1ChangesMind = true;
            else if($p2IsFriendOfTheWorld)
                $p2ChangesMind = true;
        }

        return [ $p1ChangesMind, $p2ChangesMind ];
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama(
        PetRelationship $p1,
        PetRelationship $p2,
        int $chanceP1ChangesMind,
        int $chanceP2ChangesMind
    ): array
    {
        $upgradeDescription = [
            RelationshipEnum::Dislike => 'break up',
            RelationshipEnum::FriendlyRival => 'be friendly rivals',
            RelationshipEnum::Friend => 'be friends',
            RelationshipEnum::BFF => 'be BFFs',
            RelationshipEnum::FWB => 'be FWBs',
            RelationshipEnum::Mate => 'date',
        ];

        $downgradeDescription = [
            RelationshipEnum::Dislike => 'break up entirely',
            RelationshipEnum::FriendlyRival => 'just be friendly rivals',
            RelationshipEnum::Friend => 'just be friends',
            RelationshipEnum::BFF => 'just be BFFs',
            RelationshipEnum::FWB => 'just be friends, but maybe still, you know, _do stuff_',
            RelationshipEnum::Mate => 'date',
        ];

        $descriptioning = [
            RelationshipEnum::Dislike => 'breaking up entirely',
            RelationshipEnum::FriendlyRival => 'being friendly rivals',
            RelationshipEnum::Friend => 'being friends',
            RelationshipEnum::BFF => 'being BFFs',
            RelationshipEnum::FWB => 'being FWBs',
            RelationshipEnum::Mate => 'dating',
        ];

        $p1IsFriendOfTheWorld = $p1->getPet()->hasMerit(MeritEnum::FRIEND_OF_THE_WORLD);
        $p2IsFriendOfTheWorld = $p2->getPet()->hasMerit(MeritEnum::FRIEND_OF_THE_WORLD);

        [$p1ChangesMind, $p2ChangesMind] = $this->determineWhoChangesTheirMind($p1, $p2, $chanceP1ChangesMind, $chanceP2ChangesMind);

        $tags = [ 'Relationship Change' ];
        $icon = '';

        if($p1ChangesMind)
        {
            if($p1IsFriendOfTheWorld)
                $message = $p1->getPet()->getName() . ' wanted to ' . $upgradeDescription[$p1->getRelationshipGoal()] . ', but ' . $p2->getPet()->getName() . ' wants to ' . $downgradeDescription[$p2->getRelationshipGoal()] . '. ' . $p1->getPet()->getName() . ' immediately agreed to ' . $upgradeDescription[$p2->getRelationshipGoal()] . '!';
            else
                $message = $p1->getPet()->getName() . ' wanted to ' . $upgradeDescription[$p1->getRelationshipGoal()] . ', but ' . $p2->getPet()->getName() . ' wants to ' . $downgradeDescription[$p2->getRelationshipGoal()] . '. ' . $p1->getPet()->getName() . ' thought for a bit, and agreed to try ' . $descriptioning[$p2->getRelationshipGoal()] . '!';

            $p1->setCurrentRelationship($p2->getRelationshipGoal());
            $p2->setCurrentRelationship($p2->getRelationshipGoal());

            if($this->rng->rngNextInt(1, 3) !== 1)
                $p1->setRelationshipGoal($p2->getRelationshipGoal());
        }
        else if($p2ChangesMind)
        {
            if($p2IsFriendOfTheWorld)
                $message = $p1->getPet()->getName() . ' wanted to ' . $upgradeDescription[$p1->getRelationshipGoal()] . '. ' . $p2->getPet()->getName() . ' agreed immediately!';
            else
                $message = $p1->getPet()->getName() . ' wanted to ' . $upgradeDescription[$p1->getRelationshipGoal()] . ', but ' . $p2->getPet()->getName() . ' wants to ' . $downgradeDescription[$p2->getRelationshipGoal()] . '. ' . $p2->getPet()->getName() . ' thought for a bit, and agreed to try ' . $descriptioning[$p1->getRelationshipGoal()] . '!';

            $p1->setCurrentRelationship($p1->getRelationshipGoal());
            $p2->setCurrentRelationship($p1->getRelationshipGoal());

            if($this->rng->rngNextInt(1, 3) !== 1)
                $p2->setRelationshipGoal($p1->getRelationshipGoal());
        }
        else // break up
        {
            $message = $p1->getPet()->getName() . ' wanted to ' . $upgradeDescription[$p1->getRelationshipGoal()] . ', but ' . $p2->getPet()->getName() . ' doesn\'t want that. After arguing for a while, the two broke up entirely! :\'(';
            $tags[] = 'Break-up';
            $icon = 'icons/activity-logs/breakup';

            $p1->setCurrentRelationship(RelationshipEnum::BrokeUp);
            $p2->setCurrentRelationship(RelationshipEnum::BrokeUp);
        }

        [ $p1Log, $p2Log ] = self::createLogs(
            $this->em,
            $p1->getPet(), $message,
            $p2->getPet(), $message,
            $icon
        );

        $p1Log->addTags(PetActivityLogTagHelpers::findByNames($this->em, $tags));
        $p2Log->addTags(PetActivityLogTagHelpers::findByNames($this->em, $tags));

        return [ $p1Log, $p2Log ];
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromFriendsToDisliked(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::Dislike:
                $p1->setCurrentRelationship(RelationshipEnum::Dislike);
                $p2->setCurrentRelationship(RelationshipEnum::Dislike);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! The feeling is mutual! They are no longer friends >:(',
                    $p2->getPet(), $p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense! The feeling is mutual! They are no longer friends >:('
                );

            case RelationshipEnum::Friend:
                $p2->getPet()
                    ->increaseLove(-$this->rng->rngNextInt(12, 18))
                    ->increaseEsteem(-$this->rng->rngNextInt(8, 12))
                ;

                $p1->setCurrentRelationship(RelationshipEnum::BrokeUp);
                $p2
                    ->setRelationshipGoal(RelationshipEnum::Dislike)
                    ->setCurrentRelationship(RelationshipEnum::BrokeUp)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! They are no longer friends >:(',
                    $p2->getPet(), $p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense, and no longer wants to be friends! ' . $p2->getPet()->getName() . ' thought they had a really good friendship going... :(',
                    'icons/activity-logs/breakup'
                );

            case RelationshipEnum::FriendlyRival:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 35, 0);

            case RelationshipEnum::BFF:
            case RelationshipEnum::FWB:
            case RelationshipEnum::Mate:
                $p2->getPet()
                    ->increaseLove(-$this->rng->rngNextInt(16, 24))
                    ->increaseEsteem(-$this->rng->rngNextInt(12, 16))
                ;

                $p1->setCurrentRelationship(RelationshipEnum::BrokeUp);
                $p2
                    ->setRelationshipGoal(RelationshipEnum::Dislike)
                    ->setCurrentRelationship(RelationshipEnum::BrokeUp)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! They are no longer friends >:(',
                    $p2->getPet(), $p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense, and no longer wants to be friends! ' . $p2->getPet()->getName() . ' thought they had a really good friendship going, and had been hoping they might be something more :\'(',
                    'icons/activity-logs/breakup'
                );

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromFriendsToFriendlyRivals(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::Dislike:
                $p1->setCurrentRelationship(RelationshipEnum::BrokeUp);
                $p2->setCurrentRelationship(RelationshipEnum::BrokeUp);
                $p1->setRelationshipGoal(RelationshipEnum::Dislike);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' said that they consider ' . $p2->getPet()->getName() . ' a friendly rival, but ' . $p2->getPet()->getName() . ' confessed that they\'re not really interested in hanging out at all anymore! :| The two are no longer friends...',
                    $p2->getPet(), $p1->getPet()->getName() . ' suggested being friendly rivals; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out anymore, and said so! The two are no longer friends...'
                );

            case RelationshipEnum::Friend:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 50, 30);

            case RelationshipEnum::FriendlyRival:
                $p1->setCurrentRelationship(RelationshipEnum::FriendlyRival);
                $p2->setCurrentRelationship(RelationshipEnum::FriendlyRival);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' said that they consider ' . $p2->getPet()->getName() . ' a friendly rival; ' . $p2->getPet()->getName() . ' feels the same way! Let the rivalry begin!! >:)',
                    $p2->getPet(), $p1->getPet()->getName() . ' said that they consider ' . $p2->getPet()->getName() . ' a friendly rival; ' . $p2->getPet()->getName() . ' feels the same way! Let the rivalry begin!! >:)',
                    'icons/activity-logs/friend'
                );

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 10, 10);

            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 10, 10);

            case RelationshipEnum::Mate:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 20, 0);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelySuggestingRelationshipChangeAsBFFs(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p1->getRelationshipGoal())
        {
            case RelationshipEnum::Dislike:
                return $this->hangOutPrivatelyFromBFFsToDisliked($p1, $p2);

            case RelationshipEnum::FriendlyRival:
                return $this->hangOutPrivatelyFromBFFsToFriendlyRivals($p1, $p2);

            case RelationshipEnum::Friend:
                return $this->hangOutPrivatelyFromBFFsToFriends($p1, $p2);

            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelyFromBFFsToFWBs($p1, $p2);

            case RelationshipEnum::Mate:
                return $this->hangOutPrivatelyFromBFFsToMates($p1, $p2);

            default:
                throw new \InvalidArgumentException('p1 relationship goal is of an unexpected type, "' . $p1->getRelationshipGoal() . '"');
        }
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromBFFsToFriends(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::Dislike:
                $p1
                    ->setCurrentRelationship(RelationshipEnum::BrokeUp)
                    ->setRelationshipGoal(RelationshipEnum::Dislike)
                ;

                $p2
                    ->setCurrentRelationship(RelationshipEnum::BrokeUp)
                    ->setRelationshipGoal(RelationshipEnum::Dislike)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to just be friends; ' . $p2->getPet()->getName() . ' has had enough of ' . $p1->getPet()->getName() . '\'s nonsense, and breaks up entirely! :(',
                    $p2->getPet(), $p1->getPet()->getName() . ' wanted to just be friends; ' . $p2->getPet()->getName() . ' has had enough of ' . $p1->getPet()->getName() . '\'s nonsense, and breaks up entirely! >:(',
                    'icons/activity-logs/breakup'
                );

            case RelationshipEnum::FriendlyRival:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 45, 40);

            case RelationshipEnum::Friend:
                $p1->setCurrentRelationship(RelationshipEnum::Friend);
                $p2->setCurrentRelationship(RelationshipEnum::Friend);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wants a little more time to theirself; ' . $p2->getPet()->getName() . ' feels the same way. The two are now friends, instead of BFFs.',
                    $p2->getPet(), $p1->getPet()->getName() . ' wants a little more time to theirself; ' . $p2->getPet()->getName() . ' feels the same way. The two are now friends, instead of BFFs.'
                );

            case RelationshipEnum::BFF:
            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 15, 65);

            case RelationshipEnum::Mate:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 10, 50);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromBFFsToFriendlyRivals(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::Dislike:
                $p1
                    ->setCurrentRelationship(RelationshipEnum::BrokeUp)
                    ->setRelationshipGoal(RelationshipEnum::Dislike)
                ;

                $p2
                    ->setCurrentRelationship(RelationshipEnum::BrokeUp)
                    ->setRelationshipGoal(RelationshipEnum::Dislike)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to stop being BFFs, and just be Friendly Rivals; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! :(',
                    $p2->getPet(), $p1->getPet()->getName() . ' wanted to stop being BFFs, and just be Friendly Rivals; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! >:('
                );

            case RelationshipEnum::FriendlyRival:

                $p1->setCurrentRelationship(RelationshipEnum::FriendlyRival);
                $p2->setCurrentRelationship(RelationshipEnum::FriendlyRival);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to turn their friendship with ' . $p2->getPet()->getName() . ' into a Friendly Rivalry; ' . $p2->getPet()->getName() . ' actually feels the same way! BRING IT ON! >:)',
                    $p2->getPet(), $p1->getPet()->getName() . ' wanted to turn their friendship with ' . $p2->getPet()->getName() . ' into a Friendly Rivalry; ' . $p2->getPet()->getName() . ' actually feels the same way! BRING IT ON! >:)',
                    'icons/activity-logs/friend'
                );

            case RelationshipEnum::Friend:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 45, 40);

            case RelationshipEnum::BFF:
            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 35, 20);

            case RelationshipEnum::Mate:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 20, 5);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromBFFsToFWBs(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::Dislike:
                $p1->setCurrentRelationship(RelationshipEnum::BrokeUp);
                $p2->setCurrentRelationship(RelationshipEnum::BrokeUp);
                $p1->setRelationshipGoal(RelationshipEnum::Dislike);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' said that they want to be FWBs with ' . $p2->getPet()->getName() . '; ' . $p2->getPet()->getName() . ' revealed that they don\'t actually like hanging out with ' . $p1->getPet()->getName() . '! They are no longer friends :|',
                    $p2->getPet(), $p1->getPet()->getName() . ' said that they want to be FWBs with ' . $p2->getPet()->getName() . '; ' . $p2->getPet()->getName() . ' revealed that they don\'t actually like hanging out with ' . $p1->getPet()->getName() . '! They are no longer friends :|'
                );

            case RelationshipEnum::FriendlyRival:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 25, 25);

            case RelationshipEnum::Friend:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 40, 40);

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 70, 25);

            case RelationshipEnum::FWB:
                $p1->setCurrentRelationship(RelationshipEnum::FWB);
                $p2->setCurrentRelationship(RelationshipEnum::FWB);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' said that they\'d like to be FWBs with ' . $p2->getPet()->getName() . '; ' . $p2->getPet()->getName() . ' feels the same way ' . $this->loveService->sexyTimesEmoji($p1->getPet(), $p2->getPet()),
                    $p2->getPet(), $p1->getPet()->getName() . ' said that they\'d like to be FWBs with ' . $p2->getPet()->getName() . '; ' . $p2->getPet()->getName() . ' feels the same way ' . $this->loveService->sexyTimesEmoji($p1->getPet(), $p2->getPet()),
                    'icons/activity-logs/friend-cute'
                );

            case RelationshipEnum::Mate:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 50, 45);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromBFFsToDisliked(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::Dislike:
                $p1->setCurrentRelationship(RelationshipEnum::Dislike);
                $p2->setCurrentRelationship(RelationshipEnum::Dislike);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! The feeling is mutual! They are no longer friends >:(',
                    $p2->getPet(), $p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense! The feeling is mutual! They are no longer friends >:('
                );

            case RelationshipEnum::Friend:
            case RelationshipEnum::FriendlyRival:
                $p2->getPet()
                    ->increaseLove(-$this->rng->rngNextInt(4, 8))
                    ->increaseEsteem(-$this->rng->rngNextInt(1, 4))
                ;

                $p1->setCurrentRelationship(RelationshipEnum::BrokeUp);
                $p2
                    ->setRelationshipGoal(RelationshipEnum::Dislike)
                    ->setCurrentRelationship(RelationshipEnum::BrokeUp)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! They are no longer friends >:(',
                    $p2->getPet(), $p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense, and no longer wants to be BFFs, or even friends at all! To be honest, ' . $p2->getPet()->getName() . ' felt the whole BFF thing was a bit much, anyway >:('
                );

            case RelationshipEnum::BFF:
            case RelationshipEnum::FWB:
                $p2->getPet()
                    ->increaseLove(-$this->rng->rngNextInt(12, 18))
                    ->increaseEsteem(-$this->rng->rngNextInt(8, 12))
                ;

                $p1->setCurrentRelationship(RelationshipEnum::BrokeUp);
                $p2
                    ->setRelationshipGoal(RelationshipEnum::Dislike)
                    ->setCurrentRelationship(RelationshipEnum::BrokeUp)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! They are no longer friends >:(',
                    $p2->getPet(), $p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense, and no longer wants to be BFFs, or even friends at all! ' . $p2->getPet()->getName() . ' thought they had a really good friendship going... :(',
                    'icons/activity-logs/breakup'
                );

            case RelationshipEnum::Mate:
                $p2->getPet()
                    ->increaseLove(-$this->rng->rngNextInt(16, 24))
                    ->increaseEsteem(-$this->rng->rngNextInt(12, 16))
                ;

                $p1->setCurrentRelationship(RelationshipEnum::BrokeUp);
                $p2
                    ->setRelationshipGoal(RelationshipEnum::Dislike)
                    ->setCurrentRelationship(RelationshipEnum::BrokeUp)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! They are no longer friends >:(',
                    $p2->getPet(), $p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense, and no longer wants to be BFFs, or friends at all! ' . $p2->getPet()->getName() . ' thought they had a really good friendship going, and had been hoping they might be something more :\'('
                );

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelySuggestingRelationshipChangeAsFWBs(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p1->getRelationshipGoal())
        {
            case RelationshipEnum::Dislike:
                if($this->rng->rngNextInt(1, 4) === 1)
                    return $this->hangOutPrivatelyFromFWBsToFriends($p1, $p2);
                else
                    return $this->hangOutPrivatelyFromFWBsToDisliked($p1, $p2);

            case RelationshipEnum::FriendlyRival:
                return $this->hangOutPrivatelyFromFWBsToFriendlyRivals($p1, $p2);

            case RelationshipEnum::Friend:
                return $this->hangOutPrivatelyFromFWBsToFriends($p1, $p2);

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelyFromFWBsToBFFs($p1, $p2);

            case RelationshipEnum::Mate:
                return $this->hangOutPrivatelyFromFWBsToMates($p1, $p2);

            default:
                throw new \InvalidArgumentException('p1 relationship goal is of an unexpected type, "' . $p1->getRelationshipGoal() . '"');
        }
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromFWBsToMates(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::Dislike:
                return $this->hangOutPrivatelySuggestingMatesWithCompleteRejection($p1, $p2);

            case RelationshipEnum::FriendlyRival:
            case RelationshipEnum::Friend:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 10, 25);

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 20, 40);

            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 30, 60);

            case RelationshipEnum::Mate:
                $p1->setCurrentRelationship(RelationshipEnum::Mate);
                $p2->setCurrentRelationship(RelationshipEnum::Mate);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wants to date ' . $p2->getPet()->getName() . '! ' . $p2->getPet()->getName() . ' feels the same way! The two are now dating! <3',
                    $p2->getPet(), $p1->getPet()->getName() . ' wants to date ' . $p2->getPet()->getName() . '! ' . $p2->getPet()->getName() . ' feels the same way! The two are now dating! <3',
                    'icons/activity-logs/friend-cute'
                );

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromFWBsToBFFs(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::Dislike:
                $p1
                    ->setCurrentRelationship(RelationshipEnum::BrokeUp)
                    ->setRelationshipGoal(RelationshipEnum::Dislike)
                ;

                $p2
                    ->setCurrentRelationship(RelationshipEnum::BrokeUp)
                    ->setRelationshipGoal(RelationshipEnum::Dislike)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to stop being intimate, and just be BFFs; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! :(',
                    $p2->getPet(), $p1->getPet()->getName() . ' wanted to stop being intimate, and just be BFFs; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! >:(',
                    'icons/activity-logs/breakup'
                );

            case RelationshipEnum::FriendlyRival:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 20, 20);

            case RelationshipEnum::Friend:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 40, 40);

            case RelationshipEnum::BFF:
                $p1->setCurrentRelationship(RelationshipEnum::BFF);
                $p2->setCurrentRelationship(RelationshipEnum::BFF);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to stop being intimate, and just be BFFs; ' . $p2->getPet()->getName() . ' actually feels the same way! It\'s a difficult transition, but they\'re both committed to making it work! :)',
                    $p2->getPet(), $p1->getPet()->getName() . ' wanted to stop being intimate, and just be BFFs; ' . $p2->getPet()->getName() . ' actually feels the same way! It\'s a difficult transition, but they\'re both committed to making it work! :)',
                    'icons/activity-logs/friend'
                );

            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 45, 45);

            case RelationshipEnum::Mate:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 35, 35);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromFWBsToFriends(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::Dislike:
                $p1
                    ->setCurrentRelationship(RelationshipEnum::BrokeUp)
                    ->setRelationshipGoal(RelationshipEnum::Dislike)
                ;

                $p2
                    ->setCurrentRelationship(RelationshipEnum::BrokeUp)
                    ->setRelationshipGoal(RelationshipEnum::Dislike)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to stop being intimate, and just be Friends; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! :(',
                    $p2->getPet(), $p1->getPet()->getName() . ' wanted to stop being intimate, and just be Friends; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! >:('
                );

            case RelationshipEnum::FriendlyRival:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 45, 45);

            case RelationshipEnum::Friend:
                $p1->setCurrentRelationship(RelationshipEnum::Friend);
                $p2->setCurrentRelationship(RelationshipEnum::Friend);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to stop being intimate, and just be Friends; ' . $p2->getPet()->getName() . ' actually feels the same way! It\'s a difficult transition, but they\'re both committed to making it work!',
                    $p2->getPet(), $p1->getPet()->getName() . ' wanted to stop being intimate, and just be Friends; ' . $p2->getPet()->getName() . ' actually feels the same way! It\'s a difficult transition, but they\'re both committed to making it work!',
                    'icons/activity-logs/friend'
                );

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 45, 45);

            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 35, 35);

            case RelationshipEnum::Mate:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 30, 15);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromFWBsToFriendlyRivals(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::Dislike:
                $p1
                    ->setCurrentRelationship(RelationshipEnum::BrokeUp)
                    ->setRelationshipGoal(RelationshipEnum::Dislike)
                ;

                $p2
                    ->setCurrentRelationship(RelationshipEnum::BrokeUp)
                    ->setRelationshipGoal(RelationshipEnum::Dislike)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to stop being intimate, and just be Friendly Rivals; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! :(',
                    $p2->getPet(), $p1->getPet()->getName() . ' wanted to stop being intimate, and just be Friendly Rivals; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! >:(',
                    'icons/activity-logs/breakup'
                );

            case RelationshipEnum::FriendlyRival:
                $p1->setCurrentRelationship(RelationshipEnum::FriendlyRival);
                $p2->setCurrentRelationship(RelationshipEnum::FriendlyRival);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to stop being intimate, and just be Friendly Rivals; unexpectedly, ' . $p2->getPet()->getName() . ' actually feels the same way! BRING IT ON! >:)',
                    $p2->getPet(), 'Unexpectedly, ' . $p1->getPet()->getName() . ' wanted to stop being intimate, and just be Friendly Rivals; ' . $p2->getPet()->getName() . ' actually feels the same way! BRING IT ON! >:)',
                    'icons/activity-logs/friend'
                );

            case RelationshipEnum::Friend:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 45, 45);

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 35, 30);

            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 25, 20);

            case RelationshipEnum::Mate:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 20, 5);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromFWBsToDisliked(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::Dislike:
                $p1->setCurrentRelationship(RelationshipEnum::Dislike);
                $p2->setCurrentRelationship(RelationshipEnum::Dislike);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! The feeling is mutual! They are no longer friends >:(',
                    $p2->getPet(), $p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense! The feeling is mutual! They are no longer friends >:('
                );

            case RelationshipEnum::FriendlyRival:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 20, 0);

            case RelationshipEnum::Friend:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 40, 0);

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 30, 0);

            case RelationshipEnum::FWB:
                // negotiate for a less-involved relationship
                $p2->setRelationshipGoal($this->rng->rngNextFromArray([ RelationshipEnum::BFF, RelationshipEnum::Friend, RelationshipEnum::Friend ]));
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 35, 0);

            case RelationshipEnum::Mate:
                $p2->getPet()
                    ->increaseLove(-$this->rng->rngNextInt(16, 24))
                    ->increaseEsteem(-$this->rng->rngNextInt(12, 16))
                ;

                $p1->setCurrentRelationship(RelationshipEnum::BrokeUp);
                $p2
                    ->setRelationshipGoal(RelationshipEnum::Dislike)
                    ->setCurrentRelationship(RelationshipEnum::BrokeUp)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! They are no longer friends >:(',
                    $p2->getPet(), $p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense, and no longer wants to be FWBs, or friends at all! ' . $p2->getPet()->getName() . ' thought they had a really good friendship going, and had been hoping they might be something more :\'(',
                    'icons/activity-logs/breakup'
                );

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelySuggestingRelationshipChangeAsMates(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p1->getRelationshipGoal())
        {
            case RelationshipEnum::Dislike:
                if($this->rng->rngNextInt(1, 4) === 1)
                    return $this->hangOutPrivatelyFromMatesToFriends($p1, $p2);
                else
                    return $this->hangOutPrivatelyFromMatesToDisliked($p1, $p2);

            case RelationshipEnum::FriendlyRival:
                return $this->hangOutPrivatelyFromMatesToFriendlyRivals($p1, $p2);

            case RelationshipEnum::Friend:
                return $this->hangOutPrivatelyFromMatesToFriends($p1, $p2);

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelyFromMatesToBFFs($p1, $p2);

            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelyFromMatesToFWBs($p1, $p2);

            default:
                throw new \InvalidArgumentException('p1 relationship goal is of an unexpected type, "' . $p1->getRelationshipGoal() . '"');
        }
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromMatesToFriendlyRivals(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::Dislike:
                $p1
                    ->setCurrentRelationship(RelationshipEnum::BrokeUp)
                    ->setRelationshipGoal(RelationshipEnum::Dislike)
                ;

                $p2
                    ->setCurrentRelationship(RelationshipEnum::BrokeUp)
                    ->setRelationshipGoal(RelationshipEnum::Dislike)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to just be Friendly Rivals; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! :(',
                    $p2->getPet(), $p1->getPet()->getName() . ' wanted to just be Friendly Rivals; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! >:(',
                    'icons/activity-logs/breakup'
                );

            case RelationshipEnum::FriendlyRival:
                $p1->setCurrentRelationship(RelationshipEnum::FriendlyRival);
                $p2->setCurrentRelationship(RelationshipEnum::FriendlyRival);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to just be Friendly Rivals; unexpectedly, ' . $p2->getPet()->getName() . ' actually feels the same way! Okay, then! BRING IT ON! >:)',
                    $p2->getPet(), 'Unexpectedly, ' . $p1->getPet()->getName() . ' wanted to just be Friendly Rivals; ' . $p2->getPet()->getName() . ' actually feels the same way! Okay, then! BRING IT ON! >:)',
                    'icons/activity-logs/friend'
                );

            case RelationshipEnum::Friend:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 50, 40);

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 40, 25);

            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 30, 10);

            case RelationshipEnum::Mate:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 20, 5);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromMatesToBFFs(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::Dislike:
                $p1
                    ->setCurrentRelationship(RelationshipEnum::BrokeUp)
                    ->setRelationshipGoal(RelationshipEnum::Dislike)
                ;

                $p2
                    ->setCurrentRelationship(RelationshipEnum::BrokeUp)
                    ->setRelationshipGoal(RelationshipEnum::Dislike)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to just be BFFs; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! :\'(',
                    $p2->getPet(), $p1->getPet()->getName() . ' wanted to just be BFFs; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! >:(',
                    'icons/activity-logs/breakup'
                );

            case RelationshipEnum::FriendlyRival:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 20, 20);

            case RelationshipEnum::Friend:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 45, 45);

            case RelationshipEnum::BFF:

                $p1->setCurrentRelationship(RelationshipEnum::BFF);
                $p2->setCurrentRelationship(RelationshipEnum::BFF);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to just be BFFs; ' . $p2->getPet()->getName() . ' actually feels the same way! It\'s a difficult transition, but they\'re both committed to making it work! :)',
                    $p2->getPet(), $p1->getPet()->getName() . ' wanted to just be BFFs; ' . $p2->getPet()->getName() . ' actually feels the same way! It\'s a difficult transition, but they\'re both committed to making it work! :)',
                    'icons/activity-logs/friend'
                );

            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 50, 40);

            case RelationshipEnum::Mate:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 35, 35);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromMatesToFWBs(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::Dislike:
                $p1
                    ->setCurrentRelationship(RelationshipEnum::BrokeUp)
                    ->setRelationshipGoal(RelationshipEnum::Dislike)
                ;

                $p2
                    ->setCurrentRelationship(RelationshipEnum::BrokeUp)
                    ->setRelationshipGoal(RelationshipEnum::Dislike)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to just be FWBs; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! :\'(',
                    $p2->getPet(), $p1->getPet()->getName() . ' wanted to just be FWBs; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! >:(',
                    'icons/activity-logs/breakup'
                );

            case RelationshipEnum::FriendlyRival:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 15, 15);

            case RelationshipEnum::Friend:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 25, 25);

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 35, 35);

            case RelationshipEnum::FWB:
                $p1->setCurrentRelationship(RelationshipEnum::FWB);
                $p2->setCurrentRelationship(RelationshipEnum::FWB);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to just be FWBs; unexpectedly, ' . $p2->getPet()->getName() . ' actually feels the same way!' . $this->loveService->sexyTimesEmoji($p1->getPet(), $p2->getPet()),
                    $p2->getPet(), 'Unexpectedly, ' . $p1->getPet()->getName() . ' wanted to just be FWBs; ' . $p2->getPet()->getName() . ' actually feels the same way!' . $this->loveService->sexyTimesEmoji($p1->getPet(), $p2->getPet()),
                    'icons/activity-logs/friend-cute'
                );

            case RelationshipEnum::Mate:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 35, 35);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromMatesToFriends(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::Dislike:
                $p1
                    ->setCurrentRelationship(RelationshipEnum::BrokeUp)
                    ->setRelationshipGoal(RelationshipEnum::Dislike)
                ;

                $p2
                    ->setCurrentRelationship(RelationshipEnum::BrokeUp)
                    ->setRelationshipGoal(RelationshipEnum::Dislike)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to just be friends; ' . $p2->getPet()->getName() . ' has had enough of ' . $p1->getPet()->getName() . '\'s nonsense, and breaks up entirely! :\'(',
                    $p2->getPet(), $p1->getPet()->getName() . ' wanted to just be friends; ' . $p2->getPet()->getName() . ' has had enough of ' . $p1->getPet()->getName() . '\'s nonsense, and breaks up entirely! >:(',
                    'icons/activity-logs/breakup'
                );

            case RelationshipEnum::FriendlyRival:
            case RelationshipEnum::Friend:
            case RelationshipEnum::BFF:
                $p1->setCurrentRelationship(RelationshipEnum::Friend);
                $p2->setCurrentRelationship(RelationshipEnum::Friend);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to just be friends; after talking for a bit, ' . $p2->getPet()->getName() . ' agrees that that\'d be best... :(',
                    $p2->getPet(), $p1->getPet()->getName() . ' wanted to just be friends; after talking for a bit, ' . $p2->getPet()->getName() . ' agrees that that\'d be best... :('
                );

            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 15, 60);

            case RelationshipEnum::Mate:
                $p2->setRelationshipGoal($this->rng->rngNextFromArray([ RelationshipEnum::FWB, RelationshipEnum::Mate ]));
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 15, 60);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromMatesToDisliked(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::Dislike:
                $p1->setCurrentRelationship(RelationshipEnum::Dislike);
                $p2->setCurrentRelationship(RelationshipEnum::Dislike);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' doesn\'t like ' . $p2->getPet()->getName() . ' anymore. The feeling is mutual! They\'re breaking up! >:(',
                    $p2->getPet(), $p1->getPet()->getName() . ' said they don\'t like ' . $p2->getPet()->getName() . ' anymore. The feeling is mutual! They\'re breaking up! >:('
                );

            case RelationshipEnum::Friend:
            case RelationshipEnum::FriendlyRival:
                $p2->getPet()
                    ->increaseLove(-$this->rng->rngNextInt(12, 18))
                    ->increaseEsteem(-$this->rng->rngNextInt(8, 12))
                ;

                $p1->setCurrentRelationship(RelationshipEnum::BrokeUp);
                $p2
                    ->setRelationshipGoal(RelationshipEnum::Dislike)
                    ->setCurrentRelationship(RelationshipEnum::BrokeUp)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! They are no longer friends >:(',
                    $p2->getPet(), $p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense, and wants to break up! To be honest, ' . $p2->getPet()->getName() . ' felt the whole dating thing was a bit much, anyway >:('
                );

            case RelationshipEnum::BFF:
            case RelationshipEnum::FWB:
            case RelationshipEnum::Mate:
                // negotiate for a less-involved relationship
                $p2->setRelationshipGoal($this->rng->rngNextFromArray([ RelationshipEnum::FWB, RelationshipEnum::BFF, RelationshipEnum::Friend ]));
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 20, 0);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }
}
