<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Model\PetChangesSummary;
use Doctrine\ORM\EntityManagerInterface;

class ActivityLogService
{
    /** @var PetActivityLog[] */
    private $activityLogs = [];

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function hasActivityLogs(): bool
    {
        return \count($this->activityLogs) > 0;
    }

    /**
     * @return PetActivityLog[]
     */
    public function getActivityLogs()
    {
        return $this->activityLogs;
    }

    public function createActivityLog(Pet $pet, string $entry, ?PetChangesSummary $changes = null)
    {
        $log = (new PetActivityLog())
            ->setPet($pet)
            ->setEntry($entry)
            ->setChanges($changes)
        ;

        $this->activityLogs[] = $log;

        $this->em->persist($log);

        return $log;
    }
}
