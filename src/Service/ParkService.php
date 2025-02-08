<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\ParkEvent;
use App\Entity\Pet;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
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
        private readonly IRandom $squirrel3,
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
    public function giveOutParticipationRewards(ParkEvent $parkEvent, array $participants)
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

            if($forceBalloon || $this->squirrel3->rngNextInt(1, 10) === 1)
            {
                $changes = new PetChanges($pet);

                $pet
                    ->increaseEsteem($this->squirrel3->rngNextInt(2, 4))
                    ->increaseSafety($this->squirrel3->rngNextInt(2, 4))
                ;

                [ $balloon, $log ] = $this->petCollectsRandomBalloon($pet, $parkEvent->getType(), $commentExtra, $forceBalloon);

                $log
                    ->setChanges($changes->compare($pet))
                ;

                if(!$pet->getTool())
                {
                    $pet->setTool($balloon);
                    $balloon->setLocation(LocationEnum::WARDROBE);
                }
                else if(!$pet->getHat() && $pet->hasMerit(MeritEnum::BEHATTED))
                {
                    $pet->setHat($balloon);
                    $balloon->setLocation(LocationEnum::WARDROBE);
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
                        $this->squirrel3->rngNextFromArray([
                            'Red PSP B-day Present',
                            'Yellow PSP B-day Present',
                            'Purple PSP B-day Present'
                        ]),
                        $pet->getOwner(),
                        $pet->getOwner(),
                        $pet->getName() . ' got this from participating in a park event!',
                        LocationEnum::HOME,
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
            $balloon = $this->squirrel3->rngNextFromArray([
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
            ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
        ;

        $balloonComment = $pet->getName() . ' found this while participating in a ' . $parkEventType  . ' event!';

        if($commentExtra)
            $balloonComment .= ' ' . $commentExtra;

        $item = $this->inventoryService->petCollectsItem($balloon, $pet, $balloonComment, $log);

        $item->setLockedToOwner($locked);

        return [ $item, $log ];
    }
}