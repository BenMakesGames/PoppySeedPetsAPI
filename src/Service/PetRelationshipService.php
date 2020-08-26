<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetRelationship;
use App\Enum\EnumInvalidValueException;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\RelationshipEnum;
use App\Functions\ArrayFunctions;
use App\Repository\PetRelationshipRepository;
use App\Service\PetActivity\PregnancyService;
use App\Service\PetActivity\Relationship\FriendlyRivalsService;
use App\Service\PetActivity\Relationship\LoveService;
use App\Service\PetActivity\Relationship\RelationshipChangeService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class PetRelationshipService
{
    public const RELATIONSHIP_COMMITMENTS = [
        RelationshipEnum::BROKE_UP => -1,
        RelationshipEnum::DISLIKE => 0,
        RelationshipEnum::FRIENDLY_RIVAL => 1,
        RelationshipEnum::FRIEND => 2,
        RelationshipEnum::BFF => 3,
        RelationshipEnum::FWB => 4,
        RelationshipEnum::MATE => 5,
    ];

    private $petRelationshipRepository;
    private $em;
    private $responseService;
    private $pregnancyService;
    private $friendlyRivalsService;
    private $loveService;
    private $relationshipChangeService;

    public function __construct(
        PetRelationshipRepository $petRelationshipRepository, EntityManagerInterface $em, ResponseService $responseService,
        PregnancyService $pregnancyService, FriendlyRivalsService $friendlyRivalsService, LoveService $loveService,
        RelationshipChangeService $relationshipChangeService
    )
    {
        $this->petRelationshipRepository = $petRelationshipRepository;
        $this->em = $em;
        $this->responseService = $responseService;
        $this->pregnancyService = $pregnancyService;
        $this->friendlyRivalsService = $friendlyRivalsService;
        $this->loveService = $loveService;
        $this->relationshipChangeService = $relationshipChangeService;
    }

    public function min(string $relationship1, string $relationship2): string
    {
        $r1Commitment = self::RELATIONSHIP_COMMITMENTS[$relationship1];
        $r2Commitment = self::RELATIONSHIP_COMMITMENTS[$relationship2];

        $min = min($r1Commitment, $r2Commitment);

        return $min === $r1Commitment ? $relationship1 : $relationship2;
    }

    public function max(string $relationship1, string $relationship2): string
    {
        $r1Commitment = self::RELATIONSHIP_COMMITMENTS[$relationship1];
        $r2Commitment = self::RELATIONSHIP_COMMITMENTS[$relationship2];

        $max = max($r1Commitment, $r2Commitment);

        return $max === $r1Commitment ? $relationship1 : $relationship2;
    }

    /**
     * @return string[]
     */
    public function getRelationshipsBetween(string $relationship1, string $relationship2): array
    {
        $r1Commitment = self::RELATIONSHIP_COMMITMENTS[$relationship1];
        $r2Commitment = self::RELATIONSHIP_COMMITMENTS[$relationship2];

        $minCommitment = min($r1Commitment, $r2Commitment);
        $maxCommitment = max($r1Commitment, $r2Commitment);

        $between = [];

        foreach(self::RELATIONSHIP_COMMITMENTS as $relationship=>$commitment)
        {
            if($commitment >= $minCommitment && $commitment <= $maxCommitment)
                $between[] = $relationship;
        }

        return $between;
    }

    /**
     * @param ArrayCollection|Pet[] $pets
     */
    public function groupGathering(
        $pets,
        string $hangOutDescription,
        string $enemyDescription,
        string $meetProfileText,
        string $meetActivityLogTemplate,
        int $meetChance = 2
    )
    {
        // array_values, because keys might not be sequential (members can leave), but we need to use array indicies.
        // ->toArray, because we might have received a stupid ArrayCollection from Doctrine
        if(is_array($pets))
            $members = array_values($pets);
        else
            $members = array_values($pets->toArray());

        for($i = 0; $i < count($members) - 1; $i++)
        {
            // $i + 1 prevents duplicate hang-outs
            for($j = $i + 1; $j < count($members); $j++)
                $this->seeAtGroupGathering($members[$i], $members[$j], $hangOutDescription, $enemyDescription, $meetProfileText, $meetActivityLogTemplate, $meetChance);
        }
    }

    public function seeAtGroupGathering(Pet $p1, Pet $p2, string $hangOutDescription, string $enemyDescription, string $meetSummary, string $meetActivityLogTemplate, int $meetChance = 5)
    {
        if($p1->getId() === $p2->getId()) return;

        $p1Relationships = $p1->getRelationshipWith($p2);

        if($p1Relationships)
            $this->hangOutPublicly($p1Relationships, $p2->getRelationshipWith($p1), $hangOutDescription, $enemyDescription);
        else if(mt_rand(1, 100) <= $meetChance)
            $this->introducePets($p1, $p2, $meetSummary, $meetActivityLogTemplate);
    }

    public function meetRoommate(Pet $pet, Pet $otherPet): ?PetRelationship
    {
        $relationship = $pet->getRelationshipWith($otherPet);

        if($relationship === null)
        {
            $this->introducePets(
                $pet,
                $otherPet,
                'Met at ' . $pet->getOwner()->getName() . '\'s house as roomies!',
                '%p1% met their new roommate, %p2%.'
            );
        }
        else
        {
            $whatASurprise = mt_rand(1, 10) === 1 ? 'Quelle surprise!' : 'What a surprise!';

            $this->responseService->createActivityLog($pet, $pet->getName() . ' and ' . $otherPet->getName() . ' are living together, now! ' . $whatASurprise, 'icons/activity-logs/friend')
                ->addInterestingness(PetActivityLogInterestingnessEnum::NEW_RELATIONSHIP)
            ;

            $this->responseService->createActivityLog($otherPet, $otherPet->getName() . ' and ' . $pet->getName() . ' are living together, now! ' . $whatASurprise, 'icons/activity-logs/friend')
                ->addInterestingness(PetActivityLogInterestingnessEnum::NEW_RELATIONSHIP)
            ;
        }

        return $relationship;
    }

    /**
     * @return PetRelationship[]
     */
    public function introducePets(Pet $pet, Pet $otherPet, string $howMetSummary, string $metActivityLogTemplate): array
    {
        if($this->loveService->isTooCloselyRelatedForSex($pet, $otherPet))
        {
            $initialRelationship = RelationshipEnum::FRIEND;

            $possibleRelationships = [
                RelationshipEnum::FRIEND,
                RelationshipEnum::FRIEND,
                RelationshipEnum::BFF,
                RelationshipEnum::BFF,
                RelationshipEnum::BFF,
                RelationshipEnum::BFF,
                RelationshipEnum::FRIENDLY_RIVAL,
                RelationshipEnum::DISLIKE,
            ];
        }
        else
        {

            $r = mt_rand(1, 100);

            if($r <= $pet->getSexDrive() + $otherPet->getSexDrive())
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
                    RelationshipEnum::MATE,
                    RelationshipEnum::MATE
                ];

                if($pet->getSexDrive() + $otherPet->getSexDrive() >= 1)
                    $possibleRelationships[] = RelationshipEnum::FWB;

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
                    RelationshipEnum::MATE,
                    RelationshipEnum::MATE,
                    RelationshipEnum::MATE,
                ];

                if($pet->getSexDrive() + $otherPet->getSexDrive() >= 1)
                    $possibleRelationships[] = RelationshipEnum::FWB;
            }
        }

        $relationshipGoal = ArrayFunctions::pick_one($possibleRelationships);

        // pet
        $petRelationship = (new PetRelationship())
            ->setRelationship($otherPet)
            ->setMetDescription($howMetSummary)
            ->setCurrentRelationship($initialRelationship)
            ->setPet($pet)
            ->setRelationshipGoal($relationshipGoal)
            ->setCommitment($this->generateInitialCommitment($initialRelationship, $relationshipGoal))
        ;

        $pet->addPetRelationship($petRelationship);

        $this->em->persist($petRelationship);

        $meetDescription = str_replace([ '%p1%', '%p2%'], [ $pet->getName(), $otherPet->getName() ], $metActivityLogTemplate);

        if($petRelationship->getCurrentRelationship() === RelationshipEnum::DISLIKE)
            $activityLog = $this->responseService->createActivityLog($pet, $meetDescription . ' They didn\'t really get along, though...', 'icons/activity-logs/enemy');
        else if($petRelationship->getCurrentRelationship() === RelationshipEnum::FWB || $petRelationship->getRelationshipGoal() === RelationshipEnum::FWB || $petRelationship->getRelationshipGoal() === RelationshipEnum::MATE)
            $activityLog = $this->responseService->createActivityLog($pet, $meetDescription . ' (And what a cutie!)', 'icons/activity-logs/friend-cute');
        else
            $activityLog = $this->responseService->createActivityLog($pet, $meetDescription, 'icons/activity-logs/friend');

        $activityLog->addInterestingness(PetActivityLogInterestingnessEnum::NEW_RELATIONSHIP);

        // other pet
        $relationshipGoal = ArrayFunctions::pick_one($possibleRelationships);

        $otherPetRelationship = (new PetRelationship())
            ->setRelationship($pet)
            ->setMetDescription($howMetSummary)
            ->setCurrentRelationship($initialRelationship)
            ->setPet($otherPet)
            ->setRelationshipGoal($relationshipGoal)
            ->setCommitment($this->generateInitialCommitment($initialRelationship, $relationshipGoal))
        ;

        $otherPet->addPetRelationship($otherPetRelationship);

        $this->em->persist($otherPetRelationship);

        $meetDescription = str_replace([ '%p1%', '%p2%'], [ $otherPet->getName(), $pet->getName() ], $metActivityLogTemplate);

        if($petRelationship->getCurrentRelationship() === RelationshipEnum::DISLIKE)
            $otherActivityPet = $this->responseService->createActivityLog($otherPet, $meetDescription . ' They didn\'t really get along, though...', 'icons/activity-logs/enemy');
        else if($otherPetRelationship->getCurrentRelationship() === RelationshipEnum::FWB || $otherPetRelationship->getRelationshipGoal() === RelationshipEnum::FWB || $otherPetRelationship->getRelationshipGoal() === RelationshipEnum::MATE)
            $otherActivityPet = $this->responseService->createActivityLog($otherPet, $meetDescription . ' (And what a cutie!)', 'icons/activity-logs/friend-cute');
        else
            $otherActivityPet = $this->responseService->createActivityLog($otherPet, $meetDescription, 'icons/activity-logs/friend');

        $otherActivityPet->addInterestingness(PetActivityLogInterestingnessEnum::NEW_RELATIONSHIP);

        return [ $petRelationship, $otherPetRelationship ];
    }

    public function generateInitialCommitment(string $startingRelationship, string $relationshipGoal)
    {
        $commitment = mt_rand(0, 30);

        switch($relationshipGoal)
        {
            case RelationshipEnum::MATE: $commitment += 100; break;
            case RelationshipEnum::FWB: $commitment += 80; break;
            case RelationshipEnum::BFF: $commitment += 70; break;
            case RelationshipEnum::FRIEND: $commitment += 50; break;
            case RelationshipEnum::FRIENDLY_RIVAL: $commitment += 20; break;
        }

        switch($startingRelationship)
        {
            case RelationshipEnum::MATE: $commitment += 30; break;
            case RelationshipEnum::FWB: $commitment += 20; break;
            case RelationshipEnum::BFF: $commitment += 18; break;
            case RelationshipEnum::FRIEND: $commitment += 12; break;
            case RelationshipEnum::FRIENDLY_RIVAL: $commitment += 5; break;
        }

        return $commitment;
    }

    public function calculateRelationshipDistance($initialRelationship, $targetRelationship): int
    {
        $values = [
            RelationshipEnum::BROKE_UP => -2,
            RelationshipEnum::DISLIKE => 0,
            RelationshipEnum::FRIENDLY_RIVAL => 2,
            RelationshipEnum::FRIEND => 3,
            RelationshipEnum::BFF => 6,
            RelationshipEnum::FWB => 8,
            RelationshipEnum::MATE => 10,
        ];

        return $values[$targetRelationship] - $values[$initialRelationship];
    }

    public function hangOutPublicly(PetRelationship $p1, PetRelationship $p2, string $hangOutDescription, string $enemyDescription)
    {
        $p1->decrementTimeUntilChange(0.5);
        $p2->decrementTimeUntilChange(0.5);

        $p1->setLastMet();
        $p2->setLastMet();

        if($p1->getCurrentRelationship() === RelationshipEnum::DISLIKE)
        {
            if($p1->getPet()->hasMerit(MeritEnum::NAIVE))
                $p1Description = null;
            else
                $p1Description = str_replace([ '%p1%', '%p2%' ], [ $p1->getPet()->getName(), $p2->getPet()->getName() ], $enemyDescription);

            if($p2->getPet()->hasMerit(MeritEnum::NAIVE))
                $p2Description = null;
            else
                $p2Description = str_replace([ '%p1%', '%p2%' ], [ $p2->getPet()->getName(), $p1->getPet()->getName() ], $enemyDescription);
        }
        else if($p1->getCurrentRelationship() === RelationshipEnum::BROKE_UP)
        {
            $p1Description = str_replace([ '%p1%', '%p2%' ], [ $p1->getPet()->getName(), $p2->getPet()->getName() ], $enemyDescription);
            $p2Description = str_replace([ '%p1%', '%p2%' ], [ $p2->getPet()->getName(), $p1->getPet()->getName() ], $enemyDescription);
        }
        else
        {
            $p1Description = str_replace([ '%p1%', '%p2%' ], [ $p1->getPet()->getName(), $p2->getPet()->getName() ], $hangOutDescription);
            $p2Description = str_replace([ '%p1%', '%p2%' ], [ $p2->getPet()->getName(), $p1->getPet()->getName() ], $hangOutDescription);
        }

        if($p1Description) $this->responseService->createActivityLog($p1->getPet(), $p1Description, '');
        if($p2Description) $this->responseService->createActivityLog($p2->getPet(), $p2Description, '');
    }

    /**
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     * @throws EnumInvalidValueException
     */
    public function hangOutPrivately(PetRelationship $p1, PetRelationship $p2): array
    {
        $p1->setLastMet();
        $p2->setLastMet();

        if($p1->wantsDifferentRelationship() && $p1->getTimeUntilChange() <= 1)
            return $this->relationshipChangeService->hangOutPrivatelySuggestingRelationshipChange($p1, $p2);
        else if($p2->wantsDifferentRelationship() && $p2->getTimeUntilChange() <= 1)
            return $this->relationshipChangeService->hangOutPrivatelySuggestingRelationshipChange($p2, $p1);
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
                    return $this->friendlyRivalsService->hangOutPrivatelyAsFriendlyRivals($p1, $p2);

                case RelationshipEnum::FRIEND:
                    return $this->hangOutPrivatelyAsFriends($p1, $p2);

                case RelationshipEnum::BFF:
                    return $this->hangOutPrivatelyAsBFFs($p1, $p2);

                case RelationshipEnum::FWB:
                    return $this->hangOutPrivatelyAsFWBs($p1, $p2);

                case RelationshipEnum::MATE:
                    return $this->hangOutPrivatelyAsMates($p1, $p2);

                default:
                    throw new \InvalidArgumentException('p1 relationship goal is of an unexpected type, "' . $p1->getRelationshipGoal() . '"');
            }
        }
    }

    /**
     * @param PetRelationship $p1
     * @param PetRelationship $p2
     * @return PetActivityLog[]
     */
    private function hangOutPrivatelyAsFriends(PetRelationship $p1, PetRelationship $p2): array
    {
        if(mt_rand(1, 20) === 1)
            return $this->friendlyRivalsService->hangOutPrivatelyAsFriendlyRivals($p1, $p2);

        $pet = $p1->getPet();
        $friend = $p2->getPet();

        $petLowestNeed = $pet->getLowestNeed();
        $friendLowestNeed = $friend->getLowestNeed();

        if($petLowestNeed === '')
        {
            if($friendLowestNeed === '')
            {
                if(mt_rand(1, 100) <= $this->loveService->sexyTimeChances($pet, $friend, $p1->getCurrentRelationship()))
                {
                    $message = $pet->getName() . ' hung out with ' . $friend->getName() . '. They had fun! ' . $this->loveService->sexyTimesEmoji($pet, $friend);

                    $pet
                        ->increaseLove(mt_rand(2, 4))
                        ->increaseSafety(mt_rand(2, 4))
                        ->increaseEsteem(mt_rand(2, 4))
                    ;

                    $friend
                        ->increaseLove(mt_rand(2, 4))
                        ->increaseSafety(mt_rand(2, 4))
                        ->increaseEsteem(mt_rand(2, 4))
                    ;

                    if(mt_rand(1, 20) === 1)
                        $this->pregnancyService->getPregnant($pet, $friend);
                }
                else if($p1->getCurrentRelationship() === RelationshipEnum::MATE && mt_rand(1, 5) === 1)
                {
                    $message = $this->loveService->expressLove($pet, $friend);
                }
                else
                {
                    $message = $pet->getName() . ' hung out with ' . $friend->getName() . '. They had fun! :)';

                    $pet
                        ->increaseLove(mt_rand(3, 6))
                        ->increaseSafety(mt_rand(3, 6))
                        ->increaseEsteem(mt_rand(3, 6))
                    ;

                    $friend
                        ->increaseLove(mt_rand(3, 6))
                        ->increaseSafety(mt_rand(3, 6))
                        ->increaseEsteem(mt_rand(3, 6))
                    ;
                }
            }
            else
            {
                $message = $pet->getName() . ' hung out with ' . $friend->getName() . ' who wasn\'t actually feeling that great :| ' . $pet->getName() . ' comforted them for a while.';

                $pet
                    ->increaseLove(mt_rand(2, 4))
                    ->increaseEsteem(mt_rand(4, 8))
                ;

                $friend
                    ->increaseSafety(mt_rand(2, 4))
                    ->increaseLove(mt_rand(2, 4))
                    ->increaseEsteem(mt_rand(2, 4))
                ;
            }
        }
        else if($petLowestNeed === 'safety')
        {
            if($friendLowestNeed === 'safety')
            {
                $message = $pet->getName() . ' was feeling nervous, so came to hang out with ' . $friend->getName() . '... who was also feeling a little nervous! They huddled up together, and kept each other safe.';

                $pet
                    ->increaseSafety(mt_rand(2, 4))
                    ->increaseLove(mt_rand(4, 8))
                ;

                $friend
                    ->increaseSafety(mt_rand(2, 4))
                    ->increaseLove(mt_rand(4, 8))
                ;
            }
            else
            {
                $message = $pet->getName() . ' was feeling nervous, so hung out with ' . $friend->getName() . '. They huddled up together, and kept each other safe.';

                $pet
                    ->increaseSafety(mt_rand(4, 8))
                    ->increaseLove(mt_rand(2, 4))
                ;

                $friend
                    ->increaseEsteem(mt_rand(4, 8))
                    ->increaseLove(mt_rand(2, 4))
                ;
            }
        }
        else if($petLowestNeed === 'love')
        {
            $message = $pet->getName() . ' was feeling lonely, so hung out with ' . $friend->getName() . '. They had fun :)';
            $pet
                ->increaseSafety(mt_rand(2, 4))
                ->increaseLove(mt_rand(2, 4))
            ;

            if($friendLowestNeed !== 'esteem')
                $friend->increaseSafety(mt_rand(2, 4));

            $friend->increaseLove(mt_rand(2, 4));

            if($friendLowestNeed === 'esteem')
                $friend->increaseEsteem(mt_rand(2, 4));
        }
        else //if($petLowestNeed === 'esteem')
        {
            if($friendLowestNeed === '' || $friendLowestNeed === 'safety')
            {
                $message = $pet->getName() . ' was feeling down, and talked to ' . $friend->getName() . ' about it. ' . $friend->getName() . ' listened patiently, which made ' . $pet->getName() . ' feel a little better.';

                $pet
                    ->increaseLove(mt_rand(2, 4))
                    ->increaseEsteem(mt_rand(2, 4))
                ;

                if($friendLowestNeed === 'safety')
                    $friend->increaseSafety(mt_rand(2, 4));
            }
            else
            {
                $message = $pet->getName() . ' and ' . $friend->getName() . ' were both feeling down. They complained about other people, and the world. It was kind of negative, but sharing their feelings made them both feel a little better.';

                $pet
                    ->increaseLove(mt_rand(2, 4))
                    ->increaseEsteem(mt_rand(2, 4))
                ;

                $friend
                    ->increaseLove(mt_rand(2, 4))
                    ->increaseEsteem(mt_rand(2, 4))
                ;
            }
        }

        $p1Log = (new PetActivityLog())
            ->setPet($pet)
            ->setEntry($message)
            ->setIcon('icons/activity-logs/friend')
        ;

        $this->em->persist($p1Log);

        $p2Log = (new PetActivityLog())
            ->setPet($friend)
            ->setEntry($message)
            ->setIcon('icons/activity-logs/friend')
        ;

        $this->em->persist($p2Log);

        return [ $p1Log, $p2Log ];
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
        return $this->hangOutPrivatelyAsFriends($p1, $p2);
    }

    /**
     * @return PetActivityLog[]
     */
    private function hangOutPrivatelyAsMates(PetRelationship $p1, PetRelationship $p2): array
    {
        return $this->hangOutPrivatelyAsFriends($p1, $p2);
    }
}
