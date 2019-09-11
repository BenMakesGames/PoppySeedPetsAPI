<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetRelationship;
use App\Enum\RelationshipEnum;
use App\Functions\ArrayFunctions;
use App\Repository\PetRelationshipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;

class PetRelationshipService
{
    private $petRelationshipRepository;
    private $em;
    private $responseService;

    public function __construct(
        PetRelationshipRepository $petRelationshipRepository, EntityManagerInterface $em, ResponseService $responseService
    )
    {
        $this->petRelationshipRepository = $petRelationshipRepository;
        $this->em = $em;
        $this->responseService = $responseService;
    }

    /**
     * @param Pet[] $pets
     */
    public function groupGathering($pets, string $hangOutDescription, string $enemyDescription, string $meetSummary, string $meetDescription, int $meetChance = 2)
    {
        for($i = 0; $i < count($pets) - 1; $i++)
        {
            for($j = $i + 1; $j < count($pets); $j++)
            {
                $this->seeAtGroupGathering($pets[$i], $pets[$j], $hangOutDescription, $enemyDescription, $meetSummary, $meetDescription, $meetChance);
            }
        }
    }

    public function seeAtGroupGathering(Pet $p1, Pet $p2, string $hangOutDescription, string $enemyDescription, string $meetSummary, string $meetDescription, int $meetChance = 5)
    {
        if($p1->getId() === $p2->getId()) return;

        $p1Relationships = $p1->getRelationshipWith($p2);

        if($p1Relationships)
            $this->hangOutPublicly($p1Relationships, $p2->getRelationshipWith($p1), $hangOutDescription, $enemyDescription);
        else if(mt_rand(1, 100 + ($p1->getRelationshipCount() + $p2->getRelationshipCount()) * 10) <= $meetChance)
            $this->introducePets($p1, $p2, $meetSummary, $meetDescription);
    }

    public function meetRoommate(Pet $pet, Pet $otherPet)
    {
        $relationship = $pet->getRelationshipWith($otherPet);

        if($relationship === null)
        {
            $this->introducePets(
                $pet,
                $otherPet,
                'Met at ' . $pet->getOwner()->getName() . '\'s house; they\'re roomies!',
                '%p1% met their new roommate, %p2%.'
            );
        }
        else
        {
            $whatASurprise = mt_rand(1, 10) === 1 ? 'Quelle surprise!' : 'What a surprise!';

            $this->responseService->createActivityLog($pet, $pet->getName() . ' and ' . $otherPet->getName() . ' are living together, now! ' . $whatASurprise, 'icons/activity-logs/friend');
            $this->responseService->createActivityLog($otherPet, $otherPet->getName() . ' and ' . $pet->getName() . ' are living together, now! ' . $whatASurprise, 'icons/activity-logs/friend');
        }

        return $relationship;

    }

