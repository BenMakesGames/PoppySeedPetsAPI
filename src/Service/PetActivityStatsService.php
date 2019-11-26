<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetActivityStats;
use App\Enum\PetActivityStatEnum;
use App\Repository\PetActivityStatsRepository;
use Doctrine\ORM\EntityManagerInterface;

class PetActivityStatsService
{
    public const STATS_THAT_CANT_FAIL = [
        PetActivityStatEnum::PARK_EVENT,
        PetActivityStatEnum::HANG_OUT,
        PetActivityStatEnum::OTHER
    ];

    private $petActivityStatsRepository;
    private $em;

    public function __construct(PetActivityStatsRepository $petActivityStatsRepository, EntityManagerInterface $em)
    {
        $this->petActivityStatsRepository = $petActivityStatsRepository;
        $this->em = $em;
    }

    public function logStat(Pet $pet, string $stat, ?bool $success, int $time)
    {
        $stat = strtolower($stat);

        if(!PetActivityStatEnum::isAValue($stat))
            throw new \InvalidArgumentException('$stat must be a PetActivityStatEnum value.');

        $canFail = !in_array($stat, self::STATS_THAT_CANT_FAIL);

        if($canFail)
        {
            if($success === null)
                throw new \InvalidArgumentException('$success must be true or false for ' . $stat . ' events.');

            $countSetter = 'increase' . $stat . ($success ? 'success' : 'failure');
        }
        else
            $countSetter = 'increase' . $stat;

        $timeSetter = 'increase' . $stat . 'time';

        if($pet->getPetActivityStats() === null)
        {
            $petActivityStats = new PetActivityStats();

            $pet->setPetActivityStats($petActivityStats);

            $this->em->persist($petActivityStats);
        }

        $pet->getPetActivityStats()
            ->{$countSetter}()
            ->{$timeSetter}($time)
        ;
    }
}