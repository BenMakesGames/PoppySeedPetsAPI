<?php
namespace App\Service;

use App\Entity\Pet;
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
            for($j = $i; $j < count($pets); $j++)
            {
                $this->seeAtGroupGathering($pets[$i], $pets[$j], $howMet, $meetChance);
            }
        }
    }

    public function seeAtGroupGathering(Pet $p1, Pet $p2, string $howMet, int $meetChance = 5)
    {
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

    public function meetOtherPetAtGroupGathering(Pet $pet, Pet $otherPet, string $howMet)
    {
        $relationship = $this->petRelationshipRepository->findOneBy([
            'pet' => $pet->getId(),
            'relationship' => $otherPet->getId()
        ]);

        if($relationship === null)
        {
            $relationship = (new PetRelationship())
                ->setPet($pet)
                ->setRelationship($otherPet)
                ->setMetDescription('Met ' . $howMet . '.')
                ->increaseIntimacy(mt_rand(50, 100))
            ;

            if($pet->wouldBang($otherPet))
            {
                $relationship
                    ->increasePassion(mt_rand(50, mt_rand(100, 200)))
                    ->increaseIntimacy(mt_rand(10, 20) + $pet->getWouldBangFraction() * 2)
                    ->increaseCommitment(mt_rand(0, 10) + $pet->getWouldBangFraction() * 4)
                ;

                $this->responseService->createActivityLog($pet, $pet->getName() . ' met ' . $otherPet->getName() . ' ' . $howMet . '. (And what a cutie!)', 'icons/activity-log/friend-cute');
            }
            else
            {
                $this->responseService->createActivityLog($pet, $pet->getName() . ' met ' . $otherPet->getName() . ' ' . $howMet . '.', 'icons/activity-log/friend');
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

            $this->responseService->createActivityLog($pet, $pet->getName() . ' and ' . $otherPet->getName() . ' hung out a little ' . $howMet . '.', 'icons/activity-log/friend');
        }

        return $relationship;
    }

    public function meetOtherPetPrivately(PetRelationship $p1, PetRelationship $p2)
    {
        // effects for pet 1
        $p1
            ->increaseCommitment(mt_rand(20, 40))   // they're hanging out, so there's commitment
            ->increaseIntimacy(mt_rand(10, 20))
        ;

        $p1->getPet()
            ->increaseSafety(2)
            ->increaseLove(4)
            ->increaseEsteem(2)
        ;

        if($p1->getPet()->wouldBang($p2->getPet()))
            $p1->increasePassion(mt_rand(10, 20));  // averages 15
        else if($p1->getPassion() > 100)
            $p1->increasePassion(mt_rand(2, 12));   // averages 7
        else if(mt_rand(1, $p1->getPet()->getWouldBangFraction() * 5) === 1 || $p1->getPassion() > 0)
            $p1->increasePassion(mt_rand(1, 5));    // averages 3

        // effects for pet 2
        $p2
            ->increaseCommitment(mt_rand(20, 40))   // they're hanging out, so there's commitment
            ->increaseIntimacy(mt_rand(10, 20))
        ;

        $p2->getPet()
            ->increaseSafety(2)
            ->increaseLove(4)
            ->increaseEsteem(2)
        ;

        if($p2->getPet()->wouldBang($p1->getPet()))
            $p2->increasePassion(mt_rand(10, 20));  // averages 15
        else if($p2->getPassion() > 100)
            $p2->increasePassion(mt_rand(2, 12));   // averages 7
        else if(mt_rand(1, $p2->getPet()->getWouldBangFraction() * 5) === 1 || $p2->getPassion() > 0)
            $p2->increasePassion(mt_rand(1, 5));    // averages 3

        // TODO: what happens during this outing?
        // if pets are flirty, increase passion further, or maybe have sexy fun times (increasing intimacy and commitment)
        // chance that pets do something they mutually enjoy (increasing intimacy), depending on mutual commitment

        $this->responseService->createActivityLog($p1->getPet(), $p1->getPet()->getName() . ' and ' . $p2->getPet()->getName() . ' hung out.', 'icons/activity-log/friend');
        $this->responseService->createActivityLog($p2->getPet(), $p2->getPet()->getName() . ' and ' . $p1->getPet()->getName() . ' hung out.', 'icons/activity-log/friend');
    }
}