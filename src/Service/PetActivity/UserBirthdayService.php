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

use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Functions\ActivityHelpers;
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\UserQuestRepository;
use App\Model\ComputedPetSkills;
use App\Service\InventoryService;
use App\Service\MuseumService;
use Doctrine\ORM\EntityManagerInterface;

class UserBirthdayService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly MuseumService $museumService,
        private readonly EntityManagerInterface $em
    )
    {
    }

    public function doBirthday(ComputedPetSkills $petWithSkills): ?PetActivityLog
    {
        $now = new \DateTimeImmutable();
        $user = $petWithSkills->getPet()->getOwner();
        $registeredOn = $user->getRegisteredOn();

        $birthdayPresentsReceived = UserQuestRepository::findOrCreate($this->em, $user, 'Birthday Presents Received', 0);

        $years = 2 + $birthdayPresentsReceived->getValue();

        if($now < $registeredOn->modify('+' . $years . ' years'))
            return null;

        $anniversaryMuffin = ItemRepository::findOneByName($this->em, 'Anniversary Poppy Seed* Muffin');

        $this->inventoryService->receiveItem($anniversaryMuffin, $user, $user, $petWithSkills->getPet()->getName() . ' made this for your ' . $years . '-year Anniversary!', LocationEnum::Home, true);
        $this->museumService->forceDonateItem($user, $anniversaryMuffin, $petWithSkills->getPet()->getName() . ' made this for your ' . $years . '-year Anniversary!', $user);

        $birthdayPresentsReceived->setValue($birthdayPresentsReceived->getValue() + 1);

        return PetActivityLogFactory::createUnreadLog(
            $this->em,
            $petWithSkills->getPet(),
            'For your ' . $years . '-year Anniversary, ' . ActivityHelpers::PetName($petWithSkills->getPet()) . ' made you a muffin!'
        );
    }
}