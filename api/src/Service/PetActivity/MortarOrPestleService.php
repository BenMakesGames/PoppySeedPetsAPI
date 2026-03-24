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

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetActivityLogTagEnum;
use App\Enum\PetActivityStatEnum;
use App\Functions\ActivityHelpers;
use App\Functions\EquipmentFunctions;
use App\Functions\MeritRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class MortarOrPestleService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PetExperienceService $petExperienceService,
        private readonly IRandom $rng
    )
    {
    }

    public function findTheOtherBit(Pet $pet): ?PetActivityLog
    {
        if($pet->hasMerit(MeritEnum::MORTARS_AND_PESTLES))
            return null;

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(120, 240), PetActivityStatEnum::OTHER, null);

        $pet->addMerit(MeritRepository::findOneByName($this->em, MeritEnum::MORTARS_AND_PESTLES));

        EquipmentFunctions::destroyPetTool($this->em, $pet);

        return PetActivityLogFactory::createUnreadLog($this->em, $pet, 'After exploring the island for hours looking for the missing part of their Mortar or Pestle, ' . ActivityHelpers::PetName($pet) . ' realized that it was... inside them all along? You\'re not really sure what that means, but ' . ActivityHelpers::PetName($pet) . ' seems content with it. (And they got the Mortars and Pestles Merit out of it, so who are we to judge.)')
            ->addInterestingness(PetActivityLogInterestingness::OneTimeQuestActivity)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Adventure ]))
        ;
    }
}
