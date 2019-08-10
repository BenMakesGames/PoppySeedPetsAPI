<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetRelationship;
use App\Repository\PetRelationshipRepository;
use Doctrine\ORM\EntityManagerInterface;

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
    public function groupGathering($pets, string $howMet, int $meetChance = 2)
    {
        for($i = 0; $i < count($pets) - 1; $i++)
        {
            for($j = $i + 1; $j < count($pets); $j++)
            {
                $this->seeAtGroupGathering($pets[$i], $pets[$j], $howMet, $meetChance);
            }
        }
    }

    public function seeAtGroupGathering(Pet $p1, Pet $p2, string $howMet, int $meetChance = 5)
    {
        if($p1->getId() === $p2->getId()) return;

        $alreadyKnow = $p1->hasRelationshipWith($p2) || $p2->hasRelationshipWith($p1);

        $meet =
            $alreadyKnow ||
            mt_rand(1, 100) <= $meetChance ||
            (($p1->wouldBang($p2) || $p2->wouldBang($p1)) && mt_rand(1, 100) <= $meetChance * 5)
        ;

        if($meet)
        {
            if(mt_rand(1, 2) === 1)
            {
                $this->meetOtherPetAtGroupGathering($p1, $p2, $howMet);

                if($alreadyKnow || mt_rand(1, 4) === 1 || $p2->wouldBang($p1))
                    $this->meetOtherPetAtGroupGathering($p2, $p1, $howMet);
            }
            else
            {
                $this->meetOtherPetAtGroupGathering($p2, $p1, $howMet);

                if($alreadyKnow || mt_rand(1, 4) === 1 || $p1->wouldBang($p2))
                    $this->meetOtherPetAtGroupGathering($p1, $p2, $howMet);
            }
        }

    }

    public function meetRoommate(Pet $pet, Pet $otherPet)
    {
        $relationship = $this->petRelationshipRepository->findOneBy([
            'pet' => $pet->getId(),
            'relationship' => $otherPet->getId()
        ]);

        if($relationship === null)
        {
            $relationship = (new PetRelationship())
                ->setRelationship($otherPet)
                ->setMetDescription('Are living together.')
                ->increaseIntimacy(mt_rand(100, 200))
                ->increaseCommitment(mt_rand(200, 350))
            ;

            $pet->addPetRelationship($relationship);

            if($pet->wouldBang($otherPet))
            {
                $relationship
                    ->increasePassion(mt_rand(250 + $pet->getWouldBangFraction() * 5, 600 + $pet->getWouldBangFraction() * 20))
                    ->increaseIntimacy(mt_rand(10, 20) + $pet->getWouldBangFraction() * 2)
                    ->increaseCommitment(mt_rand(0, 10) + $pet->getWouldBangFraction() * 4)
                ;

                $this->responseService->createActivityLog($pet, $pet->getName() . ' met ' . $otherPet->getName() . '. (And what a cutie!)', 'icons/activity-logs/friend-cute');
            }
            else
            {
                $this->responseService->createActivityLog($pet, $pet->getName() . ' met ' . $otherPet->getName() . '.', 'icons/activity-logs/friend');
            }

            $pet
                ->increaseSafety(2)
                ->increaseLove(4)
                ->increaseEsteem(2)
            ;

            $this->em->persist($relationship);
        }
        else
        {
            $relationship
                ->increaseCommitment(mt_rand(150, 300) + mt_rand(10, 20))
                ->increaseIntimacy(mt_rand(10, 20))
            ;

            $pet
                ->increaseSafety(2)
                ->increaseLove(4)
                ->increaseEsteem(2)
            ;

            if($pet->wouldBang($otherPet))
                $relationship->increasePassion(mt_rand(5, 15));     // averages 10
            else if($relationship->getPassion() > 100)
                $relationship->increasePassion(mt_rand(1, 9));      // averages 5
            else if(mt_rand(1, $pet->getWouldBangFraction() * 5) === 1 || $relationship->getPassion() > 0)
                $relationship->increasePassion(mt_rand(1, 3));      // averages 2

            $this->responseService->createActivityLog($pet, $pet->getName() . ' and ' . $otherPet->getName() . ' are living together, now! What a surprise!', 'icons/activity-logs/friend');
        }

        return $relationship;

    }

    public function meetOtherPetAtGroupGathering(Pet $pet, Pet $otherPet, string $howMet)
    {
        $relationship = $this->petRelationshipRepository->findOneBy([
            'pet' => $pet->getId(),
            'relationship' => $otherPet->getId()
        ]);

        if($relationship === null)
        {
            $relationship = (new PetRelationship())
                ->setRelationship($otherPet)
                ->setMetDescription('Met ' . $howMet . '.')
                ->increaseIntimacy(250)
                ->increaseCommitment(50)
            ;

            $pet->addPetRelationship($relationship);

            if($pet->wouldBang($otherPet))
            {
                $relationship
                    ->increasePassion(mt_rand(250 + $pet->getWouldBangFraction() * 5, 600 + $pet->getWouldBangFraction() * 20))
                    ->increaseIntimacy(mt_rand(10, 20) + $pet->getWouldBangFraction() * 2)
                    ->increaseCommitment(mt_rand(0, 10) + $pet->getWouldBangFraction() * 4)
                ;

                $this->responseService->createActivityLog($pet, $pet->getName() . ' met ' . $otherPet->getName() . ' ' . $howMet . '. (And what a cutie!)', 'icons/activity-logs/friend-cute');
            }
            else
            {
                $this->responseService->createActivityLog($pet, $pet->getName() . ' met ' . $otherPet->getName() . ' ' . $howMet . '.', 'icons/activity-logs/friend');
            }

            $pet
                ->increaseSafety(2)
                ->increaseLove(4)
                ->increaseEsteem(2)
            ;

            $this->em->persist($relationship);
        }
        else
        {
            $relationship
                ->increaseCommitment(mt_rand(10, 20))
                ->increaseIntimacy(mt_rand(10, 20))
            ;

            $pet
                ->increaseSafety(2)
                ->increaseLove(4)
                ->increaseEsteem(2)
            ;

            if($pet->wouldBang($otherPet))
                $relationship->increasePassion(mt_rand(5, 15));     // averages 10
            else if($relationship->getPassion() > 100)
                $relationship->increasePassion(mt_rand(1, 9));      // averages 5
            else if(mt_rand(1, $pet->getWouldBangFraction() * 5) === 1 || $relationship->getPassion() > 0)
                $relationship->increasePassion(mt_rand(1, 3));      // averages 2

            $this->responseService->createActivityLog($pet, $pet->getName() . ' and ' . $otherPet->getName() . ' hung out a little ' . $howMet . '.', 'icons/activity-logs/friend');
        }

        return $relationship;
    }

    /**
     * @return PetActivityLog[]
     */
    public function meetOtherPetPrivately(PetRelationship $p1, PetRelationship $p2)
    {
        $pet = $p1->getPet();
        $friend = $p2->getPet();

        $petLowestNeed = $pet->getLowestNeed();
        $friendLowestNeed = $friend->getLowestNeed();

        if($petLowestNeed === '')
        {
            if($friendLowestNeed === '')
            {
                // TODO: describe activity
                $message = $pet->getName() . ' hung out with ' . $friend->getName() . '. They had fun! :)';

                $pet
                    ->increaseLove(mt_rand(2, 4))
                    ->increaseSafety(mt_rand(2, 4))
                    ->increaseEsteem(mt_rand(2, 4))
                ;

                $p1
                    ->increaseCommitment(mt_rand(2, 12))
                    ->increaseIntimacy(mt_rand(2, 12))
                ;

                $friend
                    ->increaseLove(mt_rand(2, 4))
                    ->increaseSafety(mt_rand(2, 4))
                    ->increaseEsteem(mt_rand(2, 4))
                ;

                $p2
                    ->increaseCommitment(mt_rand(2, 12))
                    ->increaseIntimacy(mt_rand(2, 12))
                ;

                if($p1->getPet()->wouldBang($p2->getPet()))
                    $p1->increasePassion(mt_rand(10, 20));  // averages 15
                else if($p1->getPassion() > 100)
                    $p1->increasePassion(mt_rand(2, 12));   // averages 7
                else if(mt_rand(1, $p1->getPet()->getWouldBangFraction() * 5) === 1 || $p1->getPassion() > 0)
                    $p1->increasePassion(mt_rand(1, 5));    // averages 3

                if($p2->getPet()->wouldBang($p1->getPet()))
                    $p2->increasePassion(mt_rand(10, 20));  // averages 15
                else if($p2->getPassion() > 100)
                    $p2->increasePassion(mt_rand(2, 12));   // averages 7
                else if(mt_rand(1, $p2->getPet()->getWouldBangFraction() * 5) === 1 || $p2->getPassion() > 0)
                    $p2->increasePassion(mt_rand(1, 5));    // averages 3
            }
            else
            {
                $message = $pet->getName() . ' hung out with ' . $friend->getName() . ' who wasn\'t actually feeling that great :| ' . $pet->getName() . ' comforted them for a while.';

                $pet
                    ->increaseLove(mt_rand(2, 4))
                    ->increaseEsteem(mt_rand(4, 8))
                ;

                $p1
                    ->increaseCommitment(mt_rand(2, 12))
                    ->increaseIntimacy(mt_rand(1, 5))
                ;

                $friend
                    ->increaseSafety(mt_rand(2, 4))
                    ->increaseLove(mt_rand(2, 4))
                    ->increaseEsteem(mt_rand(2, 4))
                ;

                $p2
                    ->increaseCommitment(mt_rand(5, 20))
                    ->increaseIntimacy(mt_rand(5, 20))
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

                $p1
                    ->increaseCommitment(mt_rand(5, 20))
                    ->increaseIntimacy(mt_rand(5, 20))
                    ->increasePassion(mt_rand(5, 20))
                ;

                $friend
                    ->increaseSafety(mt_rand(2, 4))
                    ->increaseLove(mt_rand(4, 8))
                ;

                $p2
                    ->increaseCommitment(mt_rand(5, 20))
                    ->increaseIntimacy(mt_rand(5, 20))
                    ->increasePassion(mt_rand(5, 20))
                ;
            }
            else
            {
                $message = $pet->getName() . ' was feeling nervous, so hung out with ' . $friend->getName() . '. They huddled up together, and kept each other safe.';

                $pet
                    ->increaseSafety(mt_rand(4, 8))
                    ->increaseLove(mt_rand(2, 4))
                ;

                $p1
                    ->increaseCommitment(mt_rand(5, 20))
                    ->increaseIntimacy(mt_rand(5, 20))
                    ->increasePassion(mt_rand(5, 20))
                ;

                $friend
                    ->increaseEsteem(mt_rand(4, 8))
                    ->increaseLove(mt_rand(2, 4))
                ;

                if($friendLowestNeed === '')
                {
                    $p2
                        ->increaseCommitment(mt_rand(1, 5))
                        ->increaseIntimacy(mt_rand(1, 5))
                        ->increasePassion(mt_rand(1, 5))
                    ;
                }
                else
                {
                    $p2
                        ->increaseCommitment(mt_rand(2, 12))
                        ->increaseIntimacy(mt_rand(2, 12))
                        ->increasePassion(mt_rand(2, 12))
                    ;

                }

            }
        }
        else if($petLowestNeed === 'love')
        {
            $message = $pet->getName() . ' was feeling lonely, so hung out with ' . $friend->getName() . '. They had fun :)';
            $pet
                ->increaseSafety(mt_rand(2, 4))
                ->increaseLove(mt_rand(2, 4))
            ;

            $p1
                ->increaseCommitment(mt_rand(5, 15))
                ->increaseIntimacy(mt_rand(5, 15))
            ;

            if($friendLowestNeed !== 'esteem')
                $friend->increaseSafety(mt_rand(2, 4));

            $friend->increaseLove(mt_rand(2, 4));

            if($friendLowestNeed === 'esteem')
                $friend->increaseEsteem(mt_rand(2, 4));

            $p2
                ->increaseCommitment(mt_rand(5, 15))
                ->increaseIntimacy(mt_rand(5, 15))
            ;
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

                $p1
                    ->increaseCommitment(mt_rand(2, 12))
                    ->increaseIntimacy(mt_rand(5, 20))
                ;

                if($friendLowestNeed === 'safety')
                    $friend->increaseSafety(mt_rand(2, 4));

                $p2
                    ->increaseCommitment(mt_rand(1, 5))
                    ->increaseIntimacy(mt_rand(2, 12))
                ;
            }
            else
            {
                $message = $pet->getName() . ' and ' . $friend->getName() . ' were both feeling down. They complained about other people, and the world. It was kind of negative, but sharing their feelings made them both feel a little better.';

                $pet
                    ->increaseLove(mt_rand(2, 4))
                    ->increaseEsteem(mt_rand(2, 4))
                ;

                $p1
                    ->increaseCommitment(mt_rand(2, 12))
                    ->increaseIntimacy(mt_rand(5, 20))
                ;

                $friend
                    ->increaseLove(mt_rand(2, 4))
                    ->increaseEsteem(mt_rand(2, 4))
                ;

                $p2
                    ->increaseCommitment(mt_rand(2, 12))
                    ->increaseIntimacy(mt_rand(5, 20))
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
}