    /**
     * @return PetRelationship[]
     */
    public function introducePets(Pet $pet, Pet $otherPet, string $howMetSummary, string $howMetDescription): array
    {
        $r = \mt_rand(1, 100);

        if($r === 1)
        {
            $initialRelationship = RelationshipEnum::FWB;
            $possibleRelationships = [
                RelationshipEnum::FRIEND,
                RelationshipEnum::BFF,
                RelationshipEnum::FWB,
                RelationshipEnum::FWB,
                RelationshipEnum::FWB,
                RelationshipEnum::FWB,
                RelationshipEnum::MATE,
                RelationshipEnum::MATE,
                RelationshipEnum::MATE,
            ];
        }
        else if($r <= 5)
        {
            $initialRelationship = RelationshipEnum::BFF;
            $possibleRelationships = [
                RelationshipEnum::BFF,
                RelationshipEnum::BFF,
                RelationshipEnum::BFF,
                RelationshipEnum::BFF,
                RelationshipEnum::BFF,
                RelationshipEnum::FWB,
                RelationshipEnum::MATE,
                RelationshipEnum::MATE
            ];
        }
        else if($r <= 15)
        {
            $initialRelationship = RelationshipEnum::DISLIKE;
            $possibleRelationships = [ RelationshipEnum::DISLIKE, RelationshipEnum::DISLIKE, RelationshipEnum::DISLIKE, RelationshipEnum::FRIENDLY_RIVAL ];
        }
        else
        {
            $initialRelationship = RelationshipEnum::FRIEND;
            $possibleRelationships = [
                RelationshipEnum::FRIEND,
                RelationshipEnum::FRIEND,
                RelationshipEnum::BFF,
                RelationshipEnum::BFF,
                RelationshipEnum::FRIENDLY_RIVAL,
                RelationshipEnum::FWB,
                RelationshipEnum::MATE,
                RelationshipEnum::MATE,
                RelationshipEnum::MATE,
            ];
        }

        // pet
        $petRelationship = (new PetRelationship())
            ->setPet($pet)
            ->setRelationship($otherPet)
            ->setMetDescription($howMetSummary)
            ->setCurrentRelationship($initialRelationship)
            ->setRelationshipGoal(ArrayFunctions::pick_one($possibleRelationships))
        ;

        $this->em->persist($petRelationship);

        $meetDescription = str_replace([ '%p1%', '%p2%'], [ $pet->getName(), $otherPet->getName() ], $howMetDescription);

        if($petRelationship->getCurrentRelationship() === RelationshipEnum::DISLIKE)
            $this->responseService->createActivityLog($pet, $meetDescription . ' They didn\'t really get along, though...', 'icons/activity-logs/enemy');
        else if($petRelationship->getCurrentRelationship() === RelationshipEnum::FWB || $petRelationship->getRelationshipGoal() === RelationshipEnum::FWB || $petRelationship->getRelationshipGoal() === RelationshipEnum::MATE)
            $this->responseService->createActivityLog($pet, $meetDescription . ' (And what a cutie!)', 'icons/activity-logs/friend-cute');
        else
            $this->responseService->createActivityLog($pet, $meetDescription, 'icons/activity-logs/friend');

        // other pet
        $otherPetRelationship = (new PetRelationship())
            ->setPet($otherPet)
            ->setRelationship($pet)
            ->setMetDescription($howMetSummary)
            ->setCurrentRelationship($initialRelationship)
            ->setRelationshipGoal(ArrayFunctions::pick_one($possibleRelationships))
        ;

        $this->em->persist($otherPetRelationship);

        $meetDescription = str_replace([ '%p1%', '%p2%'], [ $otherPet->getName(), $pet->getName() ], $howMetDescription);

        if($petRelationship->getCurrentRelationship() === RelationshipEnum::DISLIKE)
            $this->responseService->createActivityLog($otherPet, $meetDescription . ' They didn\'t really get along, though...', 'icons/activity-logs/enemy');
        else if($otherPetRelationship->getCurrentRelationship() === RelationshipEnum::FWB || $otherPetRelationship->getRelationshipGoal() === RelationshipEnum::FWB || $otherPetRelationship->getRelationshipGoal() === RelationshipEnum::MATE)
            $this->responseService->createActivityLog($otherPet, $meetDescription . ' (And what a cutie!)', 'icons/activity-logs/friend-cute');
        else
            $this->responseService->createActivityLog($otherPet, $meetDescription, 'icons/activity-logs/friend');

        return [ $petRelationship, $otherPetRelationship ];
    }

    public function hangOutPublicly(PetRelationship $p1, PetRelationship $p2, string $hangOutDescription, string $enemyDescription)
    {
        if($p1->getCurrentRelationship() === RelationshipEnum::DISLIKE || $p1->getCurrentRelationship() === RelationshipEnum::BROKE_UP)
        {
            $p1Description = str_replace([ '%p1%', '%p2%' ], [ $p1->getPet()->getName(), $p2->getPet()->getName() ], $enemyDescription);
            $p2Description = str_replace([ '%p1%', '%p2%' ], [ $p2->getPet()->getName(), $p1->getPet()->getName() ], $enemyDescription);
        }
        else
        {
            $p1Description = str_replace([ '%p1%', '%p2%' ], [ $p1->getPet()->getName(), $p2->getPet()->getName() ], $hangOutDescription);
            $p2Description = str_replace([ '%p1%', '%p2%' ], [ $p2->getPet()->getName(), $p1->getPet()->getName() ], $hangOutDescription);
        }

        $this->responseService->createActivityLog($p1->getPet(), $p1Description, '');
        $this->responseService->createActivityLog($p2->getPet(), $p2Description, '');
    }

