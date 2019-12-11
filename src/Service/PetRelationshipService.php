<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetRelationship;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\RelationshipEnum;
use App\Functions\ArrayFunctions;
use App\Repository\PetRelationshipRepository;
use App\Service\PetActivity\PregnancyService;
use Doctrine\ORM\EntityManagerInterface;

class PetRelationshipService
{
    private $petRelationshipRepository;
    private $em;
    private $responseService;
    private $pregnancyService;

    public function __construct(
        PetRelationshipRepository $petRelationshipRepository, EntityManagerInterface $em, ResponseService $responseService,
        PregnancyService $pregnancyService
    )
    {
        $this->petRelationshipRepository = $petRelationshipRepository;
        $this->em = $em;
        $this->responseService = $responseService;
        $this->pregnancyService = $pregnancyService;
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

    public function createParentalRelationships(Pet $baby, Pet $mother, Pet $father)
    {
        $petWithMother = (new PetRelationship())
            ->setRelationship($mother)
            ->setCurrentRelationship(RelationshipEnum::BFF)
            ->setRelationshipGoal(RelationshipEnum::BFF)
            ->setMetDescription($mother->getName() . ' gave birth to ' . $baby->getName() . '!')
        ;

        $baby->addPetRelationship($petWithMother);

        $petWithFather = (new PetRelationship())
            ->setRelationship($father)
            ->setCurrentRelationship(RelationshipEnum::BFF)
            ->setRelationshipGoal(RelationshipEnum::BFF)
            ->setMetDescription($father->getName() . ' fathered ' . $baby->getName() . '!')
        ;

        $baby->addPetRelationship($petWithFather);

        $motherWithBaby = (new PetRelationship())
            ->setRelationship($baby)
            ->setCurrentRelationship(RelationshipEnum::BFF)
            ->setRelationshipGoal(RelationshipEnum::BFF)
            ->setMetDescription($mother->getName() . ' gave birth to ' . $baby->getName() . '!')
        ;

        $mother->addPetRelationship($motherWithBaby);

        $fatherWithBaby = (new PetRelationship())
            ->setRelationship($baby)
            ->setCurrentRelationship(RelationshipEnum::BFF)
            ->setRelationshipGoal(RelationshipEnum::BFF)
            ->setMetDescription($father->getName() . ' fathered ' . $baby->getName() . '!')
        ;

        $mother->addPetRelationship($fatherWithBaby);

        $this->em->persist($petWithMother);
        $this->em->persist($petWithFather);
        $this->em->persist($motherWithBaby);
        $this->em->persist($fatherWithBaby);
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
    public function introducePets(Pet $pet, Pet $otherPet, string $howMetSummary, string $howMetDescription): array
    {
        $r = \mt_rand(1, 100);

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

        // pet
        $petRelationship = (new PetRelationship())
            ->setRelationship($otherPet)
            ->setMetDescription($howMetSummary)
            ->setCurrentRelationship($initialRelationship)
            ->setRelationshipGoal(ArrayFunctions::pick_one($possibleRelationships))
        ;

        $pet->addPetRelationship($petRelationship);

        $this->em->persist($petRelationship);

        $meetDescription = str_replace([ '%p1%', '%p2%'], [ $pet->getName(), $otherPet->getName() ], $howMetDescription);

        if($petRelationship->getCurrentRelationship() === RelationshipEnum::DISLIKE)
            $activityLog = $this->responseService->createActivityLog($pet, $meetDescription . ' They didn\'t really get along, though...', 'icons/activity-logs/enemy');
        else if($petRelationship->getCurrentRelationship() === RelationshipEnum::FWB || $petRelationship->getRelationshipGoal() === RelationshipEnum::FWB || $petRelationship->getRelationshipGoal() === RelationshipEnum::MATE)
            $activityLog = $this->responseService->createActivityLog($pet, $meetDescription . ' (And what a cutie!)', 'icons/activity-logs/friend-cute');
        else
            $activityLog = $this->responseService->createActivityLog($pet, $meetDescription, 'icons/activity-logs/friend');

        $activityLog->addInterestingness(PetActivityLogInterestingnessEnum::NEW_RELATIONSHIP);

        // other pet
        $otherPetRelationship = (new PetRelationship())
            ->setRelationship($pet)
            ->setMetDescription($howMetSummary)
            ->setCurrentRelationship($initialRelationship)
            ->setRelationshipGoal(ArrayFunctions::pick_one($possibleRelationships))
        ;

        $otherPet->addPetRelationship($otherPetRelationship);

        $this->em->persist($otherPetRelationship);

        $meetDescription = str_replace([ '%p1%', '%p2%'], [ $otherPet->getName(), $pet->getName() ], $howMetDescription);

        if($petRelationship->getCurrentRelationship() === RelationshipEnum::DISLIKE)
            $otherActivityPet = $this->responseService->createActivityLog($otherPet, $meetDescription . ' They didn\'t really get along, though...', 'icons/activity-logs/enemy');
        else if($otherPetRelationship->getCurrentRelationship() === RelationshipEnum::FWB || $otherPetRelationship->getRelationshipGoal() === RelationshipEnum::FWB || $otherPetRelationship->getRelationshipGoal() === RelationshipEnum::MATE)
            $otherActivityPet = $this->responseService->createActivityLog($otherPet, $meetDescription . ' (And what a cutie!)', 'icons/activity-logs/friend-cute');
        else
            $otherActivityPet = $this->responseService->createActivityLog($otherPet, $meetDescription, 'icons/activity-logs/friend');

        $otherActivityPet->addInterestingness(PetActivityLogInterestingnessEnum::NEW_RELATIONSHIP);

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
            return $this->hangOutPrivatelySuggestingRelationshipChange($p1, $p2);
        else if($p2->wantsDifferentRelationship() && $p2->getTimeUntilChange() <= 1)
            return $this->hangOutPrivatelySuggestingRelationshipChange($p2, $p1);
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

                default:
                    throw new \InvalidArgumentException('p1 relationship goal is of an unexpected type, "' . $p1->getRelationshipGoal() . '"');
            }
        }
    }

