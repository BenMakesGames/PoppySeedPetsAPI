<?php
namespace App\Service\PetActivity\Holiday;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetRelationship;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\RelationshipEnum;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Functions\PetActivityLogFactory;
use App\Model\PetChanges;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\PetQuestRepository;
use App\Repository\PetRelationshipRepository;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;

class HoliService
{
    public const HOLI_ACTIVITY_LOG_ICON = 'ui/holidays/holi';

    private PetRelationshipRepository $petRelationshipRepository;
    private PetQuestRepository $petQuestRepository;
    private EntityManagerInterface $em;
    private PetExperienceService $petExperienceService;

    public function __construct(
        PetRelationshipRepository $petRelationshipRepository, PetQuestRepository $petQuestRepository,
        EntityManagerInterface $em, PetExperienceService $petExperienceService
    )
    {
        $this->petRelationshipRepository = $petRelationshipRepository;
        $this->petQuestRepository = $petQuestRepository;
        $this->em = $em;
        $this->petExperienceService = $petExperienceService;
    }

    public function adventure(Pet $pet): ?PetActivityLog
    {
        $now = new \DateTimeImmutable();

        $reconciledThisYear = $this->petQuestRepository->findOrCreate($pet, 'Holi ' . $now->format('Y') . ' Reconciliation', false);

        // if the pet already participated this year, cancel
        if($reconciledThisYear->getValue())
            return null;

        if($pet->hasMerit(MeritEnum::AFFECTIONLESS))
            return null;

        $reconciledThisYear->setValue(true);

        // okay, let's go:
        $changes = new PetChanges($pet);

        $dislikedPets = $this->petRelationshipRepository->getDislikedRelationships($pet);

        // if the pet doesn't dislike any pets, they will not participate this year
        if(count($dislikedPets) === 0)
        {
            $activityLog = $this->doPetNoParticipation($pet);
        }
        else
        {
            $relationshipToReconcile = ArrayFunctions::pick_one_weighted($dislikedPets, fn(PetRelationship $r1) =>
                max(1, $r1->getCommitment() >> 1)
            );

            $activityLog = $this->doReconcileWithPet($pet, $relationshipToReconcile);
        }

        $activityLog
            ->setChanges($changes->compare($pet))
            ->addInterestingness(PetActivityLogInterestingnessEnum::HOLIDAY_OR_SPECIAL_EVENT)
            // tags set in other methods
        ;

        return $activityLog;
    }

    private function doPetNoParticipation(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendSocialEnergy($pet, PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT);

        $pet->increaseLove(8);

        return PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' enjoyed watching others participate in Holi this year, but didn\'t have any relationships to repair, themselves.')
            ->setIcon(self::HOLI_ACTIVITY_LOG_ICON)
            ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Holi', 'Special Event' ]));
    }

    private function doReconcileWithPet(Pet $pet, PetRelationship $relationshipToReconcile): PetActivityLog
    {
        $this->petExperienceService->spendSocialEnergy($pet, PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT);

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

        $tags = PetActivityLogTagRepository::findByNames($this->em, [ 'Holi', 'Special Event', '1-on-1 Hangout' ]);

        $otherPetLogMessage = 'In the spirit of Holi, ' . ActivityHelpers::PetName($pet) . ' talked with ' . ActivityHelpers::PetName($otherPet) . '. The two reconciled, and are now friends!';

        $otherPetLog = $pet->getOwner()->getId() === $otherPet->getOwner()->getId()
            ? PetActivityLogFactory::createReadLog($this->em, $otherPet, $otherPetLogMessage)
            : PetActivityLogFactory::createUnreadLog($this->em, $otherPet, $otherPetLogMessage);

        $otherPetLog
            ->setIcon(self::HOLI_ACTIVITY_LOG_ICON)
            ->addInterestingness(PetActivityLogInterestingnessEnum::RELATIONSHIP_DISCUSSION)
            ->setChanges($otherPetChanges->compare($otherPet))
            ->addTags($tags)
        ;

        $activityLog = PetActivityLogFactory::createUnreadLog(
            $this->em,
            $pet,
            'In the spirit of Holi, ' . ActivityHelpers::PetName($pet) . ' talked with ' . ActivityHelpers::PetName($otherPet) . '. The two reconciled, and are now friends!',
        );

        $activityLog
            ->setIcon(self::HOLI_ACTIVITY_LOG_ICON)
            ->addInterestingness(PetActivityLogInterestingnessEnum::RELATIONSHIP_DISCUSSION)
            ->addTags($tags)
        ;

        return $activityLog;
    }
}