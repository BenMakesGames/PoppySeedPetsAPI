<?php

namespace App\Command;

use App\Enum\ParkEventTypeEnum;
use App\Repository\PetRepository;
use App\Service\ParkEvent\KinBallService;
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

    public function __construct(KinBallService $kinBallService, PetRepository $petRepository, EntityManagerInterface $em)
    {
        $this->kinBallService = $kinBallService;
        $this->petRepository = $petRepository;
        $this->em = $em;

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

        $pets = $this->petRepository->findBy(
            [ 'parkEventType' => $eventType ],
            [ 'parkEventOrder' => 'ASC' ],
            self::PARK_EVENT_SIZES[$eventType]
        );

        if(count($pets) < self::PARK_EVENT_SIZES[$eventType])
            

        switch($eventType)
        {
            case ParkEventTypeEnum::KIN_BALL:
                $this->kinBallService->play($pets);
                break;
            default:
                throw new \Exception('oops: support for events of type "' . $eventType . '" has not been coded!');
        }

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
}
