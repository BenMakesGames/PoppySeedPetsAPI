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

namespace App\Command;

use App\Entity\ParkEvent;
use App\Entity\Pet;
use App\Enum\ParkEventTypeEnum;
use App\Enum\PetLocationEnum;
use App\Enum\StatusEffectEnum;
use App\Model\ParkEvent\KinBallParticipant;
use App\Model\ParkEvent\TriDChessParticipant;
use App\Service\IRandom;
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
    private KinBallService $kinBallService;
    private EntityManagerInterface $em;
    private TriDChessService $triDChessService;
    private JoustingService $joustingService;
    private LoggerInterface $logger;
    private IRandom $rng;

    public function __construct(
        KinBallService $kinBallService, EntityManagerInterface $em,
        TriDChessService $triDChessService, JoustingService $joustingService, LoggerInterface $logger,
        IRandom $rng
    )
    {
        $this->kinBallService = $kinBallService;
        $this->em = $em;
        $this->triDChessService = $triDChessService;
        $this->joustingService = $joustingService;
        $this->logger = $logger;
        $this->rng = $rng;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:run-park-events')
            ->setDescription('Runs park events. Intended to be run every minute of the day (ex: via crontab)')
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'The type of park event to run (string value from ParkEventTypeEnum).')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
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

        $parkEvent = match ($eventType)
        {
            ParkEventTypeEnum::KinBall => $this->playKinBall(),
            ParkEventTypeEnum::TriDChess => $this->playTriDChess(),
            ParkEventTypeEnum::Jousting => $this->playJousting(),
            default => throw new \Exception('oops: support for events of type "' . $eventType . '" has not been coded!'),
        };

        if($parkEvent)
        {
            $this->em->getConnection()->executeQuery(
                'UPDATE pet SET park_event_order=FLOOR(RAND() * 2000000000) WHERE park_event_type=:parkEventType',
                [ 'parkEventType' => $eventType ]
            );

            $this->em->persist($parkEvent);

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

        return self::SUCCESS;
    }

    private function playKinBall(): ?ParkEvent
    {
        $idealNumberOfPets = 12;

        $pets = self::findPetsEligibleForParkEvent($this->em, ParkEventTypeEnum::KinBall, $idealNumberOfPets * 3);

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
        $idealNumberOfPets = $this->rng->rngNextFromArray([ 8, 16, 16 ]);

        $pets = self::findPetsEligibleForParkEvent($this->em, ParkEventTypeEnum::TriDChess, $idealNumberOfPets * 3);

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
        $idealNumberOfPets = $this->rng->rngNextFromArray([ 16, 16, 32 ]);

        $pets = self::findPetsEligibleForParkEvent($this->em, ParkEventTypeEnum::Jousting, $idealNumberOfPets * 3);

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
        $offset = $this->rng->rngNextInt(1, 2) === 1 ? 0 : count($pets) - $numberWanted;

        return array_slice($pets, $offset, $numberWanted);
    }

    /**
     * @return Pet[]
     */
    public static function findPetsEligibleForParkEvent(EntityManagerInterface $em, string $eventType, int $number): array
    {
        $today = new \DateTimeImmutable();

        $pets = $em->getRepository(Pet::class)->createQueryBuilder('p')
            //->join('p.skills', 's')
            ->leftJoin('p.statusEffects', 'statusEffects')
            ->andWhere('p.parkEventType=:eventType')
            ->andWhere('(p.lastParkEvent<:today OR p.lastParkEvent IS NULL)')
            ->andWhere('p.location=:home')
            ->andWhere('p.lastInteracted>=:twoDaysAgo')
            ->orderBy('p.parkEventOrder', 'ASC')
            ->setMaxResults($number)
            ->setParameter('eventType', $eventType)
            ->setParameter('home', PetLocationEnum::HOME)
            ->setParameter('today', $today->format('Y-m-d'))
            ->setParameter('twoDaysAgo', $today->modify('-48 hours')->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult()
        ;

        return array_values(array_filter($pets, fn(Pet $pet) => !$pet->hasStatusEffect(StatusEffectEnum::Wereform)));
    }
}
