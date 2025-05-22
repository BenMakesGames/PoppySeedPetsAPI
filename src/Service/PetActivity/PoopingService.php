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
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetBadgeEnum;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;

// yep. this game has a class called "PoopingService". you're welcome.
class PoopingService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly IRandom $rng,
        private readonly EntityManagerInterface $em
    )
    {
    }

    public function shed(Pet $pet): void
    {
        $this->inventoryService->receiveItem($pet->getSpecies()->getSheds(), $pet->getOwner(), $pet->getOwner(), $pet->getName() . ' shed this.', LocationEnum::HOME);

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% shed some ' . $pet->getSpecies()->getSheds()->getName() . '.')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Shedding']))
            ->addInterestingness(PetActivityLogInterestingness::ActivityUsingMerit)
        ;

        PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::POOPED_SHED_OR_BATHED, $activityLog);
    }

    public function poopDarkMatter(Pet $pet): PetActivityLog
    {
        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name%, um, _created_ some Dark Matter.')
            ->setIcon('items/element/dark-matter')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Pooping']))
            ->addInterestingness(PetActivityLogInterestingness::ActivityUsingMerit)
        ;

        if($this->rng->rngNextInt(1, 20) === 1)
        {
            $this->inventoryService->petCollectsItem('Dark Matter', $pet, $pet->getName() . ' ' . $this->rng->rngNextFromArray([
                'pooped this. Yay?',
                'pooped this. Neat?',
                'pooped this. Yep.',
                'pooped this. Hooray. Poop.'
            ]), $activityLog);
        }
        else
        {
            $this->inventoryService->petCollectsItem('Dark Matter', $pet, $pet->getName() . ' pooped this.', $activityLog);
        }

        PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::POOPED_SHED_OR_BATHED, $activityLog);

        return $activityLog;
    }
}
