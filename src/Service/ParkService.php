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

use App\Entity\ParkEvent;
use App\Entity\Pet;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingness;
use App\Functions\ActivityHelpers;
use App\Functions\CalendarFunctions;
use App\Functions\DateFunctions;
use App\Functions\EnchantmentRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\UserQuestRepository;
use App\Model\ParkEvent\ParkEventParticipant;
use App\Model\PetChanges;
use Doctrine\ORM\EntityManagerInterface;

class ParkService
{
    public function __construct(
        private readonly IRandom $rng,
        private readonly InventoryService $inventoryService,
        private readonly EntityManagerInterface $em,
        private readonly HattierService $hattierService,
        private readonly Clock $clock
    )
    {
    }

    /**
     * @param ParkEventParticipant[] $participants
     */
    public function giveOutParticipationRewards(ParkEvent $parkEvent, array $participants): void
    {
        $impressiveAura = EnchantmentRepository::findOneByName($this->em, 'Impressive');

        $birthdayPresentsByUser = [];

        $forceBalloon = null;
        $commentExtra = null;

        switch(DateFunctions::getFullMoonName($this->clock->now))
        {
            case 'Wolf':
                $forceBalloon = '"Wolf" Balloon';
                $commentExtra = '(There\'s a Wolf Moon out today! IRL! It\'s true!)';
                break;
            case 'Pink':
                $forceBalloon = 'Pink Balloon';
                $commentExtra = '(There\'s a Pink Moon out today! IRL! It\'s true!)';
                break;
        }

        foreach($participants as $participant)
        {
            $pet = $participant->getPet();

            $pet->setLastParkEvent();

            if($forceBalloon || $this->rng->rngNextInt(1, 10) === 1)
            {
                $changes = new PetChanges($pet);

                $pet
                    ->increaseEsteem($this->rng->rngNextInt(2, 4))
                    ->increaseSafety($this->rng->rngNextInt(2, 4))
                ;

                [ $balloon, $log ] = $this->petCollectsRandomBalloon($pet, $parkEvent->getType(), $commentExtra, $forceBalloon);

                $log
                    ->setChanges($changes->compare($pet))
                ;

                if(!$pet->getTool())
                {
                    $pet->setTool($balloon);
                    $balloon->setLocation(LocationEnum::Wardrobe);
                }
                else if(!$pet->getHat() && $pet->hasMerit(MeritEnum::BEHATTED))
                {
                    $pet->setHat($balloon);
                    $balloon->setLocation(LocationEnum::Wardrobe);
                }
            }

            if(
                $participant->getIsWinner() &&
                $pet->hasMerit(MeritEnum::BEHATTED) &&
                !$this->hattierService->userHasUnlocked($pet->getOwner(), $impressiveAura)
            )
            {
                $this->hattierService->unlockAuraDuringPetActivity(
                    $pet,
                    $participant->getActivityLog(),
                    $impressiveAura,
                    '(Impressive victory! Wear it proudly!)',
                    '(Impressive victory!)',
                    ActivityHelpers::PetName($pet) . ' won a ' . $parkEvent->getType() . ' event!'
                );
            }

            if(CalendarFunctions::isPSPBirthday($this->clock->now))
            {
                $userId = $pet->getOwner()->getId();

                if(!array_key_exists($userId, $birthdayPresentsByUser))
                    $birthdayPresentsByUser[$userId] = UserQuestRepository::findOrCreate($this->em, $pet->getOwner(), 'PSP Birthday Present ' . date('Y-m-d'), 0);

                if($birthdayPresentsByUser[$userId]->getValue() < 2)
                {
                    $birthdayPresentsByUser[$userId]->setValue($birthdayPresentsByUser[$userId]->getValue() + 1);

                    $this->inventoryService->receiveItem(
                        $this->rng->rngNextFromArray([
                            'Red PSP B-day Present',
                            'Yellow PSP B-day Present',
                            'Purple PSP B-day Present'
                        ]),
                        $pet->getOwner(),
                        $pet->getOwner(),
                        $pet->getName() . ' got this from participating in a park event!',
                        LocationEnum::Home,
                        true
                    );
                }
            }
        }
    }

    public function petCollectsRandomBalloon(Pet $pet, string $parkEventType, ?string $commentExtra, ?string $specificBalloon): array
    {
        if($specificBalloon)
        {
            $balloon = $specificBalloon;
            $locked = true;
        }
        else
        {
            $balloon = $this->rng->rngNextFromArray([
                'Red Balloon',
                'Orange Balloon',
                'Yellow Balloon',
                'Green Balloon',
                'Blue Balloon',
                'Purple Balloon',
            ]);
            $locked = false;
        }

        $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, $pet->getName() . ' found a ' . $balloon . ' while participating in a ' . $parkEventType . ' event!')
            ->addInterestingness(PetActivityLogInterestingness::RareActivity)
        ;

        $balloonComment = $pet->getName() . ' found this while participating in a ' . $parkEventType  . ' event!';

        if($commentExtra)
            $balloonComment .= ' ' . $commentExtra;

        $item = $this->inventoryService->petCollectsItem($balloon, $pet, $balloonComment, $log);

        $item->setLockedToOwner($locked);

        return [ $item, $log ];
    }
}