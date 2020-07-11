<?php

namespace App\Command;

use App\Entity\ParkEvent;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\ParkEventTypeEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Functions\ArrayFunctions;
use App\Model\ParkEvent\KinBallParticipant;
use App\Model\ParkEvent\TriDChessParticipant;
use App\Model\PetChanges;
use App\Repository\PetRepository;
use App\Repository\UserQuestRepository;
use App\Service\CalendarService;
use App\Service\InventoryService;
use App\Service\ParkEvent\JoustingService;
use App\Service\ParkEvent\KinBallService;
use App\Service\ParkEvent\TriDChessService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunParkEventsCommand extends Command
{
    private $kinBallService;
    private $petRepository;
    private $em;
    private $triDChessService;
    private $joustingService;
    private $userQuestRepository;
    private $calendarService;
    private $inventoryService;
    private $logger;

    public function __construct(
        KinBallService $kinBallService, PetRepository $petRepository, EntityManagerInterface $em,
        TriDChessService $triDChessService, JoustingService $joustingService, UserQuestRepository $userQuestRepository,
        CalendarService $calendarService, InventoryService $inventoryService, LoggerInterface $logger
    )
    {
        $this->kinBallService = $kinBallService;
        $this->petRepository = $petRepository;
        $this->em = $em;
        $this->triDChessService = $triDChessService;
        $this->joustingService = $joustingService;
        $this->userQuestRepository = $userQuestRepository;
        $this->calendarService = $calendarService;
        $this->inventoryService = $inventoryService;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:run-park-events')
            ->setDescription('Runs park events. Intended to be run every minute of the day (ex: via crontab)')
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'The type of park event to run (string value from ParkEventTypeEnum).')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startTime = microtime(true);

        $now = new \DateTimeImmutable();
        $minuteOfTheDay = (int)$now->format('i') + (int)$now->format('h') * 60;

        $eventTypes = ParkEventTypeEnum::getValues();

        $eventType = $input->getOption('type');

        if($eventType === null)
            $eventType = $eventTypes[$minuteOfTheDay % count($eventTypes)];
        else if(!ParkEventTypeEnum::isAValue($eventType))
            throw new \InvalidArgumentException('"' . $eventType . '" is not a valid park event type.');

        $output->writeln('Looking for pets for a ' . $eventType . '.');

        switch($eventType)
        {
            case ParkEventTypeEnum::KIN_BALL:
                $parkEvent = $this->playKinBall();
                break;
            case ParkEventTypeEnum::TRI_D_CHESS:
                $parkEvent = $this->playTriDChess();
                break;
            case ParkEventTypeEnum::JOUSTING:
                $parkEvent = $this->playJousting();
                break;
            default:
                throw new \Exception('oops: support for events of type "' . $eventType . '" has not been coded!');
        }

        if($parkEvent)
        {
            $this->em->getConnection()->executeQuery(
                'UPDATE pet SET park_event_order=FLOOR(RAND() * 2000000000) WHERE park_event_type=:parkEventType',
                [ 'parkEventType' => $eventType ]
            );

            $this->em->persist($parkEvent);

            $birthdayPresentsByUser = [];

            foreach($parkEvent->getParticipants() as $pet)
            {
                $pet
                    ->setLastParkEvent()
                    ->setParkEventType(null)
                ;

                if(mt_rand(1, 10) === 1)
                {
                    $changes = new PetChanges($pet);

                    $log = (new PetActivityLog())
                        ->setPet($pet)
                        ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
                    ;

                    $pet
                        ->increaseEsteem(mt_rand(2, 4))
                        ->increaseSafety(mt_rand(2, 4))
                    ;

                    $balloon = $this->inventoryService->petCollectsRandomBalloon($pet, $pet->getName() . ' found this while participating in a ' . $parkEvent->getType() . ' event!', $log);

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

                if($this->calendarService->isPSPBirthday())
                {
                    $userId = $pet->getOwner()->getId();

                    if(!array_key_exists($userId, $birthdayPresentsByUser))
                        $birthdayPresentsByUser[$userId] = $this->userQuestRepository->findOrCreate($pet->getOwner(), 'PSP Birthday Present ' . date('Y-m-d'), 0);

                    if($birthdayPresentsByUser[$userId]->getValue() < 2)
                    {
                        $birthdayPresentsByUser[$userId]->setValue($birthdayPresentsByUser[$userId]->getValue() + 1);

                        $this->inventoryService->receiveItem(
                            ArrayFunctions::pick_one([
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

            $this->em->flush();
        }

        $runTime = microtime(true) - $startTime;

        if($parkEvent)
        {
            $this->logger->info('Ran park event ' . $parkEvent->getType() . ' with ' . count($parkEvent->getParticipants()) . ' participants. Took ' . round($runTime, 3) . 's.');
        }
        else
        {
            $this->logger->info('No park event to run. Took ' . round($runTime, 3) . 's.');
        }
    }

    private function playKinBall(): ?ParkEvent
    {
        $idealNumberOfPets = 12;

        $pets = $this->petRepository->findPetsEligibleForParkEvent(ParkEventTypeEnum::KIN_BALL, $idealNumberOfPets * 3);

        $pets = $this->sliceSimilarLevel($pets, $idealNumberOfPets, function(Pet $a, Pet $b) {
            return KinBallParticipant::getSkill($a) <=> KinBallParticipant::getSkill($b);
        });

        echo 'Found ' . count($pets) . ' ' . (count($pets) === 1 ? 'pet' : 'pets') . '.' . "\n";

        // not enough interested pets? get outta' here!
        if(!$this->kinBallService->isGoodNumberOfPets(count($pets)))
            return null;

        foreach($pets as $pet)
            echo '* ' . $pet->getName() . ' (#' . $pet->getId() . ') - skill ' . KinBallParticipant::getSkill($pet) . "\n";

        return $this->kinBallService->play($pets);
    }

    private function playTriDChess(): ?ParkEvent
    {
        $idealNumberOfPets = ArrayFunctions::pick_one([ 8, 16, 16 ]);

        $pets = $this->petRepository->findPetsEligibleForParkEvent(ParkEventTypeEnum::TRI_D_CHESS, $idealNumberOfPets * 3);

        $pets = $this->sliceSimilarLevel($pets, $idealNumberOfPets, function(Pet $a, Pet $b) {
            return TriDChessParticipant::getSkill($a) <=> TriDChessParticipant::getSkill($b);
        });

        echo 'Found ' . count($pets) . ' ' . (count($pets) === 1 ? 'pet' : 'pets') . '.' . "\n";

        // we may have asked for 16 pets, but only found enough for an 8-player tournament
        if(count($pets) < 16 && count($pets) > 8)
            $pets = array_slice($pets, 0, 8);
        // didn't find enough? gtfo
        else if(count($pets) < 8)
            return null;

        foreach($pets as $pet)
            echo '* ' . $pet->getName() . ' (#' . $pet->getId() . ') - skill ' . TriDChessParticipant::getSkill($pet) . "\n";

        return $this->triDChessService->play($pets);
    }

    private function playJousting(): ?ParkEvent
    {
        $idealNumberOfPets = ArrayFunctions::pick_one([ 16, 16, 32 ]);

        $pets = $this->petRepository->findPetsEligibleForParkEvent(ParkEventTypeEnum::JOUSTING, $idealNumberOfPets * 3);

        $pets = $this->sliceSimilarLevel($pets, $idealNumberOfPets, function(Pet $a, Pet $b) {
            return $this->joustingService->getPetSkill($a) <=> $this->joustingService->getPetSkill($b);
        });

        echo 'Found ' . count($pets) . ' ' . (count($pets) === 1 ? 'pet' : 'pets') . '.' . "\n";

        // we may have wanted 32 pets, but only found enough for a 16-player tournament
        if(count($pets) < 32 && count($pets) > 16)
            $pets = array_slice($pets, 0, 16);
        // didn't find enough? gtfo
        else if(count($pets) < 16)
            return null;

        foreach($pets as $pet)
            echo '* ' . $pet->getName() . ' (#' . $pet->getId() . ')' . "\n";

        return $this->joustingService->play($pets);
    }

    /**
     * @param Pet[] $pets
     * @return Pet[]
     */
    private function sliceSimilarLevel(array $pets, int $numberWanted, callable $sortMethod): array
    {
        if(count($pets) < $numberWanted)
            return $pets;

        usort($pets, $sortMethod);

        // pick one of the two ends
        $offset = mt_rand(1, 2) === 1 ? 0 : count($pets) - $numberWanted;

        return array_slice($pets, $offset, $numberWanted);
    }
}
