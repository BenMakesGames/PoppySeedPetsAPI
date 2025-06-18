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


namespace App\Service\PetActivity;

use App\Entity\Greenhouse;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\EnumInvalidValueException;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetBadgeEnum;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ActivityHelpers;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Functions\PlayerLogFactory;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\WeatherService;
use Doctrine\ORM\EntityManagerInterface;

class PetCleaningSelfService
{
    public function __construct(
        private readonly PetExperienceService $petExperienceService,
        private readonly InventoryService $inventoryService,
        private readonly EntityManagerInterface $em,
        private readonly IRandom $rng
    )
    {
    }

    public function cleanUpStatusEffect(Pet $pet, string $statusEffect, string $itemOnBody): bool
    {
        $changes = new PetChanges($pet);

        $pet->removeStatusEffect($pet->getStatusEffect($statusEffect));
        $weather = WeatherService::getWeather(new \DateTimeImmutable());

        if($pet->hasMerit(MeritEnum::GOURMAND))
        {
            $this->cleanUpWithGourmand($pet, $itemOnBody, $changes);
            return false;
        }
        else if($weather->isRaining())
        {
            $this->cleanUpWithRain($pet, $itemOnBody, $changes);
            return true;
        }
        else if($this->rng->rngNextBool())
        {
            $this->cleanUpInBirdBath($pet, $itemOnBody, $changes);
            return true;
        }
        else
        {
            $this->cleanUpManually($pet, $itemOnBody, $changes);
            return true;
        }
    }

    /**
     * @throws PSPNotFoundException
     * @throws EnumInvalidValueException
     */
    private function cleanUpInBirdBath(Pet $pet, string $itemOnBody, PetChanges $changes): void
    {
        $pet->increaseEsteem($this->rng->rngNextInt(2, 4));

        $greenhouse = $this->findRandomGreenhouseForCleaningIn($pet->getOwner());

        if(!$greenhouse)
        {
            $this->cleanUpManually($pet, $itemOnBody, $changes);
            return;
        }

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' cleaned the ' . $itemOnBody . ' off their body in ' . ActivityHelpers::UserNamePossessive($greenhouse->getOwner()) . ' bird bath.');

        $this->inventoryService->receiveItem($itemOnBody, $greenhouse->getOwner(), null, $pet->getName() . ' used your birdbath to clean this off of themselves.', LocationEnum::BirdBath);
        $this->inventoryService->receiveItem($pet->getSpecies()->getSheds(), $greenhouse->getOwner(), null, $pet->getName() . ' used your birdbath to clean themselves off, and incidentally left this behind...', LocationEnum::BirdBath);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::OTHER, null);

        $activityLog->setChanges($changes->compare($pet));

        PlayerLogFactory::create(
            $this->em,
            $greenhouse->getOwner(),
            ActivityHelpers::PetName($pet) . ' came to your greenhouse and cleaned some ' . $itemOnBody . ' off their body in your bird bath! (' . ucfirst($pet->getSpecies()->getSheds()->getNameWithArticle()) . ' came off, too!)',
            [ 'Greenhouse', 'Birdbath' ]
        );
    }

    private function findRandomGreenhouseForCleaningIn(User $exceptUser): ?Greenhouse
    {
        $qb = $this->em->getRepository(Greenhouse::class)->createQueryBuilder('g');

        $threeDaysAgo = (new \DateTimeImmutable())->modify('-3 days');

        $qb
            ->join('g.owner', 'o')
            ->andWhere('g.hasBirdBath=1')
            ->andWhere('g.visitingBird IS NULL')
            ->andWhere('o.lastActivity >= :threeDaysAgo')
            ->andWhere('o.id != :userId')
            ->setParameter('threeDaysAgo', $threeDaysAgo)
            ->setParameter('userId', $exceptUser->getId());

        $count = $qb->select('COUNT(g)')->getQuery()->getSingleScalarResult();

        if($count === 0)
            return null;

        $offset = $this->rng->rngNextInt(0, $count - 1);

        return $qb
            ->select('g')
            ->setFirstResult($offset)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * @throws \App\Enum\EnumInvalidValueException
     */
    private function cleanUpWithGourmand(Pet $pet, string $itemOnBody, PetChanges $changes): void
    {
        $pet
            ->increaseFood($this->rng->rngNextInt(3, 6))
            ->increaseEsteem($this->rng->rngNextInt(2, 4))
        ;

        $this->petExperienceService->spendTime($pet, 5, PetActivityStatEnum::OTHER, null);

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% eats the ' . $itemOnBody . ' off their body in no time flat! (Ah~! A true Gourmand!)')
            ->setChanges($changes->compare($pet))
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Eating', 'Gourmand' ]))
        ;

        PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::POOPED_SHED_OR_BATHED, $activityLog);
    }

    /**
     * @throws \App\Enum\EnumInvalidValueException
     */
    private function cleanUpWithRain(Pet $pet, string $itemOnBody, PetChanges $changes): void
    {
        $pet->increaseEsteem($this->rng->rngNextInt(2, 4));
        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% spends some time cleaning the ' . $itemOnBody . ' off their body. The rain made it go much faster!');

        PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::POOPED_SHED_OR_BATHED, $activityLog);

        $this->inventoryService->petCollectsItem($itemOnBody, $pet, $pet->getName() . ' cleaned this off their body with the help of the rain...', $activityLog);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(15, 30), PetActivityStatEnum::OTHER, null);

        $activityLog->setChanges($changes->compare($pet));
    }

    /**
     * @throws \App\Enum\EnumInvalidValueException
     */
    private function cleanUpManually(Pet $pet, string $itemOnBody, PetChanges $changes): void
    {
        $pet->increaseEsteem($this->rng->rngNextInt(2, 4));
        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% spends some time cleaning the ' . $itemOnBody . ' off their body...');

        PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::POOPED_SHED_OR_BATHED, $activityLog);

        $this->inventoryService->petCollectsItem($itemOnBody, $pet, $pet->getName() . ' cleaned this off their body...', $activityLog);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);

        $activityLog->setChanges($changes->compare($pet));
    }

}