    /**
     * @return PetActivityLog[]
     */
    public function hangOutPrivately(PetRelationship $p1, PetRelationship $p2): array
    {
        if($p1->wantsDifferentRelationship() && $p1->getTimeUntilChange() <= 1)
        {
            return $this->hangOutPrivatelySuggestingRelationshipChange($p1, $p2);
        }
        else if($p2->wantsDifferentRelationship() && $p2->getTimeUntilChange() <= 1)
        {
            return $this->hangOutPrivatelySuggestingRelationshipChange($p2, $p1);
        }
        else
        {
            $p1->decrementTimeUntilChange();
            $p2->decrementTimeUntilChange();

            switch($p1->getCurrentRelationship())
            {
                case RelationshipEnum::BROKE_UP:
                case RelationshipEnum::DISLIKE:
                    throw new \Exception('Pets which do not like each other should not be hanging out privately! Some kind of bug has occurred!');

                case RelationshipEnum::FRIENDLY_RIVAL:
                    return $this->hangOutPrivatelyAsFriendlyRivals($p1, $p2);

                case RelationshipEnum::FRIEND:
                    return $this->hangOutPrivatelyAsFriends($p1, $p2);

                case RelationshipEnum::BFF:
                    return $this->hangOutPrivatelyAsBFFs($p1, $p2);

                case RelationshipEnum::FWB:
                    return $this->hangOutPrivatelyAsFWBs($p1, $p2);

                case RelationshipEnum::MATE:
                    return $this->hangOutPrivatelyAsMates($p1, $p2);
            }
        }

        return [ $p1Log, $p2Log ];
    }

    /**
     * @return PetActivityLog[]
     */
    private function hangOutPrivatelyAsFriendlyRivals(PetRelationship $p1, PetRelationship $p2): array
    {

    }

    /**
     * @return PetActivityLog[]
     */
    private function hangOutPrivatelyAsFriends(PetRelationship $p1, PetRelationship $p2): array
    {
        if(mt_rand(1, 5) === 1)
            return $this->hangOutPrivatelyAsFriendlyRivals($p1, $p2);


    }

    /**
     * @return PetActivityLog[]
     */
    private function hangOutPrivatelyAsBFFs(PetRelationship $p1, PetRelationship $p2): array
    {
        return $this->hangOutPrivatelyAsFriends($p1, $p2);
    }

    /**
     * @return PetActivityLog[]
     */
    private function hangOutPrivatelyAsFWBs(PetRelationship $p1, PetRelationship $p2): array
    {
        if(mt_rand(1, 2) === 1)
            return $this->hangOutPrivatelyAsBFFs($p1, $p2);

        $p1->getPet()
            ->increaseSafety(mt_rand(2, 4))
            ->increaseLove(mt_rand(2, 4))
            ->increaseEsteem(mt_rand(1, 2))
        ;

        $p2->getPet()
            ->increaseSafety(mt_rand(2, 4))
            ->increaseLove(mt_rand(2, 4))
            ->increaseEsteem(mt_rand(1, 2))
        ;

        $p1Log = $this->responseService->createActivityLog($p1->getPet(), $p1->getPet()->getName() . ' hung out with ' . $p2->getPet()->getName() . '. They had fun! ;)', '');
        $p2Log = $this->responseService->createActivityLog($p2->getPet(), $p1->getPet()->getName() . ' hung out with ' . $p2->getPet()->getName() . '. They had fun! ;)', '');

        return [ $p1Log, $p2Log ];
    }

    /**
     * @return PetActivityLog[]
     */
    private function hangOutPrivatelyAsMates(PetRelationship $p1, PetRelationship $p2): array
    {
        if(mt_rand(1, 2) === 1)
            return $this->hangOutPrivatelyAsBFFs($p1, $p2);
        else
            return $this->hangOutPrivatelyAsFWBs($p1, $p2);
    }