    /**
     * @return PetActivityLog[]
     */
    private function hangOutPrivatelyAsFriendlyRivals(PetRelationship $p1, PetRelationship $p2): array
    {
        $p1Skills = [
            'knowledge of the umbra' => $p1->getPet()->getUmbra(),
            'raw strength' => $p1->getPet()->getStrength(),
            'fighting prowess' => $p1->getPet()->getBrawl(),
            'l33t haxxing skillz' => $p1->getPet()->getComputer(),
            'crafting skill' => $p1->getPet()->getCrafts(),
            'musical ability' => $p1->getPet()->getMusic(),
        ];

        $p2Skills = [
            'knowledge of the umbra' => $p2->getPet()->getUmbra(),
            'raw strength' => $p2->getPet()->getStrength(),
            'fighting prowess' => $p2->getPet()->getBrawl(),
            'l33t haxxing skillz' => $p2->getPet()->getComputer(),
            'crafting skill' => $p2->getPet()->getCrafts(),
            'musical ability' => $p2->getPet()->getMusic(),
        ];

        $combinedSkills = [
            'knowledge of the umbra' => $p1->getPet()->getUmbra() + $p2->getPet()->getUmbra(),
            'raw strength' => $p1->getPet()->getStrength() + $p2->getPet()->getStrength(),
            'fighting prowess' => $p1->getPet()->getBrawl() + $p2->getPet()->getBrawl(),
            'l33t haxxing skillz' => $p1->getPet()->getComputer() + $p2->getPet()->getComputer(),
            'crafting skill' => $p1->getPet()->getCrafts() + $p2->getPet()->getCrafts(),
            'musical ability' => $p1->getPet()->getMusic() + $p2->getPet()->getMusic(),
        ];

        arsort($combinedSkills);
        $combinedSkills = array_splice($combinedSkills, 0, 3, true);

        $message = $p1->getPet()->getName() . ' and ' . $p2->getPet()->getName() . ' met to compare their accomplishments, but just ended up bickering over which types of accomplishments are even worth mentioning.';

        foreach($combinedSkills as $description=>$skill)
        {
            if(mt_rand(1, 2) === 1)
            {
                $message = $p1->getPet()->getName() . ' and ' . $p2->getPet()->getName() . ' met to brag about their ' . $description . '. ';

                $p1Roll = mt_rand(1, max(2, $p1Skills[$description] + 2));
                $p2Roll = mt_rand(1, max(2, $p2Skills[$description] + 2));

                if($p1Roll > ceil($p2Roll * 1.25))
                {
                    $message .= $p1->getPet()->getName() . ' was clearly the more accomplished of the two! ';

                    $message .= ArrayFunctions::pick_one([
                        '(Not that ' . $p2->getPet()->getName() . ' would ever admit it!)',
                        $p2->getPet()->getName() . ' swore revenge!',
                        $p2->getPet()->getName() . ' conceded defeat... _this time!_',
                        $p2->getPet()->getName() . ' called shenanigans, demanding a rematch! The true master will be decided _next_ time!',
                    ]);
                }
                else if($p2Roll > ceil($p1Roll * 1.25))
                {
                    $message .= $p2->getPet()->getName() . ' was clearly the more accomplished of the two! ';

                    $message .= ArrayFunctions::pick_one([
                        '(Not that ' . $p1->getPet()->getName() . ' would ever admit it!)',
                        $p1->getPet()->getName() . ' swore revenge!',
                        $p1->getPet()->getName() . ' conceded defeat... _this time!_',
                        $p1->getPet()->getName() . ' called shenanigans, demanding a rematch! The true master will be decided _next_ time!',
                    ]);
                }
                else
                {
                    $message .= ArrayFunctions::pick_one([
                        'Each claimed to be better than the other, and vowed to prove it during their next encounter!',
                        'They argued for a while about how best to test their skills, but couldn\'t come to an agreement. (Next time!)',
                        'They mocked each other\'s accomplishments, and eventually called the whole thing off without deciding on a victor.',
                    ]);
                }

                break;
            }
        }


        $p1Log = (new PetActivityLog())
            ->setPet($p1->getPet())
            ->setEntry($message)
            ->setIcon('icons/activity-logs/friend')
        ;

        $this->em->persist($p1Log);

        $p2Log = (new PetActivityLog())
            ->setPet($p2->getPet())
            ->setEntry($message)
            ->setIcon('icons/activity-logs/friend')
        ;

        $this->em->persist($p2Log);

        return [ $p1Log, $p2Log ];
    }

    private function sexyTimeChances(Pet $p1, Pet $p2, string $relationshipType): int
    {
        if(
            ($p1->getMom() && $p1->getMom()->getId() === $p2->getId()) ||
            ($p1->getDad() && $p1->getDad()->getId() === $p2->getId()) ||
            ($p2->getMom() && $p2->getMom()->getId() === $p1->getId()) ||
            ($p2->getDad() && $p2->getDad()->getId() === $p1->getId())
        )
            return 0;

        $totalDrive = $p1->getSexDrive() + $p2->getSexDrive();

        // TODO: before we can implement this, we also need to implement a way for pets to "suppress" goals to date/fwb
        // with pets while in a monogamous relationship
        /*if($p1->hasMonogamousRelationship($p2) || $p2->hasMonogamousRelationship($p1))
            return 0;*/

        switch($relationshipType)
        {
            case RelationshipEnum::BFF:
                switch($totalDrive)
                {
                    case -2: return 0;
                    case -1: return 0;
                    case 0: return 1;
                    case 1: return 2;
                    case 2: return 3;
                    default: throw new \Exception('Pets\' total sex drive was outside the possible range??');
                }

            case RelationshipEnum::FWB:
                switch($totalDrive)
                {
                    case -2: return 10;
                    case -1: return 20;
                    case 0: return 30;
                    case 1: return 55;
                    case 2: return 80;
                    default: throw new \Exception('Pets\' total sex drive was outside the possible range??');
                }

            case RelationshipEnum::MATE:
                switch($totalDrive)
                {
                    case -2: return 5;
                    case -1: return 10;
                    case 0: return 20;
                    case 1: return 40;
                    case 2: return 60;
                    default: throw new \Exception('Pets\' total sex drive was outside the possible range??');
                }

            default:
                return 0;
        }
    }

