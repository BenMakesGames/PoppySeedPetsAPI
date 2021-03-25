<?php
namespace App\Service\PetActivity;

use App\Entity\PetActivityLog;
use App\Entity\PetRelationship;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\RelationshipEnum;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\PetQuestRepository;
use App\Repository\PetRelationshipRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;

class HoliService
{
    public const HOLI_ACTIVITY_LOG_ICON = 'ui/holidays/holi';

    private $petRelationshipRepository;
    private $petQuestRepository;
    private $responseService;
    private $em;

    public function __construct(
        PetRelationshipRepository $petRelationshipRepository, PetQuestRepository $petQuestRepository,
        ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $this->petRelationshipRepository = $petRelationshipRepository;
        $this->petQuestRepository = $petQuestRepository;
        $this->responseService = $responseService;
        $this->em = $em;
    }

    public function adventure(ComputedPetSkills $petWithSkills): ?PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $now = new \DateTimeImmutable();

        $reconciledThisYear = $this->petQuestRepository->findOrCreate($pet, 'Holi ' . $now->format('Y') . ' Reconciliation', false);

        // if the pet already participated this year, cancel
        if($reconciledThisYear->getValue())
            return null;

        $reconciledThisYear->setValue(true);

        // okay, let's go:
        $changes = new PetChanges($pet);

        $dislikedPets = $this->petRelationshipRepository->getDislikedRelationships($pet);

        // if the pet doesn't dislike any pets, they will not participate this year
        if(count($dislikedPets) === 0)
        {
            $activityLog = $this->doPetNoParticipation($petWithSkills);
        }
        else
        {
            $relationshipToReconcile = ArrayFunctions::pick_one_weighted($dislikedPets, fn(PetRelationship $r1) =>
                max(1, $r1->getCommitment() >> 1)
            );

            $activityLog = $this->doReconcileWithPet($petWithSkills, $relationshipToReconcile);
        }

        $activityLog
            ->setChanges($changes->compare($pet))
            ->addInterestingness(PetActivityLogInterestingnessEnum::HOLIDAY_OR_SPECIAL_EVENT)
        ;

        return $activityLog;
    }

    private function doPetNoParticipation(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $pet->increaseLove(8);

        return $this->responseService->createActivityLog(
            $pet,
            ActivityHelpers::PetName($pet) . ' enjoyed watching others participate in Holi this year, but didn\'t have any relationships to repair, themselves.',
            self::HOLI_ACTIVITY_LOG_ICON
        );
    }

    private function doReconcileWithPet(ComputedPetSkills $petWithSkills, PetRelationship $relationshipToReconcile): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $otherPet = $relationshipToReconcile->getRelationship();
        $relationshipOtherSide = $otherPet->getRelationshipWith($pet);

        // set current relationship to "Friend"
        $relationshipToReconcile->setCurrentRelationship(RelationshipEnum::FRIEND);
        $relationshipOtherSide->setCurrentRelationship(RelationshipEnum::FRIEND);

        // set goal to "Friend" if current goal is "Dislike" or "Broke Up"
        // (I don't THINK "Broke Up" can ever be a goal, but just in case...)
        if(in_array($relationshipToReconcile->getRelationshipGoal(), [ RelationshipEnum::DISLIKE, RelationshipEnum::BROKE_UP ]))
            $relationshipToReconcile->setRelationshipGoal(RelationshipEnum::FRIEND);

        if(in_array($relationshipOtherSide->getRelationshipGoal(), [ RelationshipEnum::DISLIKE, RelationshipEnum::BROKE_UP ]))
            $relationshipOtherSide->setRelationshipGoal(RelationshipEnum::FRIEND);

        $pet
            ->increaseSafety(8)
            ->increaseLove(8)
            ->increaseEsteem(4)
        ;

        $otherPetChanges = new PetChanges($otherPet);

        $otherPet
            ->increaseSafety(8)
            ->increaseLove(8)
            ->increaseEsteem(4)
        ;

        $otherPetLog = (new PetActivityLog())
            ->setPet($otherPet)
            ->setEntry('In the spirit of Holi, ' . ActivityHelpers::PetName($pet) . ' talked with ' . ActivityHelpers::PetName($otherPet) . '. The two reconciled, and are now friends!')
            ->setIcon(self::HOLI_ACTIVITY_LOG_ICON)
            ->addInterestingness(PetActivityLogInterestingnessEnum::RELATIONSHIP_DISCUSSION)
            ->setChanges($otherPetChanges->compare($otherPet))
        ;

        $this->em->persist($otherPetLog);

        $activityLog = $this->responseService->createActivityLog(
            $pet,
            'In the spirit of Holi, ' . ActivityHelpers::PetName($pet) . ' talked with ' . ActivityHelpers::PetName($otherPet) . '. The two reconciled, and are now friends!',
            self::HOLI_ACTIVITY_LOG_ICON
        );

        $activityLog->addInterestingness(PetActivityLogInterestingnessEnum::RELATIONSHIP_DISCUSSION);

        return $activityLog;
    }
}