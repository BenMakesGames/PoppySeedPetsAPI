<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetLocationEnum;
use App\Functions\SimpleDb;
use App\Repository\PetRepository;
use App\Repository\UserQuestRepository;
use App\Service\PetActivity\SagaSagaService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;

class HouseService
{
    private PetActivityService $petActivityService;
    private UserQuestRepository $userQuestRepository;
    private InventoryService $inventoryService;
    private CacheItemPoolInterface $cache;
    private EntityManagerInterface $em;
    private IRandom $squirrel3;
    private HouseSimService $houseSimService;
    private SagaSagaService $sagaSagaService;
    private PetSocialActivityService $petSocialActivityService;
    private PerformanceProfiler $performanceProfiler;

    public function __construct(
        PetActivityService $petActivityService, CacheItemPoolInterface $cache,
        EntityManagerInterface $em, UserQuestRepository $userQuestRepository, InventoryService $inventoryService,
        Squirrel3 $squirrel3, HouseSimService $houseSimService, SagaSagaService $sagaSagaService,
        PetSocialActivityService $petSocialActivityService, PerformanceProfiler $performanceProfiler
    )
    {
        $this->petActivityService = $petActivityService;
        $this->userQuestRepository = $userQuestRepository;
        $this->inventoryService = $inventoryService;
        $this->cache = $cache;
        $this->em = $em;
        $this->squirrel3 = $squirrel3;
        $this->houseSimService = $houseSimService;
        $this->sagaSagaService = $sagaSagaService;
        $this->petSocialActivityService = $petSocialActivityService;
        $this->performanceProfiler = $performanceProfiler;
    }

    public function needsToBeRun(User $user)
    {
        $time = microtime(true);

        $query = $this->em->getRepository(Pet::class)->createQueryBuilder('p')
            ->select('p.id')
            ->join('p.houseTime', 'ht')
            ->andWhere('p.owner=:user')
            ->andWhere('(ht.activityTime>=60 OR (ht.socialEnergy>=:minimumSocialEnergy AND ht.canAttemptSocialHangoutAfter<CURRENT_TIMESTAMP()))')
            ->andWhere('p.location=:home')
            ->setParameter('user', $user->getId())
            ->setParameter('home', PetLocationEnum::HOME)
            ->setParameter('minimumSocialEnergy', PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT)
            ->setMaxResults(1)
            ->getQuery();

        $petsWithTime = (int)$query->execute();

        $this->performanceProfiler->logExecutionTime(__METHOD__, microtime(true) - $time);

        return $petsWithTime > 0;
    }

    public function needsToBeRunSimpleDb(User $user)
    {
        $time = microtime(true);

        $petsWithTime = SimpleDb::createReadOnlyConnection()
            ->query(
                'SELECT p0_.id AS id_0
                FROM pet p0_
                INNER JOIN pet_house_time p1_ ON p0_.id = p1_.pet_id
                WHERE
                    p0_.owner_id = :userId
                    AND (
                        p1_.activity_time >= 60
                        OR (p1_.social_energy >= :minimumSocialEnergy AND p1_.can_attempt_social_hangout_after < NOW())
                    )
                    AND p0_.location = :home
                LIMIT 1',
                [
                    ':userId' => $user->getId(),
                    ':home' => PetLocationEnum::HOME,
                    ':minimumSocialEnergy' => PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT,
                ]
            )
            ->getSingleValue();

        $this->performanceProfiler->logExecutionTime(__METHOD__, microtime(true) - $time);

        return $petsWithTime > 0;
    }

    public function getHouseRunLock(User $user)
    {
        return $this->cache->getItem('User #' . $user->getId() . ' - Running House Hours');
    }

    public function run(User $user)
    {
        $item = $this->getHouseRunLock($user);

        if($item->isHit())
            return;

        $item->set(true)->expiresAfter(\DateInterval::createFromDateString('1 minute'));
        $this->cache->save($item);

        if($user->getRegisteredOn() < (new \DateTimeImmutable())->modify('-8 hours'))
        {
            $fruitBasket = $this->userQuestRepository->findOrCreate($user, 'Received Fruit Basket', false);

            if($fruitBasket->getValue() === false)
            {
                $fruitBasket->setValue(true);
                $this->inventoryService->receiveItem('Fruit Basket', $user, $user, 'There\'s a note attached. It says "Are you settling in alright? Here\'s a little something to help get you started. And don\'t throw away the basket! Equip it to your pet!"', LocationEnum::HOME, true);
                $this->em->flush();
            }
        }

        $time = microtime(true);

        /** @var Pet[] $petsWithTime */
        $petsWithTime = $this->em->getRepository(Pet::class)->createQueryBuilder('p')
            ->join('p.houseTime', 'ht')
            ->andWhere('p.owner=:user')
            ->andWhere('(ht.activityTime>=60 OR (ht.socialEnergy>=:minimumSocialEnergy AND ht.canAttemptSocialHangoutAfter<:now))')
            ->andWhere('p.location=:home')
            ->setParameter('user', $user->getId())
            ->setParameter('home', PetLocationEnum::HOME)
            ->setParameter('minimumSocialEnergy', PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->execute()
        ;

        $this->performanceProfiler->logExecutionTime(__METHOD__ . ' - Get petsWithTime', microtime(true) - $time);

        if(count($petsWithTime) > 0)
        {
            $this->houseSimService->begin($this->em, $user);

            while(count($petsWithTime) > 0)
            {
                $this->squirrel3->rngNextShuffle($petsWithTime);

                $petsWithTime = $this->processPets($petsWithTime);
            }

            $this->houseSimService->end($this->em);

            $time = microtime(true);
            $this->em->flush();
            $this->performanceProfiler->logExecutionTime(__METHOD__ . ' - Flush', microtime(true) - $time);
        }
    }

    /**
     * @param Pet[] $petsWithTime
     * @return Pet[]
     */
    private function processPets(array $petsWithTime): array
    {
        $petsRemaining = [];

        foreach($petsWithTime as $pet)
        {
            if($pet->getHouseTime()->getActivityTime() >= 60)
            {
                $this->petActivityService->runHour($pet);
            }

            $hungOut = false;

            if($this->petCanRunSocialTime($pet))
            {
                // only one social activity per request, to avoid weird bugs...
                $hungOut = $this->petSocialActivityService->runSocialTime($pet);

                if($hungOut)
                    $this->petSocialActivityService->recomputeFriendRatings($pet);

                $this->houseSimService->setPetHasRunSocialTime($pet);
            }

            if($pet->hasMerit(MeritEnum::SAGA_SAGA) && $this->sagaSagaService->petCompletedSagaSaga($pet))
                break;
            else if($this->petCanStillProcess($pet, $hungOut))
                $petsRemaining[] = $pet;
        }

        return $petsRemaining;
    }

    private function petCanRunSocialTime(Pet $pet)
    {
        return
            $pet->getHouseTime()->getSocialEnergy() >= PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT &&
            $pet->getHouseTime()->getCanAttemptSocialHangoutAfter() < (new \DateTimeImmutable()) &&
            !$this->houseSimService->getPetHasRunSocialTime($pet)
        ;
    }

    private function petCanStillProcess(Pet $pet, bool $hungOut): bool
    {
        if(!$pet->isAtHome())
            return false;

        if($pet->getHouseTime()->getActivityTime() < 60 && $pet->getHouseTime()->getSocialEnergy() < PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT)
            return false;

        if($pet->getHouseTime()->getActivityTime() < 60 && !$hungOut)
            return false;

        return true;
    }
}