    /**
     * @return PetActivityLog[]
     */
    private function hangOutPrivatelyAsFriends(PetRelationship $p1, PetRelationship $p2): array
    {
        if(mt_rand(1, 20) === 1)
            return $this->hangOutPrivatelyAsFriendlyRivals($p1, $p2);

        $pet = $p1->getPet();
        $friend = $p2->getPet();

        $petLowestNeed = $pet->getLowestNeed();
        $friendLowestNeed = $friend->getLowestNeed();

        if($petLowestNeed === '')
        {
            if($friendLowestNeed === '')
            {
                if(mt_rand(1, 100) <= $this->sexyTimeChances($pet, $friend, $p1->getCurrentRelationship()))
                {
                    $message = $pet->getName() . ' hung out with ' . $friend->getName() . '. They had fun! ;)';

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

    /**
     * @return PetActivityLog[]
     */
    private function hangOutPrivatelySuggestingRelationshipChange(PetRelationship $p1, PetRelationship $p2): array
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

    private function hangOutPrivatelySuggestingRelationshipChangeAsFriendlyRival(PetRelationship $p1, PetRelationship $p2): array
    {
        if($p1->getRelationshipGoal() === RelationshipEnum::DISLIKE)
        {
            $p1->setCurrentRelationship(RelationshipEnum::DISLIKE);
            $p2->setCurrentRelationship(RelationshipEnum::DISLIKE);

            $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s shennanigans! They are no longer friendly rivals.');

            if($p2->getRelationshipGoal() === RelationshipEnum::DISLIKE)
            {
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s shennanigans! The feeling is mutual! They are no longer friendly rivals!');
            }
            else
            {
                $p2->setRelationshipGoal(RelationshipEnum::DISLIKE);

                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s shennanigans! They don\'t want to be friendly rivals any more! (How rude!)');
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

                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' wanted to be friends with ' . $p2->getPet()->getName() . ', but ' . $p2->getPet()->getName() . ' apparently wants nothing to do with ' . $p1->getPet()->getName() . ' anymore! :(');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' wanted to be friends; ' . $p2->getPet()->getName() . ' rejected, wanting nothing to do with with ' . $p2->getPet()->getName() . '!');
            }
            else
            {
                $p1->setCurrentRelationship(RelationshipEnum::FRIEND);
                $p2->setCurrentRelationship(RelationshipEnum::FRIEND);

                if(mt_rand(1, 3) === 1)
                    $mostly = ' (Well, mostly!)';
                else
                    $mostly = '';

                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' wanted to be friends with ' . $p2->getPet()->getName() . '; ' . $p2->getPet()->getName() . ' happily accepted! No more of this silly rivalry stuff!' . $mostly);
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' wanted to be friends with ' . $p2->getPet()->getName() . '; they happily accepted! No more of this silly rivalry stuff!' . $mostly);
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
                    return $this->hangOutPrivatelyFromFriendsToMates($p1, $p2);
                else
                    return $this->hangOutPrivatelyFromFriendsToBFFs($p1, $p2);

            default:
                throw new \InvalidArgumentException('p1 relationship goal is of an unexpected type, "' . $p1->getRelationshipGoal() . '"');
        }
    }

