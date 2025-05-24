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


namespace App\Service\PetActivity\Holiday;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetRelationship;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetActivityLogTagEnum;
use App\Enum\RelationshipEnum;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Model\PetChanges;
use App\Service\PetExperienceService;
use App\Service\PetQuestRepository;
use Doctrine\ORM\EntityManagerInterface;

class HoliService
{
    public const string HOLI_ACTIVITY_LOG_ICON = 'calendar/holidays/holi';

    public function __construct(
        private readonly PetQuestRepository $petQuestRepository,
        private readonly EntityManagerInterface $em,
        private readonly PetExperienceService $petExperienceService
    )
    {
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

        $dislikedPets = $this->getDislikedRelationships($pet);

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
            ->addInterestingness(PetActivityLogInterestingness::HolidayOrSpecialEvent)
            // tags set in other methods
        ;

        return $activityLog;
    }

    /**
     * @return PetRelationship[]
     */
    private function getDislikedRelationships(Pet $pet): array
    {
        $qb = $this->em->getRepository(PetRelationship::class)
            ->createQueryBuilder('r')
            ->leftJoin('r.pet', 'pet')
            ->leftJoin('r.relationship', 'friend')
            ->andWhere('r.currentRelationship IN (:dislikedRelationshipTypes)')
            ->andWhere('pet.id=:petId')
            ->setParameter('petId', $pet->getId())
            ->setParameter('dislikedRelationshipTypes', [ RelationshipEnum::Dislike, RelationshipEnum::BrokeUp ])
        ;

        return $qb->getQuery()->execute();
    }

    private function doPetNoParticipation(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendSocialEnergy($pet, PetExperienceService::SocialEnergyPerHangOut);

        $pet->increaseLove(8);

        return PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' enjoyed watching others participate in Holi this year, but didn\'t have any relationships to repair, themselves.')
            ->setIcon(self::HOLI_ACTIVITY_LOG_ICON)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Holi, 'Special Event' ]));
    }

    private function doReconcileWithPet(Pet $pet, PetRelationship $relationshipToReconcile): PetActivityLog
    {
        $this->petExperienceService->spendSocialEnergy($pet, PetExperienceService::SocialEnergyPerHangOut);

        $otherPet = $relationshipToReconcile->getRelationship();
        $relationshipOtherSide = $otherPet->getRelationshipWith($pet);

        // set current relationship to "Friend"
        $relationshipToReconcile->setCurrentRelationship(RelationshipEnum::Friend);
        $relationshipOtherSide->setCurrentRelationship(RelationshipEnum::Friend);

        // set goal to "Friend" if current goal is "Dislike" or "Broke Up"
        // (I don't THINK "Broke Up" can ever be a goal, but just in case...)
        if(in_array($relationshipToReconcile->getRelationshipGoal(), [ RelationshipEnum::Dislike, RelationshipEnum::BrokeUp ]))
            $relationshipToReconcile->setRelationshipGoal(RelationshipEnum::Friend);

        if(in_array($relationshipOtherSide->getRelationshipGoal(), [ RelationshipEnum::Dislike, RelationshipEnum::BrokeUp ]))
            $relationshipOtherSide->setRelationshipGoal(RelationshipEnum::Friend);

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

        $tags = PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Holi, PetActivityLogTagEnum::Special_Event, PetActivityLogTagEnum::One_on_One_Hangout ]);

        $otherPetLogMessage = 'In the spirit of Holi, ' . ActivityHelpers::PetName($pet) . ' talked with ' . ActivityHelpers::PetName($otherPet) . '. The two reconciled, and are now friends!';

        $otherPetLog = $pet->getOwner()->getId() === $otherPet->getOwner()->getId()
            ? PetActivityLogFactory::createReadLog($this->em, $otherPet, $otherPetLogMessage)
            : PetActivityLogFactory::createUnreadLog($this->em, $otherPet, $otherPetLogMessage);

        $otherPetLog
            ->setIcon(self::HOLI_ACTIVITY_LOG_ICON)
            ->addInterestingness(PetActivityLogInterestingness::RelationshipDiscussion)
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
            ->addInterestingness(PetActivityLogInterestingness::RelationshipDiscussion)
            ->addTags($tags)
        ;

        return $activityLog;
    }
}