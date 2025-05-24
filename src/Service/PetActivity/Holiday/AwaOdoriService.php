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
use App\Entity\PetActivityLogTag;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\StatusEffectEnum;
use App\Functions\ArrayFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\StatusEffectHelpers;
use App\Model\PetChanges;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class AwaOdoriService
{
    public function __construct(
        private readonly IRandom $rng,
        private readonly EntityManagerInterface $em,
        private readonly PetExperienceService $petExperienceService
    )
    {
    }

    public function adventure(Pet $pet): ?PetActivityLog
    {
        $qb = $this->em->getRepository(Pet::class)->createQueryBuilder('p');

        $qb
            ->leftJoin('p.houseTime', 'houseTime')
            ->andWhere('p.id != :petId')
            ->andWhere('p.lastInteracted >= :threeDaysAgo')
            ->andWhere('houseTime.socialEnergy >= :minimumSocialEnergy')
            ->andWhere('p.food > 0')
            ->setParameter('petId', $pet->getId())
            ->setParameter('threeDaysAgo', new \DateTimeImmutable('-3 days'))
            ->setParameter('minimumSocialEnergy', (PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT * 3) / 2)
        ;

        if($pet->hasStatusEffect(StatusEffectEnum::Wereform))
        {
            $qb
                ->join('p.statusEffects', 'se')
                ->andWhere('se.status = :wereform')
                ->setParameter('wereform', StatusEffectEnum::Wereform)
            ;
        }

        $count = $qb->select('COUNT(p.id)')->getQuery()->getSingleScalarResult();

        if($count == 0)
        {
            return null;
        }

        if($count > 1)
        {
            $numberOfDancingBuddies = $this->rng->rngNextInt(1, 4);

            $qb
                ->setFirstResult($this->rng->rngNextInt(0, max(1, $count - $numberOfDancingBuddies)))
                ->setMaxResults($numberOfDancingBuddies)
            ;
        }
        else
        {
            $qb->setMaxResults(1);
        }

        /** @var Pet[] $dancingBuddies */
        $dancingBuddies = $qb
            ->select('p')
            ->getQuery()
            ->execute();

        $awaOdoriTag = PetActivityLogTagHelpers::findByNames($this->em, [ 'Awa Odori' ]);

        $petNames = [ '%pet:' . $pet->getId() . '.name%' ];

        foreach($dancingBuddies as $buddy)
            $petNames[] = '%pet:' . $buddy->getId() . '.name%';

        $listOfPetNames = ArrayFunctions::list_nice($petNames);

        $activityLog = $this->dance($pet, $listOfPetNames, $awaOdoriTag, false);

        foreach($dancingBuddies as $buddy)
        {
            $buddyActivityLog = $this->dance($buddy, $listOfPetNames, $awaOdoriTag, $buddy->getOwner()->getId() == $pet->getOwner()->getId());
        }

        return $activityLog;
    }

    /**
     * @param PetActivityLogTag[] $activityLogTags
     * @throws \Exception
     */
    private function dance(Pet $pet, string$listOfPetNames, array $activityLogTags, bool $markLogAsRead): PetActivityLog
    {
        $changes = new PetChanges($pet);

        StatusEffectHelpers::applyStatusEffect($this->em, $pet, StatusEffectEnum::DancingLikeAFool, PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT);
        $this->petExperienceService->spendSocialEnergy($pet, PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT);

        $pet->increaseSafety(4)->increaseLove(4)->increaseEsteem(4);

        $log = $markLogAsRead
            ? PetActivityLogFactory::createReadLog($this->em, $pet, $listOfPetNames . ' went out dancing together!')
            : PetActivityLogFactory::createUnreadLog($this->em, $pet, $listOfPetNames . ' went out dancing together!');

        $log
            ->setIcon('calendar/holidays/awa-odori')
            ->setChanges($changes->compare($pet))
            ->addTags($activityLogTags)
            ->addInterestingness(PetActivityLogInterestingness::HolidayOrSpecialEvent)
        ;

        return $log;
    }
}