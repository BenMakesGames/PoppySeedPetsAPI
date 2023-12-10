<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetLocationEnum;
use App\Functions\UserQuestRepository;
use App\Service\PetActivity\SagaSagaService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;

class HouseService
{
    public function __construct(
        private readonly PetActivityService $petActivityService,
        private readonly CacheItemPoolInterface $cache,
        private readonly EntityManagerInterface $em,
        private readonly InventoryService $inventoryService,
        private readonly IRandom $squirrel3,
        private readonly HouseSimService $houseSimService,
        private readonly SagaSagaService $sagaSagaService,
        private readonly PetSocialActivityService $petSocialActivityService
    )
    {
    }

    public function needsToBeRun(User $user)
    {
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
            $fruitBasket = UserQuestRepository::findOrCreate($this->em, $user, 'Received Fruit Basket', false);

            if($fruitBasket->getValue() === false)
            {
                $fruitBasket->setValue(true);
                $this->inventoryService->receiveItem('Fruit Basket', $user, $user, 'There\'s a note attached. It says "Are you settling in alright? Here\'s a little something to help get you started. And don\'t throw away the basket! Equip it to your pet!"', LocationEnum::HOME, true);
                $this->em->flush();
            }
        }

        $petsAtHome = $this->em->getRepository(Pet::class)->createQueryBuilder('p')
            ->join('p.houseTime', 'ht')
            ->andWhere('p.owner=:ownerId')
            ->andWhere('p.location=:home')
            ->setParameter('ownerId', $user->getId())
            ->setParameter('home', PetLocationEnum::HOME)
            ->getQuery()
            ->execute()
        ;

        if(count($petsAtHome) > $user->getMaxPets())
            return;

        $now = new \DateTimeImmutable();

        /** @var Pet[] $petsWithTime */
        // array_filter preserves keys, so we use array_values to reset them, because PHP...
        $petsWithTime = array_values(array_filter($petsAtHome, fn(Pet $pet) =>
            $pet->getHouseTime()->getActivityTime() >= 60 ||
            (
                $pet->getHouseTime()->getSocialEnergy() >= PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT &&
                $pet->getHouseTime()->getCanAttemptSocialHangoutAfter() < $now
            )
        ));

        if(count($petsWithTime) > 0)
        {
            $this->houseSimService->begin($this->em, $user);

            while(count($petsWithTime) > 0)
            {
                $this->squirrel3->rngNextShuffle($petsWithTime);

                $petsWithTime = $this->processPets($petsWithTime);
            }

            $this->houseSimService->end($this->em);

            $this->em->flush();
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