    private function hangOutPrivatelyFromFriendsToBFFs(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' said that they consider ' . $p2->getPet()->getName() . ' a best friend; ' . $p2->getPet()->getName() . ' revealed that they don\'t actually like hanging out with ' . $p1->getPet()->getName() . '! They are no longer friends :(');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' said that they consider ' . $p2->getPet()->getName() . ' a best friend; ' . $p2->getPet()->getName() . ' revealed that they don\'t actually like hanging out with ' . $p1->getPet()->getName() . '! They are no longer friends :|');
                $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p2->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p1->setRelationshipGoal(RelationshipEnum::DISLIKE);
                break;

            case RelationshipEnum::FRIEND:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 45, 45);

            case RelationshipEnum::FRIENDLY_RIVAL:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 40, 40);

            case RelationshipEnum::BFF:
            case RelationshipEnum::FWB:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' said that they consider ' . $p2->getPet()->getName() . ' a best friend; ' . $p2->getPet()->getName() . ' feels the same way! The two are now BFFs! :D')->setIcon('icons/activity-logs/friend');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' said that they consider ' . $p2->getPet()->getName() . ' a best friend; ' . $p2->getPet()->getName() . ' feels the same way! The two are now BFFs! :D')->setIcon('icons/activity-logs/friend');
                $p1->setCurrentRelationship(RelationshipEnum::BFF);
                $p2->setCurrentRelationship(RelationshipEnum::BFF);
                break;

            case RelationshipEnum::MATE:
                return $this->hangOutPrivatelyFromBFFsToMates($p2, $p1);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }

        return [ $log1, $log2 ];
    }

    private function hangOutPrivatelyFromFriendsToFWBs(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' said that they want to be FWBs with ' . $p2->getPet()->getName() . '; ' . $p2->getPet()->getName() . ' revealed that they don\'t actually like hanging out with ' . $p1->getPet()->getName() . '! They are no longer friends :(')->setIcon('icons/activity-logs/breakup');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' said that they want to be FWBs with ' . $p2->getPet()->getName() . '; ' . $p2->getPet()->getName() . ' revealed that they don\'t actually like hanging out with ' . $p1->getPet()->getName() . '! They are no longer friends >:(')->setIcon('icons/activity-logs/breakup');
                $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p2->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p1->setRelationshipGoal(RelationshipEnum::DISLIKE);
                break;

            case RelationshipEnum::FRIEND:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 60, 25);

            case RelationshipEnum::FRIENDLY_RIVAL:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 10, 10);

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 80, 15);

            case RelationshipEnum::FWB:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' said that they\'d like to be FWBs with ' . $p2->getPet()->getName() . '; ' . $p2->getPet()->getName() . ' feels the same way ;)')->setIcon('icons/activity-logs/friend-cute');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' said that they\'d like to be FWBs with ' . $p2->getPet()->getName() . '; ' . $p2->getPet()->getName() . ' feels the same way ;)')->setIcon('icons/activity-logs/friend-cute');
                $p1->setCurrentRelationship(RelationshipEnum::FWB);
                $p2->setCurrentRelationship(RelationshipEnum::FWB);
                break;

            case RelationshipEnum::MATE:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 65, 25);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }

        return [ $log1, $log2 ];
    }

    private function hangOutPrivatelySuggestingMatesWithCompleteRejection(PetRelationship $p1, PetRelationship $p2): array
    {
        $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' wanted to date ' . $p2->getPet()->getName() . ', but ' . $p2->getPet()->getName() . ' revealed that they don\'t actually like hanging out with ' . $p1->getPet()->getName() . '! They are no longer friends :\'(')->setIcon('icons/activity-logs/breakup');
        $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' wanted to date ' . $p2->getPet()->getName() . ', but ' . $p2->getPet()->getName() . ' revealed that they don\'t actually like hanging out with ' . $p1->getPet()->getName() . '! They are no longer friends! >:(')->setIcon('icons/activity-logs/breakup');

        $p1
            ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
            ->setRelationshipGoal(RelationshipEnum::DISLIKE)
        ;

        $p2->setCurrentRelationship(RelationshipEnum::BROKE_UP);

        return [ $log1, $log2 ];
    }

    private function hangOutPrivatelyFromFriendsToMates(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                return $this->hangOutPrivatelySuggestingMatesWithCompleteRejection($p1, $p2);

            case RelationshipEnum::FRIEND:
                if(mt_rand(1, 4) === 1)
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
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' wants to date ' . $p2->getPet()->getName() . '! ' . $p2->getPet()->getName() . ' feels the same way! The two are now dating! <3')->setIcon('icons/activity-logs/friend-cute');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' wants to date ' . $p2->getPet()->getName() . '! ' . $p2->getPet()->getName() . ' feels the same way! The two are now dating! <3')->setIcon('icons/activity-logs/friend-cute');

                $p1->setCurrentRelationship(RelationshipEnum::MATE);
                $p2->setCurrentRelationship(RelationshipEnum::MATE);
                break;

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }

        return [ $log1, $log2 ];
    }

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
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' wants to date ' . $p2->getPet()->getName() . '! ' . $p2->getPet()->getName() . ' feels the same way! The two are now dating! <3')->setIcon('icons/activity-logs/friend-cute');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' wants to date ' . $p2->getPet()->getName() . '! ' . $p2->getPet()->getName() . ' feels the same way! The two are now dating! <3')->setIcon('icons/activity-logs/friend-cute');
                $p1->setCurrentRelationship(RelationshipEnum::MATE);
                $p2->setCurrentRelationship(RelationshipEnum::MATE);
                break;

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }

        return [ $log1, $log2 ];
    }

    private function hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama(PetRelationship $p1, PetRelationship $p2, $chanceP1ChangesMind, $chanceP2ChangesMind)
    {
        $downgradeDescription = [
            RelationshipEnum::DISLIKE => 'break up entirely',
            RelationshipEnum::FRIEND => 'just be friends',
            RelationshipEnum::BFF => 'just be friends - but really good friends -',
            RelationshipEnum::FWB => 'just be friends, but maybe still, you know, _do stuff_',
            RelationshipEnum::FRIENDLY_RIVAL => 'just be friendly rivals',
        ];

        $r = \mt_rand(1, 100);

        if($p1->getPet()->hasMerit(MeritEnum::INTROSPECTIVE))
            $chanceP1ChangesMind = ceil($chanceP1ChangesMind / 4);

        if($p2->getPet()->hasMerit(MeritEnum::INTROSPECTIVE))
            $chanceP2ChangesMind = ceil($chanceP2ChangesMind / 4);

        if($r <= $chanceP1ChangesMind)
        {
            $originalGoal = $p1->getRelationshipGoal();

            $p1->setCurrentRelationship($p2->getRelationshipGoal());

            if(mt_rand(1, 4) !== 1)
                $p1->setRelationshipGoal($p2->getRelationshipGoal());

            $p1Log = (new PetActivityLog())
                ->setPet($p1->getPet())
                ->setEntry($p1->getPet()->getName() . ' wanted to ' . $downgradeDescription[$originalGoal] . ', but ' . $p2->getPet()->getName() . ' was upset, and asked to ' . $downgradeDescription[$p2->getCurrentRelationship()] . '. After talking for a while, ' . $p1->getPet()->getName() . ' agreed...')
            ;

            $p2Log = (new PetActivityLog())
                ->setPet($p2->getPet())
                ->setEntry($p1->getPet()->getName() . ' wanted to ' . $downgradeDescription[$originalGoal] . ', but ' . $p2->getPet()->getName() . ' was upset, and asked to ' . $downgradeDescription[$p2->getCurrentRelationship()] . '. After talking for a while, ' . $p1->getPet()->getName() . ' agreed...')
            ;
        }
        else if($r < $chanceP1ChangesMind + $chanceP2ChangesMind)
        {
            $p2->setCurrentRelationship($p1->getRelationshipGoal());

            if(mt_rand(1, 4) !== 1)
                $p2->setRelationshipGoal($p1->getRelationshipGoal());

            $p1Log = (new PetActivityLog())
                ->setPet($p1->getPet())
                ->setEntry($p1->getPet()->getName() . ' wanted to ' . $downgradeDescription[$p1->getRelationshipGoal()] . '. ' . $p2->getPet()->getName() . ' was upset, but after talking for a while said that it would be okay...')
            ;

            $p2Log = (new PetActivityLog())
                ->setPet($p2->getPet())
                ->setEntry($p1->getPet()->getName() . ' wanted to ' . $downgradeDescription[$p1->getRelationshipGoal()] . '. ' . $p2->getPet()->getName() . ' was upset, but after talking for a while said that it would be okay...')
            ;
        }
        else // break up
        {
            $p1Log = (new PetActivityLog())
                ->setPet($p1->getPet())
                ->setEntry($p1->getPet()->getName() . ' wanted to ' . $downgradeDescription[$p1->getRelationshipGoal()] . '; ' . $p2->getPet()->getName() . ' was really upset! After arguing for a while, the two broke up entirely! :(')
            ;

            $p2Log = (new PetActivityLog())
                ->setPet($p2->getPet())
                ->setEntry($p1->getPet()->getName() . ' wanted to ' . $downgradeDescription[$p1->getRelationshipGoal()] . '; ' . $p2->getPet()->getName() . ' was really upset! After arguing for a while, the two broke up entirely! :(')
            ;

            $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
            $p2->setCurrentRelationship(RelationshipEnum::BROKE_UP);
        }

        return [ $p1Log, $p2Log ];
    }

    private function hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama(PetRelationship $p1, PetRelationship $p2, $chanceP1ChangesMind, $chanceP2ChangesMind)
    {
        $upgradeDescription = [
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
            RelationshipEnum::BFF => 'just be friends - but really good friends -',
            RelationshipEnum::FWB => 'just be friends, but maybe still, you know, _do stuff_',
            RelationshipEnum::MATE => 'date',
        ];

        $downgradeDescription2 = [
            RelationshipEnum::DISLIKE => 'break up entirely',
            RelationshipEnum::FRIENDLY_RIVAL => 'be friendly rivals',
            RelationshipEnum::FRIEND => 'be friends',
            RelationshipEnum::BFF => 'be BFFs',
            RelationshipEnum::FWB => 'be FWBs',
            RelationshipEnum::MATE => 'date',
        ];

        $r = \mt_rand(1, 100);

        if($r <= $chanceP1ChangesMind)
        {
            $p1Log = (new PetActivityLog())
                ->setPet($p1->getPet())
                ->setEntry($p1->getPet()->getName() . ' wanted to ' . $upgradeDescription[$p1->getRelationshipGoal()] . ', but ' . $p2->getPet()->getName() . ' wants to ' . $downgradeDescription[$p2->getRelationshipGoal()] . '. After talking for a while, ' . $p1->getPet()->getName() . ' agreed to ' . $downgradeDescription2[$p2->getRelationshipGoal()] . '...')
            ;

            $p2Log = (new PetActivityLog())
                ->setPet($p2->getPet())
                ->setEntry($p1->getPet()->getName() . ' wanted to ' . $upgradeDescription[$p1->getRelationshipGoal()] . ', but ' . $p2->getPet()->getName() . ' wants to ' . $downgradeDescription[$p2->getRelationshipGoal()] . '. After talking for a while, ' . $p1->getPet()->getName() . ' agreed to ' . $downgradeDescription2[$p2->getRelationshipGoal()] . '...')
            ;

            $p1->setCurrentRelationship($p2->getRelationshipGoal());

            if(mt_rand(1, 4) !== 1)
                $p1->setRelationshipGoal($p2->getRelationshipGoal());
        }
        else if($r < $chanceP1ChangesMind + $chanceP2ChangesMind)
        {
            $p1Log = (new PetActivityLog())
                ->setPet($p1->getPet())
                ->setEntry($p1->getPet()->getName() . ' wanted to ' . $upgradeDescription[$p1->getRelationshipGoal()] . ', but ' . $p2->getPet()->getName() . ' wants to ' . $downgradeDescription[$p2->getRelationshipGoal()] . '. After talking for a while, ' . $p2->getPet()->getName() . ' agreed to ' . $upgradeDescription[$p1->getRelationshipGoal()] . '...')
            ;

            $p2Log = (new PetActivityLog())
                ->setPet($p2->getPet())
                ->setEntry($p1->getPet()->getName() . ' wanted to ' . $upgradeDescription[$p1->getRelationshipGoal()] . ', but ' . $p2->getPet()->getName() . ' wants to ' . $downgradeDescription[$p2->getRelationshipGoal()] . '. After talking for a while, ' . $p2->getPet()->getName() . ' agreed to ' . $upgradeDescription[$p1->getRelationshipGoal()] . '...')
            ;

            $p2->setCurrentRelationship($p1->getRelationshipGoal());

            if(mt_rand(1, 4) !== 1)
                $p2->setRelationshipGoal($p1->getRelationshipGoal());
        }
        else // break up
        {
            $p1Log = (new PetActivityLog())
                ->setPet($p1->getPet())
                ->setEntry($p1->getPet()->getName() . ' wanted to ' . $upgradeDescription[$p1->getRelationshipGoal()] . ', but ' . $p2->getPet()->getName() . ' doesn\'t want that. After arguing for a while, the two broke up entirely! :\'(')
                ->setIcon('icons/activity-logs/breakup')
            ;

            $p2Log = (new PetActivityLog())
                ->setPet($p2->getPet())
                ->setEntry($p1->getPet()->getName() . ' wanted to ' . $upgradeDescription[$p1->getRelationshipGoal()] . ', but ' . $p2->getPet()->getName() . ' doesn\'t want that. After arguing for a while, the two broke up entirely! :\'(')
                ->setIcon('icons/activity-logs/breakup')
            ;

            $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
            $p2->setCurrentRelationship(RelationshipEnum::BROKE_UP);
        }

        $this->em->persist($p1Log);
        $this->em->persist($p2Log);

        return [ $p1Log, $p2Log ];
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

    private function hangOutPrivatelyFromFriendsToDisliked(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! The feeling is mutual! They are no longer friends >:(');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense! The feeling is mutual! They are no longer friends >:(');
                $p1->setCurrentRelationship(RelationshipEnum::DISLIKE);
                $p2->setCurrentRelationship(RelationshipEnum::DISLIKE);
                break;

            case RelationshipEnum::FRIEND:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! They are no longer friends >:(')->setIcon('icons/activity-logs/breakup');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense, and no longer wants to be friends! ' . $p2->getPet()->getName() . ' thought they had a really good friendship going... :(')->setIcon('icons/activity-logs/breakup');

                $p2->getPet()
                    ->increaseLove(-mt_rand(12, 18))
                    ->increaseEsteem(-mt_rand(8, 12))
                ;

                $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p2
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                ;
                break;

            case RelationshipEnum::FRIENDLY_RIVAL:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 35, 0);

            case RelationshipEnum::BFF:
            case RelationshipEnum::FWB:
            case RelationshipEnum::MATE:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! They are no longer friends >:(')->setIcon('icons/activity-logs/breakup');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense, and no longer wants to be friends! ' . $p2->getPet()->getName() . ' thought they had a really good friendship going, and had been hoping they might be something more :\'(')->setIcon('icons/activity-logs/breakup');

                $p2->getPet()
                    ->increaseLove(-mt_rand(16, 24))
                    ->increaseEsteem(-mt_rand(12, 16))
                ;

                $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p2
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                ;
                break;

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }

        return [ $log1, $log2 ];
    }

    private function hangOutPrivatelyFromFriendsToFriendlyRivals(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' said that they consider ' . $p2->getPet()->getName() . ' a friendly rival, but ' . $p2->getPet()->getName() . ' confessed that they\'re not really interested in hanging out at all anymore! :| The two are no longer friends...');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' suggested being friendly rivals; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out anymore, and said so! The two are no longer friends...');
                $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p2->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p1->setRelationshipGoal(RelationshipEnum::DISLIKE);
                break;

            case RelationshipEnum::FRIEND:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 50, 30);

            case RelationshipEnum::FRIENDLY_RIVAL:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' said that they consider ' . $p2->getPet()->getName() . ' a friendly rival; ' . $p2->getPet()->getName() . ' feels the same way! Let the rivalry begin!! >:)')->setIcon('icons/activity-logs/friend');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' said that they consider ' . $p2->getPet()->getName() . ' a friendly rival; ' . $p2->getPet()->getName() . ' feels the same way! Let the rivalry begin!! >:)')->setIcon('icons/activity-logs/friend');
                $p1->setCurrentRelationship(RelationshipEnum::FRIENDLY_RIVAL);
                $p2->setCurrentRelationship(RelationshipEnum::FRIENDLY_RIVAL);
                break;

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 10, 10);

            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 10, 10);

            case RelationshipEnum::MATE:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 20, 0);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
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
                throw new \InvalidArgumentException('p1 relationship goal is of an unexpected type, "' . $p1->getRelationshipGoal() . '"');
        }
    }

    private function hangOutPrivatelyFromBFFsToFriends(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' wanted to just be friends; ' . $p2->getPet()->getName() . ' has had enough of ' . $p1->getPet()->getName() . '\'s nonsense, and breaks up entirely! :(')->setIcon('icons/activity-logs/breakup');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' wanted to just be friends; ' . $p2->getPet()->getName() . ' has had enough of ' . $p1->getPet()->getName() . '\'s nonsense, and breaks up entirely! >:(')->setIcon('icons/activity-logs/breakup');

                $p1
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                $p2
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;
                break;

            case RelationshipEnum::FRIENDLY_RIVAL:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 45, 40);

            case RelationshipEnum::FRIEND:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' wants a little more time to theirself; ' . $p2->getPet()->getName() . ' feels the same way. The two are now friends, instead of BFFs.');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' wants a little more time to theirself; ' . $p2->getPet()->getName() . ' feels the same way. The two are now friends, instead of BFFs.');
                $p1->setCurrentRelationship(RelationshipEnum::FRIEND);
                $p2->setCurrentRelationship(RelationshipEnum::FRIEND);
                break;

            case RelationshipEnum::BFF:
            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 15, 65);

            case RelationshipEnum::MATE:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 10, 50);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }

        return [ $log1, $log2 ];
    }

    private function hangOutPrivatelyFromBFFsToFriendlyRivals(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' wanted to stop being BFFs, and just be Friendly Rivals; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! :(');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' wanted to stop being BFFs, and just be Friendly Rivals; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! >:(');

                $p1
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                $p2
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                break;

            case RelationshipEnum::FRIENDLY_RIVAL:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' wanted to turn their friendship with ' . $p2->getPet()->getName() . ' into a Friendly Rivalry; ' . $p2->getPet()->getName() . ' actually feels the same way! BRING IT ON! >:)')->setIcon('icons/activity-logs/friend');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' wanted to turn their friendship with ' . $p2->getPet()->getName() . ' into a Friendly Rivalry; ' . $p2->getPet()->getName() . ' actually feels the same way! BRING IT ON! >:)')->setIcon('icons/activity-logs/friend');

                $p1->setCurrentRelationship(RelationshipEnum::FRIENDLY_RIVAL);
                $p2->setCurrentRelationship(RelationshipEnum::FRIENDLY_RIVAL);

                break;

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

        return [ $log1, $log2 ];

    }

    private function hangOutPrivatelyFromBFFsToFWBs(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' said that they want to be FWBs with ' . $p2->getPet()->getName() . '; ' . $p2->getPet()->getName() . ' revealed that they don\'t actually like hanging out with ' . $p1->getPet()->getName() . '! They are no longer friends :|');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' said that they want to be FWBs with ' . $p2->getPet()->getName() . '; ' . $p2->getPet()->getName() . ' revealed that they don\'t actually like hanging out with ' . $p1->getPet()->getName() . '! They are no longer friends :|');
                $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p2->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p1->setRelationshipGoal(RelationshipEnum::DISLIKE);
                break;

            case RelationshipEnum::FRIENDLY_RIVAL:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 25, 25);

            case RelationshipEnum::FRIEND:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 40, 40);

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 70, 25);

            case RelationshipEnum::FWB:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' said that they\'d like to be FWBs with ' . $p2->getPet()->getName() . '; ' . $p2->getPet()->getName() . ' feels the same way ;)')->setIcon('icons/activity-logs/friend-cute');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' said that they\'d like to be FWBs with ' . $p2->getPet()->getName() . '; ' . $p2->getPet()->getName() . ' feels the same way ;)')->setIcon('icons/activity-logs/friend-cute');
                $p1->setCurrentRelationship(RelationshipEnum::FWB);
                $p2->setCurrentRelationship(RelationshipEnum::FWB);
                break;

            case RelationshipEnum::MATE:
                return $this->hangOutPrivatelySuggestingRelationshipUpgradeWithChanceForDrama($p1, $p2, 50, 45);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }

        return [ $log1, $log2 ];
    }

    private function hangOutPrivatelyFromBFFsToDisliked(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! The feeling is mutual! They are no longer friends >:(');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense! The feeling is mutual! They are no longer friends >:(');
                $p1->setCurrentRelationship(RelationshipEnum::DISLIKE);
                $p2->setCurrentRelationship(RelationshipEnum::DISLIKE);
                break;

            case RelationshipEnum::FRIEND:
            case RelationshipEnum::FRIENDLY_RIVAL:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! They are no longer friends >:(');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense, and no longer wants to be BFFs, or even friends at all! To be honest, ' . $p2->getPet()->getName() . ' felt the whole BFF thing was a bit much, anyway >:(');

                $p2->getPet()
                    ->increaseLove(-mt_rand(4, 8))
                    ->increaseEsteem(-mt_rand(1, 4))
                ;

                $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p2
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                ;
                break;

            case RelationshipEnum::BFF:
            case RelationshipEnum::FWB:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! They are no longer friends >:(')->setIcon('icons/activity-logs/breakup');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense, and no longer wants to be BFFs, or even friends at all! ' . $p2->getPet()->getName() . ' thought they had a really good friendship going... :(')->setIcon('icons/activity-logs/breakup');

                $p2->getPet()
                    ->increaseLove(-mt_rand(12, 18))
                    ->increaseEsteem(-mt_rand(8, 12))
                ;

                $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p2
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                ;
                break;

            case RelationshipEnum::MATE:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! They are no longer friends >:(');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense, and no longer wants to be BFFs, or friends at all! ' . $p2->getPet()->getName() . ' thought they had a really good friendship going, and had been hoping they might be something more :\'(');

                $p2->getPet()
                    ->increaseLove(-mt_rand(16, 24))
                    ->increaseEsteem(-mt_rand(12, 16))
                ;

                $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p2
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                ;
                break;

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
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
                throw new \InvalidArgumentException('p1 relationship goal is of an unexpected type, "' . $p1->getRelationshipGoal() . '"');
        }
    }

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
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' wants to date ' . $p2->getPet()->getName() . '! ' . $p2->getPet()->getName() . ' feels the same way! The two are now dating! <3')->setIcon('icons/activity-logs/friend-cute');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' wants to date ' . $p2->getPet()->getName() . '! ' . $p2->getPet()->getName() . ' feels the same way! The two are now dating! <3')->setIcon('icons/activity-logs/friend-cute');
                $p1->setCurrentRelationship(RelationshipEnum::MATE);
                $p2->setCurrentRelationship(RelationshipEnum::MATE);
                break;

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }

        return [ $log1, $log2 ];

    }

    private function hangOutPrivatelyFromFWBsToBFFs(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' wanted to stop being intimate, and just be BFFs; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! :(')->setIcon('icons/activity-logs/breakup');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' wanted to stop being intimate, and just be BFFs; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! >:(')->setIcon('icons/activity-logs/breakup');

                $p1
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                $p2
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                break;

            case RelationshipEnum::FRIENDLY_RIVAL:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 20, 20);

            case RelationshipEnum::FRIEND:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 40, 40);

            case RelationshipEnum::BFF:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' wanted to stop being intimate, and just be BFFs; ' . $p2->getPet()->getName() . ' actually feels the same way! It\'s a difficult transition, but they\'re both committed to making it work! :)')->setIcon('icons/activity-logs/friend');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' wanted to stop being intimate, and just be BFFs; ' . $p2->getPet()->getName() . ' actually feels the same way! It\'s a difficult transition, but they\'re both committed to making it work! :)')->setIcon('icons/activity-logs/friend');

                $p1->setCurrentRelationship(RelationshipEnum::BFF);
                $p2->setCurrentRelationship(RelationshipEnum::BFF);

                break;

            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 45, 45);

            case RelationshipEnum::MATE:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 35, 35);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }

        return [ $log1, $log2 ];
    }

    private function hangOutPrivatelyFromFWBsToFriends(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' wanted to stop being intimate, and just be Friends; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! :(');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' wanted to stop being intimate, and just be Friends; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! >:(');

                $p1
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                $p2
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                break;

            case RelationshipEnum::FRIENDLY_RIVAL:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 45, 45);

            case RelationshipEnum::FRIEND:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' wanted to stop being intimate, and just be Friends; ' . $p2->getPet()->getName() . ' actually feels the same way! It\'s a difficult transition, but they\'re both committed to making it work!')->setIcon('icons/activity-logs/friend');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' wanted to stop being intimate, and just be Friends; ' . $p2->getPet()->getName() . ' actually feels the same way! It\'s a difficult transition, but they\'re both committed to making it work!')->setIcon('icons/activity-logs/friend');

                $p1->setCurrentRelationship(RelationshipEnum::FRIEND);
                $p2->setCurrentRelationship(RelationshipEnum::FRIEND);

                break;

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 45, 45);

            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 35, 35);

            case RelationshipEnum::MATE:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 30, 15);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }

        return [ $log1, $log2 ];
    }

    private function hangOutPrivatelyFromFWBsToFriendlyRivals(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' wanted to stop being intimate, and just be Friendly Rivals; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! :(')->setIcon('icons/activity-logs/breakup');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' wanted to stop being intimate, and just be Friendly Rivals; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! >:(')->setIcon('icons/activity-logs/breakup');

                $p1
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                $p2
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                break;

            case RelationshipEnum::FRIENDLY_RIVAL:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' wanted to stop being intimate, and just be Friendly Rivals; unexpectedly, ' . $p2->getPet()->getName() . ' actually feels the same way! BRING IT ON! >:)')->setIcon('icons/activity-logs/friend');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry('Unexpectedly, ' . $p1->getPet()->getName() . ' wanted to stop being intimate, and just be Friendly Rivals; ' . $p2->getPet()->getName() . ' actually feels the same way! BRING IT ON! >:)')->setIcon('icons/activity-logs/friend');

                $p1->setCurrentRelationship(RelationshipEnum::FRIENDLY_RIVAL);
                $p2->setCurrentRelationship(RelationshipEnum::FRIENDLY_RIVAL);

                break;

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

        return [ $log1, $log2 ];
    }

    private function hangOutPrivatelyFromFWBsToDisliked(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! The feeling is mutual! They are no longer friends >:(');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense! The feeling is mutual! They are no longer friends >:(');
                $p1->setCurrentRelationship(RelationshipEnum::DISLIKE);
                $p2->setCurrentRelationship(RelationshipEnum::DISLIKE);
                break;

            case RelationshipEnum::FRIENDLY_RIVAL:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 20, 0);
                break;

            case RelationshipEnum::FRIEND:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 40, 0);
                break;

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 30, 0);
                break;

            case RelationshipEnum::FWB:
                // negotiate for a less-involved relationship
                $p2->setRelationshipGoal(ArrayFunctions::pick_one([ RelationshipEnum::BFF, RelationshipEnum::FRIEND, RelationshipEnum::FRIEND ]));
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 35, 0);
                break;

            case RelationshipEnum::MATE:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! They are no longer friends >:(')->setIcon('icons/activity-logs/breakup');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense, and no longer wants to be FWBs, or friends at all! ' . $p2->getPet()->getName() . ' thought they had a really good friendship going, and had been hoping they might be something more :\'(')->setIcon('icons/activity-logs/breakup');

                $p2->getPet()
                    ->increaseLove(-mt_rand(16, 24))
                    ->increaseEsteem(-mt_rand(12, 16))
                ;

                $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p2
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                ;
                break;

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
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
                throw new \InvalidArgumentException('p1 relationship goal is of an unexpected type, "' . $p1->getRelationshipGoal() . '"');
        }
    }

    private function hangOutPrivatelyFromMatesToFriendlyRivals(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' wanted to just be Friendly Rivals; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! :(')->setIcon('icons/activity-logs/breakup');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' wanted to just be Friendly Rivals; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! >:(')->setIcon('icons/activity-logs/breakup');

                $p1
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                $p2
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                break;

            case RelationshipEnum::FRIENDLY_RIVAL:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' wanted to just be Friendly Rivals; unexpectedly, ' . $p2->getPet()->getName() . ' actually feels the same way! Okay, then! BRING IT ON! >:)')->setIcon('icons/activity-logs/friend');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry('Unexpectedly, ' . $p1->getPet()->getName() . ' wanted to just be Friendly Rivals; ' . $p2->getPet()->getName() . ' actually feels the same way! Okay, then! BRING IT ON! >:)')->setIcon('icons/activity-logs/friend');

                $p1->setCurrentRelationship(RelationshipEnum::FRIENDLY_RIVAL);
                $p2->setCurrentRelationship(RelationshipEnum::FRIENDLY_RIVAL);

                break;

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

        return [ $log1, $log2 ];
    }

    private function hangOutPrivatelyFromMatesToBFFs(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' wanted to just be BFFs; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! :\'(')->setIcon('icons/activity-logs/breakup');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' wanted to just be BFFs; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! >:(')->setIcon('icons/activity-logs/breakup');

                $p1
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                $p2
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                break;

            case RelationshipEnum::FRIENDLY_RIVAL:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 20, 20);

            case RelationshipEnum::FRIEND:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 45, 45);

            case RelationshipEnum::BFF:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' wanted to just be BFFs; ' . $p2->getPet()->getName() . ' actually feels the same way! It\'s a difficult transition, but they\'re both committed to making it work! :)')->setIcon('icons/activity-logs/friend');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' wanted to just be BFFs; ' . $p2->getPet()->getName() . ' actually feels the same way! It\'s a difficult transition, but they\'re both committed to making it work! :)')->setIcon('icons/activity-logs/friend');

                $p1->setCurrentRelationship(RelationshipEnum::BFF);
                $p2->setCurrentRelationship(RelationshipEnum::BFF);

                break;

            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 50, 40);

            case RelationshipEnum::MATE:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 35, 35);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }

        return [ $log1, $log2 ];
    }

    private function hangOutPrivatelyFromMatesToFWBs(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' wanted to just be FWBs; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! :\'(')->setIcon('icons/activity-logs/breakup');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' wanted to just be FWBs; ' . $p2->getPet()->getName() . ' doesn\'t actually want to hang out at all anymore, and breaks up entirely! >:(')->setIcon('icons/activity-logs/breakup');

                $p1
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                $p2
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                break;

            case RelationshipEnum::FRIENDLY_RIVAL:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 15, 15);

            case RelationshipEnum::FRIEND:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 25, 25);

            case RelationshipEnum::BFF:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 35, 35);

            case RelationshipEnum::FWB:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' wanted to just be FWBs; unexpectedly, ' . $p2->getPet()->getName() . ' actually feels the same way! ;)')->setIcon('icons/activity-logs/friend-cute');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry('Unexpectedly, ' . $p1->getPet()->getName() . ' wanted to just be FWBs; ' . $p2->getPet()->getName() . ' actually feels the same way! ;)')->setIcon('icons/activity-logs/friend-cute');

                $p1->setCurrentRelationship(RelationshipEnum::FWB);
                $p2->setCurrentRelationship(RelationshipEnum::FWB);

                break;

            case RelationshipEnum::MATE:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 35, 35);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }

        return [ $log1, $log2 ];
    }

    private function hangOutPrivatelyFromMatesToFriends(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' wanted to just be friends; ' . $p2->getPet()->getName() . ' has had enough of ' . $p1->getPet()->getName() . '\'s nonsense, and breaks up entirely! :\'(')->setIcon('icons/activity-logs/breakup');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' wanted to just be friends; ' . $p2->getPet()->getName() . ' has had enough of ' . $p1->getPet()->getName() . '\'s nonsense, and breaks up entirely! >:(')->setIcon('icons/activity-logs/breakup');

                $p1
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                $p2
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                ;

                break;

            case RelationshipEnum::FRIENDLY_RIVAL:
            case RelationshipEnum::FRIEND:
            case RelationshipEnum::BFF:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' wanted to just be friends; after talking for a bit, ' . $p2->getPet()->getName() . ' agrees that that\'d be best... :(');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' wanted to just be friends; after talking for a bit, ' . $p2->getPet()->getName() . ' agrees that that\'d be best... :(');

                $p1->setCurrentRelationship(RelationshipEnum::FRIEND);
                $p2->setCurrentRelationship(RelationshipEnum::FRIEND);

                break;

            case RelationshipEnum::FWB:
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 15, 60);

            case RelationshipEnum::MATE:
                $p2->setRelationshipGoal(ArrayFunctions::pick_one([ RelationshipEnum::FWB, RelationshipEnum::MATE ]));
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 15, 60);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }

        return [ $log1, $log2 ];
    }

    private function hangOutPrivatelyFromMatesToDisliked(PetRelationship $p1, PetRelationship $p2): array
    {
        switch($p2->getRelationshipGoal())
        {
            case RelationshipEnum::DISLIKE:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' doesn\'t like ' . $p2->getPet()->getName() . ' anymore. The feeling is mutual! They\'re breaking up! >:(');
                $log2 = (new PetActivityLog())->setPet($p2->getPet())->setEntry($p1->getPet()->getName() . ' said they don\'t like ' . $p2->getPet()->getName() . ' anymore. The feeling is mutual! They\'re breaking up! >:(');
                $p1->setCurrentRelationship(RelationshipEnum::DISLIKE);
                $p2->setCurrentRelationship(RelationshipEnum::DISLIKE);
                break;

            case RelationshipEnum::FRIEND:
            case RelationshipEnum::FRIENDLY_RIVAL:
                $log1 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' is tired of ' . $p2->getPet()->getName() . '\'s nonsense! They are no longer friends >:(');
                $log2 = (new PetActivityLog())->setPet($p1->getPet())->setEntry($p1->getPet()->getName() . ' said they\'re tired of ' . $p2->getPet()->getName() . '\'s nonsense, and wants to break up! To be honest, ' . $p2->getPet()->getName() . ' felt the whole dating thing was a bit much, anyway >:(');

                $p2->getPet()
                    ->increaseLove(-mt_rand(12, 18))
                    ->increaseEsteem(-mt_rand(8, 12))
                ;

                $p1->setCurrentRelationship(RelationshipEnum::BROKE_UP);
                $p2
                    ->setRelationshipGoal(RelationshipEnum::DISLIKE)
                    ->setCurrentRelationship(RelationshipEnum::BROKE_UP)
                ;
                break;

            case RelationshipEnum::BFF:
            case RelationshipEnum::FWB:
            case RelationshipEnum::MATE:
                // negotiate for a less-involved relationship
                $p2->setRelationshipGoal(ArrayFunctions::pick_one([ RelationshipEnum::FWB, RelationshipEnum::BFF, RelationshipEnum::FRIEND ]));
                return $this->hangOutPrivatelySuggestingRelationshipDowngradeWithChanceForDrama($p1, $p2, 20, 0);

            default:
                throw new \InvalidArgumentException('p2 relationship goal is of an unexpected type, "' . $p2->getRelationshipGoal() . '"');
        }

        return [ $log1, $log2 ];
    }
}