    /**
     * @return PetActivityLog[]
     */
    private function hangOutPrivatelySuggestingRelationshipChange(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p1->getCurrentRelationship())
        {
            case RelationshipEnum::FRIENDLY_RIVAL:
                return $this->hangOutPrivatelySuggestingRelationshipChangeAsFriendlyRival($p1, $p2);
            case RelationshipEnum::FRIEND:
                return $this->hangOutPrivatelySuggestingRelationshipChangeAsFriends($p1, $p2);
            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelySuggestingRelationshipChangeAsBFFs($p1, $p2);
            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelySuggestingRelationshipChangeAsFWBs($p1, $p2);
            case RelationshipEnum::MATE:
                return $this->hangOutPrivatelySuggestingRelationshipChangeAsMates($p1, $p2);
            default:
                throw new \Exception('Current relationship is of an unexpected type, "' . $p1->getCurrentRelationship() . '"');
        }
    }

    private function hangOutPrivatelySuggestingRelationshipChangeAsFriendlyRival(PetRelationship $p1, PetRelationship $p2): array
    {
        if($p1->getRelationshipGoal() === RelationshipEnum::DISLIKE)
        {
            $p1->setCurrentRelationship(RelationshipEnum::DISLIKE);
            $p2->setCurrentRelationship(RelationshipEnum::DISLIKE);

            $log1 = $this->responseService->createActivityLog($p1->getPet(), $p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s shennanigans! They are no longer friendly rivals.', '');

            if($p2->getRelationshipGoal() === RelationshipEnum::DISLIKE)
            {
                $log2 = $this->responseService->createActivityLog($p2->getPet(), $p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s shennanigans! The feeling is mutual! They are no longer friendly rivals!', '');
            }
            else
            {
                $p2->setRelationshipGoal(RelationshipEnum::DISLIKE);

                $log2 = $this->responseService->createActivityLog($p2->getPet(), $p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s shennanigans! They don\'t want to be friendly rivals any more! (How rude!)', '');
            }
        }
        else
        {
            if($p2->getRelationshipGoal() === RelationshipEnum::DISLIKE)
            {
                $p1
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(ArrayFunctions::pick_one([
                        RelationshipEnum::FRIENDLY_RIVAL, RelationshipEnum::DISLIKE, RelationshipEnum::DISLIKE, RelationshipEnum::DISLIKE
                    ]))
                ;

                $p2->setCurrentRelationship(RelationshipEnum::BROKE_UP);

                $log1 = $this->responseService->createActivityLog($p1->getPet(), $p1->getPet()->getName() . ' wanted to be friends with ' . $p2->getPet()->getName() . ', but ' . $p2->getPet()->getName() . ' apparently wants nothing to do with ' . $p1->getPet()->getName() . ' anymore! :(', '');
                $log2 = $this->responseService->createActivityLog($p2->getPet(), $p1->getPet()->getName() . ' wanted to be friends; ' . $p2->getPet()->getName() . ' rejected, wanting nothing to do with with ' . $p2->getPet()->getName() . '!', '');
            }
            else
            {
                $p1->setCurrentRelationship(RelationshipEnum::FRIEND);
                $p2->setCurrentRelationship(RelationshipEnum::FRIEND);

                if(mt_rand(1, 3) === 1)
                    $mostly = ' (Well, mostly!)';
                else
                    $mostly = '';

                $log1 = $this->responseService->createActivityLog($p1->getPet(), $p1->getPet()->getName() . ' wanted to be friends with ' . $p2->getPet()->getName() . '; ' . $p2->getPet()->getName() . ' happily accepted! No more of this silly rivalry stuff!' . $mostly, '');
                $log2 = $this->responseService->createActivityLog($p2->getPet(), $p1->getPet()->getName() . ' wanted to be friends with ' . $p2->getPet()->getName() . '; they happily accepted! No more of this silly rivalry stuff!' . $mostly, '');
            }
        }

        return [ $log1, $log2 ];
    }

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
                if(mt_rand(1, 4) === 1)
                    return $this->hangOutPrivatelyFromFriendsToBFFs($p1, $p2);
                else
                    return $this->hangOutPrivatelyFromFriendsToFWBs($p1, $p2);

            case RelationshipEnum::MATE:
                if(mt_rand(1, 4) === 1)
                    return $this->hangOutPrivatelyFromFriendsToMate($p1, $p2);
                else
                    return $this->hangOutPrivatelyFromFriendsToBFFs($p1, $p2);

            default:
                throw new \Exception('Relationship goal is of an unexpected type, "' . $p1->getRelationshipGoal() . '"');
        }
    }

    private function hangOutPrivatelyFromFriendsToBFFs(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $log1 = $this->responseService->createActivityLog($p1->getPet(), $p1->getPet()->getName() . ' said that they consider ' . $p2->getPet()->getName() . ' a best friend; ' . $p2->getPet()->getName() . ' revealed that they don\'t actually like hanging out with ' . $p1->getPet()->getName() . '! They are no longer friends :|', '');
                $log2 = $this->responseService->createActivityLog($p2->getPet(), $p1->getPet()->getName() . ' said that they consider ' . $p2->getPet()->getName() . ' a best friend; ' . $p2->getPet()->getName() . ' revealed that they don\'t actually like hanging out with ' . $p1->getPet()->getName() . '! They are no longer friends :|', '');
                $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p2->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p1->setRelationshipGoal(RelationshipEnum::DISLIKE);
                break;

            case RelationshipEnum::FRIEND:
                break;

            case RelationshipEnum::FRIENDLY_RIVAL:
                break;

            case RelationshipEnum::BFF:
                $log1 = $this->responseService->createActivityLog($p1->getPet(), $p1->getPet()->getName() . ' said that they consider ' . $p2->getPet()->getName() . ' a best friend; ' . $p2->getPet()->getName() . ' feels the same way! The two are now BFFs! :)', '');
                $log2 = $this->responseService->createActivityLog($p2->getPet(), $p1->getPet()->getName() . ' said that they consider ' . $p2->getPet()->getName() . ' a best friend; ' . $p2->getPet()->getName() . ' feels the same way! The two are now BFFs! :)', '');
                $p1->setCurrentRelationship(RelationshipEnum::BFF);
                $p2->setCurrentRelationship(RelationshipEnum::BFF);
                break;

            case RelationshipEnum::FWB:
                break;

            case RelationshipEnum::MATE:
                break;
        }

        return [ $log1, $log2 ];
    }

    private function hangOutPrivatelyFromFriendsToDisliked(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $log1 = $this->responseService->createActivityLog($p1->getPet(), $p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! The feeling is mutual! They are no longer friends >:(', '');
                $log2 = $this->responseService->createActivityLog($p2->getPet(), $p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense! The feeling is mutual! They are no longer friends >:(', '');
                $p1->setCurrentRelationship(RelationshipEnum::DISLIKE);
                $p2->setCurrentRelationship(RelationshipEnum::DISLIKE);
                break;

            case RelationshipEnum::FRIEND:
                break;

            case RelationshipEnum::FRIENDLY_RIVAL:
                break;

            case RelationshipEnum::BFF:
            case RelationshipEnum::FWB:
            case RelationshipEnum::MATE:
                break;
        }

        return [ $log1, $log2 ];
    }

    private function hangOutPrivatelyFromFriendsToFriendlyRivals(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $log1 = $this->responseService->createActivityLog($p1->getPet(), $p1->getPet()->getName() . ' said that they consider ' . $p2->getPet()->getName() . ' a friendly rival, but ' . $p2->getPet()->getName() . ' confessed that they\'re not really interested in hanging out at all anymore! :| The two are no longer friends...', '');
                $log2 = $this->responseService->createActivityLog($p2->getPet(), $p1->getPet()->getName() . ' suggested being friendly rivals; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out anymore, and said so! The two are no longer friends...', '');
                $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p2->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p1->setRelationshipGoal(RelationshipEnum::DISLIKE);
                break;

            case RelationshipEnum::FRIEND:
                break;

            case RelationshipEnum::FRIENDLY_RIVAL:
                $log1 = $this->responseService->createActivityLog($p1->getPet(), $p1->getPet()->getName() . ' said that they consider ' . $p2->getPet()->getName() . ' a friendly rival; ' . $p2->getPet()->getName() . ' feels the same way! Let the rivalry begin!!', '');
                $log2 = $this->responseService->createActivityLog($p2->getPet(), $p1->getPet()->getName() . ' said that they consider ' . $p2->getPet()->getName() . ' a friendly rival; ' . $p2->getPet()->getName() . ' feels the same way! Let the rivalry begin!!', '');
                $p1->setCurrentRelationship(RelationshipEnum::FRIENDLY_RIVAL);
                $p2->setCurrentRelationship(RelationshipEnum::FRIENDLY_RIVAL);
                break;

            case RelationshipEnum::BFF:
            case RelationshipEnum::FWB:
            case RelationshipEnum::MATE:
                break;
        }

        return [ $log1, $log2 ];
    }

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
                throw new \Exception('Relationship goal is of an unexpected type, "' . $p1->getRelationshipGoal() . '"');
        }
    }

    private function hangOutPrivatelyFromBFFsToDisliked(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $log1 = $this->responseService->createActivityLog($p1->getPet(), $p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! The feeling is mutual! They are no longer friends >:(', '');
                $log2 = $this->responseService->createActivityLog($p2->getPet(), $p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense! The feeling is mutual! They are no longer friends >:(', '');
                $p1->setCurrentRelationship(RelationshipEnum::DISLIKE);
                $p2->setCurrentRelationship(RelationshipEnum::DISLIKE);
                break;

            case RelationshipEnum::FRIEND:
                break;

            case RelationshipEnum::FRIENDLY_RIVAL:
                break;

            case RelationshipEnum::BFF:
                break;

            case RelationshipEnum::FWB:
                break;

            case RelationshipEnum::MATE:
                break;
        }

        return [ $log1, $log2 ];
    }

    private function hangOutPrivatelySuggestingRelationshipChangeAsFWBs(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p1->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                if(mt_rand(1, 4) === 1)
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
                throw new \Exception('Relationship goal is of an unexpected type, "' . $p1->getRelationshipGoal() . '"');
        }
    }

    private function hangOutPrivatelyFromFWBsToDisliked(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $log1 = $this->responseService->createActivityLog($p1->getPet(), $p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! The feeling is mutual! They are no longer friends >:(', '');
                $log2 = $this->responseService->createActivityLog($p2->getPet(), $p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense! The feeling is mutual! They are no longer friends >:(', '');
                $p1->setCurrentRelationship(RelationshipEnum::DISLIKE);
                $p2->setCurrentRelationship(RelationshipEnum::DISLIKE);
                break;

            case RelationshipEnum::FRIENDLY_RIVAL:
                break;

            case RelationshipEnum::FRIEND:
            case RelationshipEnum::BFF:
                break;

            case RelationshipEnum::FWB:
                break;

            case RelationshipEnum::MATE:
                break;
        }

        return [ $log1, $log2 ];
    }

    private function hangOutPrivatelySuggestingRelationshipChangeAsMates(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p1->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                if(mt_rand(1, 4) === 1)
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
                throw new \Exception('Relationship goal is of an unexpected type, "' . $p1->getRelationshipGoal() . '"');
        }
    }

    private function hangOutPrivatelyFromMatesToDisliked(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $log1 = $this->responseService->createActivityLog($p1->getPet(), $p1->getPet()->getName() . ' doesn\'t like ' . $p2->getPet()->getName() . ' anymore. The feeling is mutual! They\'re breaking up! >:(', '');
                $log2 = $this->responseService->createActivityLog($p2->getPet(), $p1->getPet()->getName() . ' said they don\'t like ' . $p2->getPet()->getName() . ' anymore. The feeling is mutual! They\'re breaking up! >:(', '');
                $p1->setCurrentRelationship(RelationshipEnum::DISLIKE);
                $p2->setCurrentRelationship(RelationshipEnum::DISLIKE);
                break;

            case RelationshipEnum::FRIEND:
                break;

            case RelationshipEnum::FRIENDLY_RIVAL:
                break;

            case RelationshipEnum::BFF:
                break;

            case RelationshipEnum::FWB:
                break;

            case RelationshipEnum::MATE:
                break;
        }

        return [ $log1, $log2 ];
    }
}