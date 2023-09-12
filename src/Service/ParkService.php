<?php
namespace App\Service;

use App\Entity\ParkEvent;
use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Functions\ActivityHelpers;
use App\Functions\CalendarFunctions;
use App\Functions\DateFunctions;
use App\Model\ParkEvent\ParkEventParticipant;
use App\Model\PetChanges;
use App\Repository\EnchantmentRepository;
use App\Repository\UserQuestRepository;
use Doctrine\ORM\EntityManagerInterface;

class ParkService
{
    private IRandom $squirrel3;
    private InventoryService $inventoryService;
    private UserQuestRepository $userQuestRepository;
    private EntityManagerInterface $em;
    private EnchantmentRepository $enchantmentRepository;
    private HattierService $hattierService;
    private Clock $clock;

    public function __construct(
        Squirrel3 $squirrel3, InventoryService $inventoryService, UserQuestRepository $userQuestRepository,
        EntityManagerInterface $em, EnchantmentRepository $enchantmentRepository, HattierService $hattierService,
        Clock $clock
    )
    {
        $this->squirrel3 = $squirrel3;
        $this->inventoryService = $inventoryService;
        $this->userQuestRepository = $userQuestRepository;
        $this->em = $em;
        $this->enchantmentRepository = $enchantmentRepository;
        $this->hattierService = $hattierService;
        $this->clock = $clock;
    }

    /**
     * @param ParkEventParticipant[] $participants
     */
    public function giveOutParticipationRewards(ParkEvent $parkEvent, array $participants)
    {
        $impressiveAura = $this->enchantmentRepository->findOneByName('Impressive');

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

                $log = (new PetActivityLog())
                    ->setPet($pet)
                    ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
                ;

                $pet
                    ->increaseEsteem($this->squirrel3->rngNextInt(2, 4))
                    ->increaseSafety($this->squirrel3->rngNextInt(2, 4))
                ;

                $comment = $pet->getName() . ' found this while participating in a ' . $parkEvent->getType() . ' event!';

                if($commentExtra)
                    $comment .= ' ' . $commentExtra;

                $balloon = $this->inventoryService->petCollectsRandomBalloon($pet, $comment, $forceBalloon, $log);

                $log
                    ->setEntry($pet->getName() . ' found a ' . $balloon->getItem()->getName() . ' while participating in a ' . $parkEvent->getType() . ' event!')
                    ->setChanges($changes->compare($pet))
                ;

                $this->em->persist($log);

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
                    $birthdayPresentsByUser[$userId] = $this->userQuestRepository->findOrCreate($pet->getOwner(), 'PSP Birthday Present ' . date('Y-m-d'), 0);

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
}