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


namespace App\Service;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\PetActivityLogTagEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Model\PetChanges;
use Doctrine\ORM\EntityManagerInterface;

class PetAndPraiseService
{
    public function __construct(
        private readonly PetExperienceService $petExperienceService,
        private readonly CravingService $cravingService,
        private readonly EntityManagerInterface $em,
        private readonly UserStatsService $userStatsRepository
    )
    {
    }

    public function doPet(User $petter, Pet $pet): void
    {
        if(!$pet->isAtHome())
            throw new PSPInvalidOperationException('Pets that aren\'t home cannot be interacted with.');

        $now = new \DateTimeImmutable();

        if($pet->getLastInteracted() >= $now->modify('-4 hours'))
            throw new PSPInvalidOperationException('This pet was already pet recently.');

        $changes = new PetChanges($pet);

        $diff = $now->diff($pet->getLastInteracted());
        $hours = min(48, $diff->h + $diff->days * 24);

        $affection = (int)($hours / 4);
        $gain = (int)ceil($hours / 2.5) + 3;

        $safetyBonus = 0;
        $esteemBonus = 0;

        if($pet->getSafety() > $pet->getEsteem())
        {
            $safetyBonus -= (int)floor($gain / 4);
            $esteemBonus += (int)floor($gain / 4);
        }
        else if($pet->getEsteem() > $pet->getSafety())
        {
            $safetyBonus += (int)floor($gain / 4);
            $esteemBonus -= (int)floor($gain / 4);
        }

        $pet->increaseSafety($gain + $safetyBonus);
        $pet->increaseLove($gain);
        $pet->increaseEsteem($gain + $esteemBonus);
        $this->petExperienceService->gainAffection($pet, $affection);

        $pet->setLastInteracted($now);

        $this->cravingService->maybeAddCraving($pet);

        PetActivityLogFactory::createUnreadLog($this->em, $pet, '%user:' . $petter->getId() . '.Name% pet ' . '%pet:' . $pet->getId() . '.name%.')
            ->setIcon('ui/affection')
            ->setChanges($changes->compare($pet))
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Petting ]))
        ;

        $this->userStatsRepository->incrementStat($petter, UserStatEnum::PETTED_A_PET);
    }

}