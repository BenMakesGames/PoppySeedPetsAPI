<?php
namespace App\Service\PetActivity\Relationship;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetRelationship;
use App\Enum\EnumInvalidValueException;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\RelationshipEnum;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Service\IRandom;
use Doctrine\ORM\EntityManagerInterface;

class RelationshipChangeService
{
    private LoveService $loveService;
    private IRandom $squirrel3;
    private EntityManagerInterface $em;

    public function __construct(
        LoveService $loveService, IRandom $squirrel3, EntityManagerInterface $em
    )
    {
        $this->loveService = $loveService;
        $this->squirrel3 = $squirrel3;
        $this->em = $em;
    }

    /**
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    public function hangOutPrivatelySuggestingRelationshipChange(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p1->getCurrentRelationship())
        {
            case RelationshipEnum::FRIENDLY_RIVAL:
                $logs = $this->hangOutPrivatelySuggestingRelationshipChangeAsFriendlyRival($p1, $p2);
                break;

            case RelationshipEnum::FRIEND:
                $logs = $this->hangOutPrivatelySuggestingRelationshipChangeAsFriends($p1, $p2);
                break;

            case RelationshipEnum::BFF:
                $logs = $this->hangOutPrivatelySuggestingRelationshipChangeAsBFFs($p1, $p2);
                break;

            case RelationshipEnum::FWB:
                $logs = $this->hangOutPrivatelySuggestingRelationshipChangeAsFWBs($p1, $p2);
                break;

            case RelationshipEnum::MATE:
                $logs = $this->hangOutPrivatelySuggestingRelationshipChangeAsMates($p1, $p2);
                break;

            default:
                throw new \Exception('Current relationship is of an unexpected type, "' . $p1->getCurrentRelationship() . '"');
        }

        $p1->setTimeUntilChange();
        $p2->setTimeUntilChange();

        foreach($logs as $log)
            $log->addInterestingness(PetActivityLogInterestingnessEnum::RELATIONSHIP_DISCUSSION);

        return $logs;
    }

    private static function createLogs(EntityManagerInterface $em, Pet $p1, string $p1Message, Pet $p2, string $p2Message, $icon = '')
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
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelySuggestingRelationshipChangeAsFriendlyRival(PetRelationship $p1, PetRelationship $p2): array
    {
        if($p1->getRelationshipGoal() === RelationshipEnum::DISLIKE)
        {
            $p1->setCurrentRelationship(RelationshipEnum::DISLIKE);
            $p2->setCurrentRelationship(RelationshipEnum::DISLIKE);

            if($p2->getRelationshipGoal() === RelationshipEnum::DISLIKE)
            {
                $log2Message = $p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s shennanigans! The feeling is mutual! They are no longer friendly rivals!';
            }
            else
            {
                $p2->setRelationshipGoal(RelationshipEnum::DISLIKE);

                $log2Message = $p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s shennanigans! They don\'t want to be friendly rivals any more! (How rude!)';
            }

            return self::createLogs($this->em,
                $p1->getPet(), $p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s shennanigans! They are no longer friendly rivals.',
                $p2->getPet(), $log2Message
            );
        }

        if($p2->getRelationshipGoal() === RelationshipEnum::DISLIKE)
        {
            $p1
                ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                ->setRelationshipGoal($this->squirrel3->rngNextFromArray([
                    RelationshipEnum::FRIENDLY_RIVAL, RelationshipEnum::DISLIKE, RelationshipEnum::DISLIKE, RelationshipEnum::DISLIKE
                ]))
            ;

            $p2->setCurrentRelationship(RelationshipEnum::BROKE_UP);

            return self::createLogs(
                $this->em,
                $p1->getPet(), $p1->getPet()->getName() . ' wanted to be friends with ' . $p2->getPet()->getName() . ', but ' . $p2->getPet()->getName() . ' apparently wants nothing to do with ' . $p1->getPet()->getName() . ' anymore! :(',
                $p2->getPet(), $p1->getPet()->getName() . ' wanted to be friends; ' . $p2->getPet()->getName() . ' rejected, wanting nothing to do with with ' . $p2->getPet()->getName() . '!'
            );
        }

        $p1->setCurrentRelationship(RelationshipEnum::FRIEND);
        $p2->setCurrentRelationship(RelationshipEnum::FRIEND);

        if($this->squirrel3->rngNextInt(1, 3) === 1)
            $mostly = ' (Well, mostly!)';
        else
            $mostly = '';

        $log1Message = $p1->getRelationshipGoal() === RelationshipEnum::FRIENDLY_RIVAL
            ? $p1->getPet()->getName() . ' wanted to be friends with ' . $p2->getPet()->getName() . '; ' . $p2->getPet()->getName() . ' accepted...'
            : $p1->getPet()->getName() . ' wanted to be friends with ' . $p2->getPet()->getName() . '; ' . $p2->getPet()->getName() . ' happily accepted! No more of this silly rivalry stuff!' . $mostly;

        $log2Message = $p2->getRelationshipGoal() === RelationshipEnum::FRIENDLY_RIVAL
            ? $p1->getPet()->getName() . ' wanted to be friends with ' . $p2->getPet()->getName() . '; they accepted...'
            : $p1->getPet()->getName() . ' wanted to be friends with ' . $p2->getPet()->getName() . '; they happily accepted! No more of this silly rivalry stuff!' . $mostly;

        return self::createLogs(
            $this->em,
            $p1->getPet(), $log1Message,
            $p2->getPet(), $log2Message
        );
    }

    /**
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelySuggestingRelationshipChangeAsFriends(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p1->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                return $this->hangOutPrivatelyFromFriendsToDisliked($p1, $p2);

            case RelationshipEnum::FRIENDLY_RIVAL:
                return $this->hangOutPrivatelyFromFriendsToFriendlyRivals($p1, $p2);

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelyFromFriendsToBFFs($p1, $p2);

            case RelationshipEnum::FWB:
                if($this->squirrel3->rngNextInt(1, 4) === 1)
                    return $this->hangOutPrivatelyFromFriendsToBFFs($p1, $p2);
                else
                    return $this->hangOutPrivatelyFromFriendsToFWBs($p1, $p2);

            case RelationshipEnum::MATE:
                if($this->squirrel3->rngNextInt(1, 4) === 1)
                    return $this->hangOutPrivatelyFromFriendsToMates($p1, $p2);
                else
                    return $this->hangOutPrivatelyFromFriendsToBFFs($p1, $p2);

            default:
                throw new \InvalidArgumentException('p1 relationship goal is of an unexpected type, "' . $p1->getRelationshipGoal() . '"');
        }
    }

    /**
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromFriendsToBFFs(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p2->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p1->setRelationshipGoal(RelationshipEnum::DISLIKE);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' said that they consider ' . $p2->getPet()->getName() . ' a best friend; ' . $p2->getPet()->getName() . ' revealed that they don\'t actually like hanging out with ' . $p1->getPet()->getName() . '! They are no longer friends :(',
                    $p2->getPet(), $p1->getPet()->getName() . ' said that they consider ' . $p2->getPet()->getName() . ' a best friend; ' . $p2->getPet()->getName() . ' revealed that they don\'t actually like hanging out with ' . $p1->getPet()->getName() . '! They are no longer friends :|'
                );

            case RelationshipEnum::FRIEND:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 45, 45);

            case RelationshipEnum::FRIENDLY_RIVAL:
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

            case RelationshipEnum::MATE:
                return $this->hangOutPrivatelyFromBFFsToMates($p2, $p1);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromFriendsToFWBs(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p2->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p1->setRelationshipGoal(RelationshipEnum::DISLIKE);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' said that they want to be FWBs with ' . $p2->getPet()->getName() . '; ' . $p2->getPet()->getName() . ' revealed that they don\'t actually like hanging out with ' . $p1->getPet()->getName() . '! They are no longer friends :(',
                    $p2->getPet(), $p1->getPet()->getName() . ' said that they want to be FWBs with ' . $p2->getPet()->getName() . '; ' . $p2->getPet()->getName() . ' revealed that they don\'t actually like hanging out with ' . $p1->getPet()->getName() . '! They are no longer friends >:(',
                    'icons/activity-logs/breakup'
                );

            case RelationshipEnum::FRIEND:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 60, 25);

            case RelationshipEnum::FRIENDLY_RIVAL:
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

            case RelationshipEnum::MATE:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 65, 25);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelySuggestingMatesWithCompleteRejection(PetRelationship $p1, PetRelationship $p2): array
    {
        $p1
            ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
            ->setRelationshipGoal(RelationshipEnum::DISLIKE)
        ;

        $p2->setCurrentRelationship(RelationshipEnum::BROKE_UP);

        return self::createLogs(
            $this->em,
            $p1->getPet(), $p1->getPet()->getName() . ' wanted to date ' . $p2->getPet()->getName() . ', but ' . $p2->getPet()->getName() . ' revealed that they don\'t actually like hanging out with ' . $p1->getPet()->getName() . '! They are no longer friends :\'(',
            $p2->getPet(), $p1->getPet()->getName() . ' wanted to date ' . $p2->getPet()->getName() . ', but ' . $p2->getPet()->getName() . ' revealed that they don\'t actually like hanging out with ' . $p1->getPet()->getName() . '! They are no longer friends! >:(',
            'icons/activity-logs/breakup'
        );
    }

    /**
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromFriendsToMates(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                return $this->hangOutPrivatelySuggestingMatesWithCompleteRejection($p1, $p2);

            case RelationshipEnum::FRIEND:
                if($this->squirrel3->rngNextInt(1, 4) === 1)
                    return $this->hangOutPrivatelyFromFriendsToBFFs($p1, $p2);
                else
                    return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 25, 45);

            case RelationshipEnum::FRIENDLY_RIVAL:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 10, 10);

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 45, 45);

            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 25, 45);

            case RelationshipEnum::MATE:
                $p1->setCurrentRelationship(RelationshipEnum::MATE);
                $p2->setCurrentRelationship(RelationshipEnum::MATE);

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
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromBFFsToMates(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                return $this->hangOutPrivatelySuggestingMatesWithCompleteRejection($p1, $p2);

            case RelationshipEnum::FRIEND:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p2, $p1, 5, 30);

            case RelationshipEnum::FRIENDLY_RIVAL:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 10, 10);

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 45, 45);

            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 25, 45);

            case RelationshipEnum::MATE:
                $p1->setCurrentRelationship(RelationshipEnum::MATE);
                $p2->setCurrentRelationship(RelationshipEnum::MATE);

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
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @param int $chanceP1ChangesMind
     * @param int $chanceP2ChangesMind
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama(PetRelationship $p1, PetRelationship $p2, int $chanceP1ChangesMind, int $chanceP2ChangesMind)
    {
        $downgradeDescription = [
            RelationshipEnum::DISLIKE => 'break up entirely',
            RelationshipEnum::FRIEND => 'just be friends',
            RelationshipEnum::BFF => 'just be BFFs',
            RelationshipEnum::FWB => 'just be friends, but maybe still, you know, _do stuff_',
            RelationshipEnum::FRIENDLY_RIVAL => 'just be friendly rivals',
            RelationshipEnum::MATE => 'date',
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

            if($this->squirrel3->rngNextInt(1, 4) !== 1)
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

            if($this->squirrel3->rngNextInt(1, 4) !== 1)
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

            $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
            $p2->setCurrentRelationship(RelationshipEnum::BROKE_UP);
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
        PetRelationship $p1, PetRelationship $p2, $chanceP1ChangesMind, $chanceP2ChangesMind
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
                $chanceP1ChangesMind = ceil($chanceP1ChangesMind / 4);

            if($p2->getPet()->hasMerit(MeritEnum::INTROSPECTIVE))
                $chanceP2ChangesMind = ceil($chanceP2ChangesMind / 4);
        }

        $r = $this->squirrel3->rngNextInt(1, 100);

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
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @param $chanceP1ChangesMind
     * @param $chanceP2ChangesMind
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama(PetRelationship $p1, PetRelationship $p2, $chanceP1ChangesMind, $chanceP2ChangesMind)
    {
        $upgradeDescription = [
            RelationshipEnum::DISLIKE => 'break up',
            RelationshipEnum::FRIENDLY_RIVAL => 'be friendly rivals',
            RelationshipEnum::FRIEND => 'be friends',
            RelationshipEnum::BFF => 'be BFFs',
            RelationshipEnum::FWB => 'be FWBs',
            RelationshipEnum::MATE => 'date',
        ];

        $downgradeDescription = [
            RelationshipEnum::DISLIKE => 'break up entirely',
            RelationshipEnum::FRIENDLY_RIVAL => 'just be friendly rivals',
            RelationshipEnum::FRIEND => 'just be friends',
            RelationshipEnum::BFF => 'just be BFFs',
            RelationshipEnum::FWB => 'just be friends, but maybe still, you know, _do stuff_',
            RelationshipEnum::MATE => 'date',
        ];

        $descriptioning = [
            RelationshipEnum::DISLIKE => 'breaking up entirely',
            RelationshipEnum::FRIENDLY_RIVAL => 'being friendly rivals',
            RelationshipEnum::FRIEND => 'being friends',
            RelationshipEnum::BFF => 'being BFFs',
            RelationshipEnum::FWB => 'being FWBs',
            RelationshipEnum::MATE => 'dating',
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

            if($this->squirrel3->rngNextInt(1, 3) !== 1)
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

            if($this->squirrel3->rngNextInt(1, 3) !== 1)
                $p2->setRelationshipGoal($p1->getRelationshipGoal());
        }
        else // break up
        {
            $message = $p1->getPet()->getName() . ' wanted to ' . $upgradeDescription[$p1->getRelationshipGoal()] . ', but ' . $p2->getPet()->getName() . ' doesn\'t want that. After arguing for a while, the two broke up entirely! :\'(';
            $tags[] = 'Break-up';
            $icon = 'icons/activity-logs/breakup';

            $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
            $p2->setCurrentRelationship(RelationshipEnum::BROKE_UP);
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
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromFriendsToDisliked(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $p1->setCurrentRelationship(RelationshipEnum::DISLIKE);
                $p2->setCurrentRelationship(RelationshipEnum::DISLIKE);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! The feeling is mutual! They are no longer friends >:(',
                    $p2->getPet(), $p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense! The feeling is mutual! They are no longer friends >:('
                );

            case RelationshipEnum::FRIEND:
                $p2->getPet()
                    ->increaseLove(-$this->squirrel3->rngNextInt(12, 18))
                    ->increaseEsteem(-$this->squirrel3->rngNextInt(8, 12))
                ;

                $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p2
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! They are no longer friends >:(',
                    $p2->getPet(), $p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense, and no longer wants to be friends! ' . $p2->getPet()->getName() . ' thought they had a really good friendship going... :(',
                    'icons/activity-logs/breakup'
                );

            case RelationshipEnum::FRIENDLY_RIVAL:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 35, 0);

            case RelationshipEnum::BFF:
            case RelationshipEnum::FWB:
            case RelationshipEnum::MATE:
                $p2->getPet()
                    ->increaseLove(-$this->squirrel3->rngNextInt(16, 24))
                    ->increaseEsteem(-$this->squirrel3->rngNextInt(12, 16))
                ;

                $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p2
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
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
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromFriendsToFriendlyRivals(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p2->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p1->setRelationshipGoal(RelationshipEnum::DISLIKE);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' said that they consider ' . $p2->getPet()->getName() . ' a friendly rival, but ' . $p2->getPet()->getName() . ' confessed that they\'re not really interested in hanging out at all anymore! :| The two are no longer friends...',
                    $p2->getPet(), $p1->getPet()->getName() . ' suggested being friendly rivals; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out anymore, and said so! The two are no longer friends...'
                );

            case RelationshipEnum::FRIEND:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 50, 30);

            case RelationshipEnum::FRIENDLY_RIVAL:
                $p1->setCurrentRelationship(RelationshipEnum::FRIENDLY_RIVAL);
                $p2->setCurrentRelationship(RelationshipEnum::FRIENDLY_RIVAL);

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

            case RelationshipEnum::MATE:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 20, 0);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelySuggestingRelationshipChangeAsBFFs(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p1->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                return $this->hangOutPrivatelyFromBFFsToDisliked($p1, $p2);

            case RelationshipEnum::FRIENDLY_RIVAL:
                return $this->hangOutPrivatelyFromBFFsToFriendlyRivals($p1, $p2);

            case RelationshipEnum::FRIEND:
                return $this->hangOutPrivatelyFromBFFsToFriends($p1, $p2);

            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelyFromBFFsToFWBs($p1, $p2);

            case RelationshipEnum::MATE:
                return $this->hangOutPrivatelyFromBFFsToMates($p1, $p2);

            default:
                throw new \InvalidArgumentException('p1 relationship goal is of an unexpected type, "' . $p1->getRelationshipGoal() . '"');
        }
    }

    /**
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromBFFsToFriends(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $p1
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                $p2
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to just be friends; ' . $p2->getPet()->getName() . ' has had enough of ' . $p1->getPet()->getName() . '\'s nonsense, and breaks up entirely! :(',
                    $p2->getPet(), $p1->getPet()->getName() . ' wanted to just be friends; ' . $p2->getPet()->getName() . ' has had enough of ' . $p1->getPet()->getName() . '\'s nonsense, and breaks up entirely! >:(',
                    'icons/activity-logs/breakup'
                );

            case RelationshipEnum::FRIENDLY_RIVAL:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 45, 40);

            case RelationshipEnum::FRIEND:
                $p1->setCurrentRelationship(RelationshipEnum::FRIEND);
                $p2->setCurrentRelationship(RelationshipEnum::FRIEND);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wants a little more time to theirself; ' . $p2->getPet()->getName() . ' feels the same way. The two are now friends, instead of BFFs.',
                    $p2->getPet(), $p1->getPet()->getName() . ' wants a little more time to theirself; ' . $p2->getPet()->getName() . ' feels the same way. The two are now friends, instead of BFFs.'
                );

            case RelationshipEnum::BFF:
            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 15, 65);

            case RelationshipEnum::MATE:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 10, 50);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromBFFsToFriendlyRivals(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $p1
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                $p2
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to stop being BFFs, and just be Friendly Rivals; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! :(',
                    $p2->getPet(), $p1->getPet()->getName() . ' wanted to stop being BFFs, and just be Friendly Rivals; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! >:('
                );

            case RelationshipEnum::FRIENDLY_RIVAL:

                $p1->setCurrentRelationship(RelationshipEnum::FRIENDLY_RIVAL);
                $p2->setCurrentRelationship(RelationshipEnum::FRIENDLY_RIVAL);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to turn their friendship with ' . $p2->getPet()->getName() . ' into a Friendly Rivalry; ' . $p2->getPet()->getName() . ' actually feels the same way! BRING IT ON! >:)',
                    $p2->getPet(), $p1->getPet()->getName() . ' wanted to turn their friendship with ' . $p2->getPet()->getName() . ' into a Friendly Rivalry; ' . $p2->getPet()->getName() . ' actually feels the same way! BRING IT ON! >:)',
                    'icons/activity-logs/friend'
                );

            case RelationshipEnum::FRIEND:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 45, 40);

            case RelationshipEnum::BFF:
            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 35, 20);

            case RelationshipEnum::MATE:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 20, 5);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromBFFsToFWBs(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p2->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p1->setRelationshipGoal(RelationshipEnum::DISLIKE);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' said that they want to be FWBs with ' . $p2->getPet()->getName() . '; ' . $p2->getPet()->getName() . ' revealed that they don\'t actually like hanging out with ' . $p1->getPet()->getName() . '! They are no longer friends :|',
                    $p2->getPet(), $p1->getPet()->getName() . ' said that they want to be FWBs with ' . $p2->getPet()->getName() . '; ' . $p2->getPet()->getName() . ' revealed that they don\'t actually like hanging out with ' . $p1->getPet()->getName() . '! They are no longer friends :|'
                );

            case RelationshipEnum::FRIENDLY_RIVAL:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 25, 25);

            case RelationshipEnum::FRIEND:
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

            case RelationshipEnum::MATE:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 50, 45);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromBFFsToDisliked(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $p1->setCurrentRelationship(RelationshipEnum::DISLIKE);
                $p2->setCurrentRelationship(RelationshipEnum::DISLIKE);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! The feeling is mutual! They are no longer friends >:(',
                    $p2->getPet(), $p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense! The feeling is mutual! They are no longer friends >:('
                );

            case RelationshipEnum::FRIEND:
            case RelationshipEnum::FRIENDLY_RIVAL:
                $p2->getPet()
                    ->increaseLove(-$this->squirrel3->rngNextInt(4, 8))
                    ->increaseEsteem(-$this->squirrel3->rngNextInt(1, 4))
                ;

                $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p2
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! They are no longer friends >:(',
                    $p2->getPet(), $p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense, and no longer wants to be BFFs, or even friends at all! To be honest, ' . $p2->getPet()->getName() . ' felt the whole BFF thing was a bit much, anyway >:('
                );

            case RelationshipEnum::BFF:
            case RelationshipEnum::FWB:
                $p2->getPet()
                    ->increaseLove(-$this->squirrel3->rngNextInt(12, 18))
                    ->increaseEsteem(-$this->squirrel3->rngNextInt(8, 12))
                ;

                $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p2
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! They are no longer friends >:(',
                    $p2->getPet(), $p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense, and no longer wants to be BFFs, or even friends at all! ' . $p2->getPet()->getName() . ' thought they had a really good friendship going... :(',
                    'icons/activity-logs/breakup'
                );

            case RelationshipEnum::MATE:
                $p2->getPet()
                    ->increaseLove(-$this->squirrel3->rngNextInt(16, 24))
                    ->increaseEsteem(-$this->squirrel3->rngNextInt(12, 16))
                ;

                $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p2
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
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
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelySuggestingRelationshipChangeAsFWBs(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p1->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                if($this->squirrel3->rngNextInt(1, 4) === 1)
                    return $this->hangOutPrivatelyFromFWBsToFriends($p1, $p2);
                else
                    return $this->hangOutPrivatelyFromFWBsToDisliked($p1, $p2);

            case RelationshipEnum::FRIENDLY_RIVAL:
                return $this->hangOutPrivatelyFromFWBsToFriendlyRivals($p1, $p2);

            case RelationshipEnum::FRIEND:
                return $this->hangOutPrivatelyFromFWBsToFriends($p1, $p2);

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelyFromFWBsToBFFs($p1, $p2);

            case RelationshipEnum::MATE:
                return $this->hangOutPrivatelyFromFWBsToMates($p1, $p2);

            default:
                throw new \InvalidArgumentException('p1 relationship goal is of an unexpected type, "' . $p1->getRelationshipGoal() . '"');
        }
    }

    /**
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromFWBsToMates(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                return $this->hangOutPrivatelySuggestingMatesWithCompleteRejection($p1, $p2);

            case RelationshipEnum::FRIENDLY_RIVAL:
            case RelationshipEnum::FRIEND:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 10, 25);

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 20, 40);

            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 30, 60);

            case RelationshipEnum::MATE:
                $p1->setCurrentRelationship(RelationshipEnum::MATE);
                $p2->setCurrentRelationship(RelationshipEnum::MATE);

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
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromFWBsToBFFs(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $p1
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                $p2
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to stop being intimate, and just be BFFs; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! :(',
                    $p2->getPet(), $p1->getPet()->getName() . ' wanted to stop being intimate, and just be BFFs; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! >:(',
                    'icons/activity-logs/breakup'
                );

            case RelationshipEnum::FRIENDLY_RIVAL:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 20, 20);

            case RelationshipEnum::FRIEND:
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

            case RelationshipEnum::MATE:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 35, 35);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromFWBsToFriends(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $p1
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                $p2
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to stop being intimate, and just be Friends; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! :(',
                    $p2->getPet(), $p1->getPet()->getName() . ' wanted to stop being intimate, and just be Friends; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! >:('
                );

            case RelationshipEnum::FRIENDLY_RIVAL:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 45, 45);

            case RelationshipEnum::FRIEND:
                $p1->setCurrentRelationship(RelationshipEnum::FRIEND);
                $p2->setCurrentRelationship(RelationshipEnum::FRIEND);

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

            case RelationshipEnum::MATE:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 30, 15);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromFWBsToFriendlyRivals(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $p1
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                $p2
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to stop being intimate, and just be Friendly Rivals; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! :(',
                    $p2->getPet(), $p1->getPet()->getName() . ' wanted to stop being intimate, and just be Friendly Rivals; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! >:(',
                    'icons/activity-logs/breakup'
                );

            case RelationshipEnum::FRIENDLY_RIVAL:
                $p1->setCurrentRelationship(RelationshipEnum::FRIENDLY_RIVAL);
                $p2->setCurrentRelationship(RelationshipEnum::FRIENDLY_RIVAL);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to stop being intimate, and just be Friendly Rivals; unexpectedly, ' . $p2->getPet()->getName() . ' actually feels the same way! BRING IT ON! >:)',
                    $p2->getPet(), 'Unexpectedly, ' . $p1->getPet()->getName() . ' wanted to stop being intimate, and just be Friendly Rivals; ' . $p2->getPet()->getName() . ' actually feels the same way! BRING IT ON! >:)',
                    'icons/activity-logs/friend'
                );

            case RelationshipEnum::FRIEND:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 45, 45);

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 35, 30);

            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 25, 20);

            case RelationshipEnum::MATE:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 20, 5);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromFWBsToDisliked(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $p1->setCurrentRelationship(RelationshipEnum::DISLIKE);
                $p2->setCurrentRelationship(RelationshipEnum::DISLIKE);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! The feeling is mutual! They are no longer friends >:(',
                    $p2->getPet(), $p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense! The feeling is mutual! They are no longer friends >:('
                );

            case RelationshipEnum::FRIENDLY_RIVAL:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 20, 0);

            case RelationshipEnum::FRIEND:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 40, 0);

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 30, 0);

            case RelationshipEnum::FWB:
                // negotiate for a less-involved relationship
                $p2->setRelationshipGoal($this->squirrel3->rngNextFromArray([ RelationshipEnum::BFF, RelationshipEnum::FRIEND, RelationshipEnum::FRIEND ]));
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 35, 0);

            case RelationshipEnum::MATE:
                $p2->getPet()
                    ->increaseLove(-$this->squirrel3->rngNextInt(16, 24))
                    ->increaseEsteem(-$this->squirrel3->rngNextInt(12, 16))
                ;

                $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p2
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
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
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelySuggestingRelationshipChangeAsMates(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p1->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                if($this->squirrel3->rngNextInt(1, 4) === 1)
                    return $this->hangOutPrivatelyFromMatesToFriends($p1, $p2);
                else
                    return $this->hangOutPrivatelyFromMatesToDisliked($p1, $p2);

            case RelationshipEnum::FRIENDLY_RIVAL:
                return $this->hangOutPrivatelyFromMatesToFriendlyRivals($p1, $p2);

            case RelationshipEnum::FRIEND:
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
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromMatesToFriendlyRivals(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $p1
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                $p2
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to just be Friendly Rivals; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! :(',
                    $p2->getPet(), $p1->getPet()->getName() . ' wanted to just be Friendly Rivals; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! >:(',
                    'icons/activity-logs/breakup'
                );

            case RelationshipEnum::FRIENDLY_RIVAL:
                $p1->setCurrentRelationship(RelationshipEnum::FRIENDLY_RIVAL);
                $p2->setCurrentRelationship(RelationshipEnum::FRIENDLY_RIVAL);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to just be Friendly Rivals; unexpectedly, ' . $p2->getPet()->getName() . ' actually feels the same way! Okay, then! BRING IT ON! >:)',
                    $p2->getPet(), 'Unexpectedly, ' . $p1->getPet()->getName() . ' wanted to just be Friendly Rivals; ' . $p2->getPet()->getName() . ' actually feels the same way! Okay, then! BRING IT ON! >:)',
                    'icons/activity-logs/friend'
                );

            case RelationshipEnum::FRIEND:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 50, 40);

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 40, 25);

            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 30, 10);

            case RelationshipEnum::MATE:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 20, 5);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromMatesToBFFs(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $p1
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                $p2
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to just be BFFs; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! :\'(',
                    $p2->getPet(), $p1->getPet()->getName() . ' wanted to just be BFFs; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! >:(',
                    'icons/activity-logs/breakup'
                );

            case RelationshipEnum::FRIENDLY_RIVAL:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 20, 20);

            case RelationshipEnum::FRIEND:
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

            case RelationshipEnum::MATE:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 35, 35);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromMatesToFWBs(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $p1
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                $p2
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to just be FWBs; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! :\'(',
                    $p2->getPet(), $p1->getPet()->getName() . ' wanted to just be FWBs; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! >:(',
                    'icons/activity-logs/breakup'
                );

            case RelationshipEnum::FRIENDLY_RIVAL:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 15, 15);

            case RelationshipEnum::FRIEND:
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

            case RelationshipEnum::MATE:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 35, 35);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromMatesToFriends(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $p1
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                $p2
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to just be friends; ' . $p2->getPet()->getName() . ' has had enough of ' . $p1->getPet()->getName() . '\'s nonsense, and breaks up entirely! :\'(',
                    $p2->getPet(), $p1->getPet()->getName() . ' wanted to just be friends; ' . $p2->getPet()->getName() . ' has had enough of ' . $p1->getPet()->getName() . '\'s nonsense, and breaks up entirely! >:(',
                    'icons/activity-logs/breakup'
                );

            case RelationshipEnum::FRIENDLY_RIVAL:
            case RelationshipEnum::FRIEND:
            case RelationshipEnum::BFF:
                $p1->setCurrentRelationship(RelationshipEnum::FRIEND);
                $p2->setCurrentRelationship(RelationshipEnum::FRIEND);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' wanted to just be friends; after talking for a bit, ' . $p2->getPet()->getName() . ' agrees that that\'d be best... :(',
                    $p2->getPet(), $p1->getPet()->getName() . ' wanted to just be friends; after talking for a bit, ' . $p2->getPet()->getName() . ' agrees that that\'d be best... :('
                );

            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 15, 60);

            case RelationshipEnum::MATE:
                $p2->setRelationshipGoal($this->squirrel3->rngNextFromArray([ RelationshipEnum::FWB, RelationshipEnum::MATE ]));
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 15, 60);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }

    /**
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    private function hangOutPrivatelyFromMatesToDisliked(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $p1->setCurrentRelationship(RelationshipEnum::DISLIKE);
                $p2->setCurrentRelationship(RelationshipEnum::DISLIKE);

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' doesn\'t like ' . $p2->getPet()->getName() . ' anymore. The feeling is mutual! They\'re breaking up! >:(',
                    $p2->getPet(), $p1->getPet()->getName() . ' said they don\'t like ' . $p2->getPet()->getName() . ' anymore. The feeling is mutual! They\'re breaking up! >:('
                );

            case RelationshipEnum::FRIEND:
            case RelationshipEnum::FRIENDLY_RIVAL:
                $p2->getPet()
                    ->increaseLove(-$this->squirrel3->rngNextInt(12, 18))
                    ->increaseEsteem(-$this->squirrel3->rngNextInt(8, 12))
                ;

                $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p2
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                ;

                return self::createLogs(
                    $this->em,
                    $p1->getPet(), $p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! They are no longer friends >:(',
                    $p2->getPet(), $p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense, and wants to break up! To be honest, ' . $p2->getPet()->getName() . ' felt the whole dating thing was a bit much, anyway >:('
                );

            case RelationshipEnum::BFF:
            case RelationshipEnum::FWB:
            case RelationshipEnum::MATE:
                // negotiate for a less-involved relationship
                $p2->setRelationshipGoal($this->squirrel3->rngNextFromArray([ RelationshipEnum::FWB, RelationshipEnum::BFF, RelationshipEnum::FRIEND ]));
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 20, 0);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }
    }
}
