<?php

namespace App\Command;

use App\Entity\ParkEvent;
use App\Enum\ParkEventTypeEnum;
use App\Functions\ArrayFunctions;
use App\Repository\PetRepository;
use App\Service\ParkEvent\KinBallService;
use App\Service\ParkEvent\TriDChessService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RunParkEventsCommand extends Command
{
    private $kinBallService;
    private $petRepository;
    private $em;
    private $triDChessService;

    public function __construct(
        KinBallService $kinBallService, PetRepository $petRepository, EntityManagerInterface $em,
        TriDChessService $triDChessService
    )
    {
        $this->kinBallService = $kinBallService;
        $this->petRepository = $petRepository;
        $this->em = $em;
        $this->triDChessService = $triDChessService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:run-park-events')
            ->setDescription('Runs park events. Intended to be run every minute of the day (ex: via crontab)')
        ;
    }

    const PARK_EVENT_SIZES = [
        ParkEventTypeEnum::KIN_BALL => 12
    ];

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $now = new \DateTimeImmutable();
        $minuteOfTheDay = (int)$now->format('i') + (int)$now->format('h') * 60;

        $eventTypes = ParkEventTypeEnum::getValues();

        $eventType = $eventTypes[$minuteOfTheDay % count($eventTypes)];

        $output->writeln('Looking for pets to run a ' . $eventType . ' event.');

        switch($eventType)
        {
            case ParkEventTypeEnum::KIN_BALL:
                $parkEvent = $this->playKinBall();
                break;
            case ParkEventTypeEnum::TRI_D_TOURNAMENT:
                $parkEvent = $this->playTriDChess();
                break;
            default:
                throw new \Exception('oops: support for events of type "' . $eventType . '" has not been coded!');
        }

        if($parkEvent)
            $this->em->persist($parkEvent);
        else
            $output->writeln('No park event was run.');

        /*
        foreach($pets as $pet)
        {
            $pet
                ->setLastParkEvent()
                ->setParkEventType(null)
            ;
        }
        */

        $this->em->flush();
    }

    private function playKinBall(): ?ParkEvent
    {
        $pets = $this->petRepository->findPetsEligibleForParkEvent(ParkEventTypeEnum::KIN_BALL, 12);

        echo 'Found ' . count($pets) . ' pets.' . "\n";

        // not enough interested pets? get outta' here!
        if(!$this->kinBallService->isGoodNumberOfPets(count($pets)))
            return null;

        return $this->kinBallService->play($pets);
    }

    private function playTriDChess(): ?ParkEvent
    {
        $idealNumberOfPets = ArrayFunctions::pick_one([ 8, 16, 16, 16, 32 ]);

        $pets = $this->petRepository->findPetsEligibleForParkEvent(ParkEventTypeEnum::TRI_D_TOURNAMENT, $idealNumberOfPets);

        echo 'Found ' . count($pets) . ' pets.' . "\n";

        if(count($pets) > 32)
            $pets = array_slice($pets, 0, 32);
        else if(count($pets) < 32 && count($pets) > 16)
            $pets = array_slice($pets, 0, 16);
        else if(count($pets) < 16 && count($pets) > 8)
            $pets = array_slice($pets, 0, 8);
        else
            return null;

        return $this->triDChessService->play($pets);
    }
}
