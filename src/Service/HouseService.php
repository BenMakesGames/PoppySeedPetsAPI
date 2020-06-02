<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetRelationship;
use App\Entity\User;
use App\Enum\LocationEnum;
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

    public function __construct(
        PetService $petService, PetRepository $petRepository, AdapterInterface $cache, EntityManagerInterface $em,
        UserQuestRepository $userQuestRepository, InventoryService $inventoryService
    )
    {
        $this->petService = $petService;
        $this->petRepository = $petRepository;
        $this->userQuestRepository = $userQuestRepository;
        $this->inventoryService = $inventoryService;
        $this->cache = $cache;
        $this->em = $em;
    }

    public function run(User $user)
    {
        $item = $this->cache->getItem('User #' . $user->getId() . ' - Running House Hours');

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
            ->andWhere('p.owner=:user')
            ->andWhere('(p.time>=60 OR p.socialEnergy>=:minimumSocialEnergy)')
            ->andWhere('p.inDaycare=0')
            ->setParameter('user', $user->getId())
            ->setParameter('minimumSocialEnergy', PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT)
            ->getQuery()
            ->execute();

        while(count($petsWithTime) > 0)
        {
            shuffle($petsWithTime);

            $petsWithTime = $this->processPets($petsWithTime);
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
            if($pet->getTime() >= 60)
                $this->petService->runHour($pet);

            $hungOut = false;

            if($pet->getSocialEnergy() >= PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT)
            {
                $hungOut = $this->petService->runSocialTime($pet);
            }

            $this->em->flush();

            if($this->petCanStillProcess($pet, $hungOut))
                $petsRemaining[] = $pet;
        }

        return $petsRemaining;
    }

    private function petCanStillProcess(Pet $pet, bool $hungOut): bool
    {
        if($pet->getInDaycare())
            return false;

        if($pet->getTime() < 60 && $pet->getSocialEnergy() < PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT)
            return false;

        if($pet->getTime() < 60 && !$hungOut)
            return false;

        return true;
    }
}
