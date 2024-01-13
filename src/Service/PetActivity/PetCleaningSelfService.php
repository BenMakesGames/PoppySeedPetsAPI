<?php

namespace App\Service\PetActivity;

use App\Entity\Greenhouse;
use App\Entity\Pet;
use App\Enum\EnumInvalidValueException;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityStatEnum;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ActivityHelpers;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PlayerLogFactory;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\WeatherService;
use Doctrine\ORM\EntityManagerInterface;

class PetCleaningSelfService
{
    public function __construct(
        private PetExperienceService $petExperienceService,
        private InventoryService $inventoryService,
        private EntityManagerInterface $em,
        private IRandom $rng
    )
    {
    }

    public function cleanUpStatusEffect(Pet $pet, string $statusEffect, string $itemOnBody): bool
    {
        $changes = new PetChanges($pet);

        $pet->removeStatusEffect($pet->getStatusEffect($statusEffect));
        $weather = WeatherService::getWeather(new \DateTimeImmutable(), $pet);

        if($pet->hasMerit(MeritEnum::GOURMAND))
        {
            $this->cleanUpWithGourmand($pet, $itemOnBody, $changes);
            return false;
        }
        else if($weather->getRainfall() > 0)
        {
            $this->cleanUpWithRain($pet, $itemOnBody, $changes);
            return true;
        }
        else if($this->rng->rngNextBool())
        {
            $this->cleanUpInBirdBath($pet, $itemOnBody, $changes);
            return true;
        }
        else
        {
            $this->cleanUpManually($pet, $itemOnBody, $changes);
            return true;
        }
    }

    /**
     * @throws PSPNotFoundException
     * @throws EnumInvalidValueException
     */
    private function cleanUpInBirdBath(Pet $pet, string $itemOnBody, PetChanges $changes): void
    {
        $pet->increaseEsteem($this->rng->rngNextInt(2, 4));

        $greenhouse = $this->findRandomGreenhouseForCleaningIn();

        if(!$greenhouse)
        {
            $this->cleanUpManually($pet, $itemOnBody, $changes);
            return;
        }

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' cleaned the ' . $itemOnBody . ' off their body in %user:' . $greenhouse->getOwner()->getId() . '.name%\'s bird bath.');

        $this->inventoryService->receiveItem($itemOnBody, $greenhouse->getOwner(), null, $pet->getName() . ' used your birdbath to clean this off of themselves.', LocationEnum::BIRDBATH);
        $this->inventoryService->receiveItem($pet->getSpecies()->getSheds(), $greenhouse->getOwner(), null, $pet->getName() . ' used your birdbath to clean themselves off, and incidentally left this behind...', LocationEnum::BIRDBATH);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::OTHER, null);

        $activityLog->setChanges($changes->compare($pet));

        PlayerLogFactory::create(
            $this->em,
            $greenhouse->getOwner(),
            ActivityHelpers::PetName($pet) . ' came to your greenhouse and cleaned some ' . $itemOnBody . ' off their body in your bird bath! (' . ucfirst($pet->getSpecies()->getSheds()->getNameWithArticle()) . ' came off, too!)',
            [ 'Greenhouse', 'Birdbath' ]
        );
    }

    private function findRandomGreenhouseForCleaningIn(): ?Greenhouse
    {
        $qb = $this->em->getRepository(Greenhouse::class)->createQueryBuilder('g');

        $threeDaysAgo = (new \DateTimeImmutable())->modify('-3 days');

        $qb
            ->join('g.owner', 'o')
            ->andWhere('g.hasBirdBath=1')
            ->andWhere('g.visitingBird IS NULL')
            ->andWhere('o.lastActivity >= :threeDaysAgo')
            ->setParameter('threeDaysAgo', $threeDaysAgo);

        $count = $qb->select('COUNT(g)')->getQuery()->getSingleScalarResult();

        if($count === 0)
            return null;

        $offset = $this->rng->rngNextInt(0, $count - 1);

        return $qb
            ->select('g')
            ->setFirstResult($offset)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * @throws \App\Enum\EnumInvalidValueException
     */
    private function cleanUpWithGourmand(Pet $pet, string $itemOnBody, PetChanges $changes): void
    {
        $pet
            ->increaseFood($this->rng->rngNextInt(3, 6))
            ->increaseEsteem($this->rng->rngNextInt(2, 4))
        ;

        $this->petExperienceService->spendTime($pet, 5, PetActivityStatEnum::OTHER, null);

        PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% eats the ' . $itemOnBody . ' off their body in no time flat! (Ah~! A true Gourmand!)')
            ->setChanges($changes->compare($pet))
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Eating', 'Gourmand' ]))
        ;
    }

    /**
     * @throws \App\Enum\EnumInvalidValueException
     */
    private function cleanUpWithRain(Pet $pet, string $itemOnBody, PetChanges $changes): void
    {
        $pet->increaseEsteem($this->rng->rngNextInt(2, 4));
        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% spends some time cleaning the ' . $itemOnBody . ' off their body. The rain made it go much faster!');

        $this->inventoryService->petCollectsItem($itemOnBody, $pet, $pet->getName() . ' cleaned this off their body with the help of the rain...', $activityLog);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(15, 30), PetActivityStatEnum::OTHER, null);

        $activityLog->setChanges($changes->compare($pet));
    }

    /**
     * @throws \App\Enum\EnumInvalidValueException
     */
    private function cleanUpManually(Pet $pet, string $itemOnBody, PetChanges $changes): void
    {
        $pet->increaseEsteem($this->rng->rngNextInt(2, 4));
        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% spends some time cleaning the ' . $itemOnBody . ' off their body...');

        $this->inventoryService->petCollectsItem($itemOnBody, $pet, $pet->getName() . ' cleaned this off their body...', $activityLog);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);

        $activityLog->setChanges($changes->compare($pet));
    }

}