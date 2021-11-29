<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\PetLocationEnum;
use App\Repository\InventoryRepository;
use App\Repository\PetRepository;
use App\Repository\UserQuestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class HouseService
{
    private $petService;
    private $petRepository;
    private $userQuestRepository;
    private $inventoryService;
    private $cache;
    private $em;
    private IRandom $squirrel3;
    private HouseSimService $houseSimService;

    public function __construct(
        PetService $petService, PetRepository $petRepository, AdapterInterface $cache, EntityManagerInterface $em,
        UserQuestRepository $userQuestRepository, InventoryService $inventoryService, Squirrel3 $squirrel3,
        HouseSimService $houseSimService
    )
    {
        $this->petService = $petService;
        $this->petRepository = $petRepository;
        $this->userQuestRepository = $userQuestRepository;
        $this->inventoryService = $inventoryService;
        $this->cache = $cache;
        $this->em = $em;
        $this->squirrel3 = $squirrel3;
        $this->houseSimService = $houseSimService;
    }

    public function needsToBeRun(User $user)
    {
        $petsWithTime = (int)$this->petRepository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->join('p.houseTime', 'ht')
            ->andWhere('p.owner=:user')
            ->andWhere('(ht.activityTime>=60 OR (ht.socialEnergy>=:minimumSocialEnergy AND ht.canAttemptSocialHangoutAfter<:now))')
            ->andWhere('p.location=:home')
            ->setParameter('user', $user->getId())
            ->setParameter('home', PetLocationEnum::HOME)
            ->setParameter('minimumSocialEnergy', PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getSingleScalarResult()
        ;

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

        /** @var Pet[] $petsWithTime */
        $petsWithTime = $this->petRepository->createQueryBuilder('p')
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

        if(count($petsWithTime) > 0)
        {
            $this->houseSimService->begin($user);

            while(count($petsWithTime) > 0)
            {
                $this->squirrel3->rngNextShuffle($petsWithTime);

                $petsWithTime = $this->processPets($petsWithTime);
            }

            $this->houseSimService->end();

            $this->em->flush();
        }
    }

    /**
     * @param Pet[] $petsWithTime
     * @return Pet[]
     */
    private function processPets($petsWithTime): array
    {
        $petsRemaining = [];

        foreach($petsWithTime as $pet)
        {
            if($pet->getHouseTime()->getActivityTime() >= 60)
            {
                $this->petService->runHour($pet);
            }

            $hungOut = false;

            if($this->petCanRunSocialTime($pet))
            {
                // only one social activity per request, to avoid weird bugs...
                $hungOut = $this->petService->runSocialTime($pet);
                $this->houseSimService->setPetHasRunSocialTime($pet);
            }

            if($this->petCanStillProcess($pet, $hungOut))
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
