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

    public const STAT_COLORS = [
        PetActivityStatEnum::UMBRA => '#9900FF', // purple
        PetActivityStatEnum::SMITH => '#FFCC00', // yellow
        PetActivityStatEnum::PLASTIC_PRINT => '#FFFFFF', // white
        PetActivityStatEnum::CRAFT => '#FF6600', // orange
        PetActivityStatEnum::MAGIC_BIND => '#FF00FF', // magenta
        PetActivityStatEnum::GATHER => '#33CC00', // green
        PetActivityStatEnum::PROTOCOL_7 => '#336600', // dark green
        PetActivityStatEnum::PROGRAM => '#000000', // black
        PetActivityStatEnum::HUNT => '#CC0000', // red
        PetActivityStatEnum::FISH => '#3399FF', // blue
        PetActivityStatEnum::HANG_OUT => '#FF99FF', // pink
        PetActivityStatEnum::PARK_EVENT => '#996600', // brown
        PetActivityStatEnum::OTHER => '#999999', // gray
    ];

    public const STAT_LABELS = [
        PetActivityStatEnum::UMBRA => 'Umbra',
        PetActivityStatEnum::SMITH => 'Smithing',
        PetActivityStatEnum::PLASTIC_PRINT => '3D Printer',
        PetActivityStatEnum::CRAFT => 'Crafting',
        PetActivityStatEnum::MAGIC_BIND => 'Magic-binding',
        PetActivityStatEnum::GATHER => 'Gathering',
        PetActivityStatEnum::PROTOCOL_7 => 'Protocol 7',
        PetActivityStatEnum::PROGRAM => 'Programming',
        PetActivityStatEnum::HUNT => 'Hunting',
        PetActivityStatEnum::FISH => 'Fishing',
        PetActivityStatEnum::HANG_OUT => 'Hanging Out',
        PetActivityStatEnum::PARK_EVENT => 'Park Event',
        PetActivityStatEnum::OTHER => 'Other',
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