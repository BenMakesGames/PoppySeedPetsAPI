<?php
namespace App\Service\PetActivity;

use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Functions\ActivityHelpers;
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogFactory;
use App\Model\ComputedPetSkills;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\MuseumService;
use Doctrine\ORM\EntityManagerInterface;

class UserBirthdayService
{
    private UserQuestRepository $userQuestRepository;
    private InventoryService $inventoryService;
    private MuseumService $museumService;
    private EntityManagerInterface $em;

    public function __construct(
        UserQuestRepository $userQuestRepository, InventoryService $inventoryService,
        MuseumService $museumService, EntityManagerInterface $em
    )
    {
        $this->userQuestRepository = $userQuestRepository;
        $this->inventoryService = $inventoryService;
        $this->museumService = $museumService;
        $this->em = $em;
    }

    public function doBirthday(ComputedPetSkills $petWithSkills): ?PetActivityLog
    {
        $now = new \DateTimeImmutable();
        $user = $petWithSkills->getPet()->getOwner();
        $registeredOn = $user->getRegisteredOn();

        $birthdayPresentsReceived = $this->userQuestRepository->findOrCreate($user, 'Birthday Presents Received', 0);

        $years = 2 + $birthdayPresentsReceived->getValue();

        if($now < $registeredOn->modify('+' . $years . ' years'))
            return null;

        $anniversaryMuffin = ItemRepository::findOneByName($this->em, 'Anniversary Poppy Seed* Muffin');

        $this->inventoryService->receiveItem($anniversaryMuffin, $user, $user, $petWithSkills->getPet()->getName() . ' made this for your ' . $years . '-year Anniversary!', LocationEnum::HOME, true);
        $this->museumService->forceDonateItem($user, $anniversaryMuffin, $petWithSkills->getPet()->getName() . ' made this for your ' . $years . '-year Anniversary!', $user);

        $birthdayPresentsReceived->setValue($birthdayPresentsReceived->getValue() + 1);

        return PetActivityLogFactory::createUnreadLog(
            $this->em,
            $petWithSkills->getPet(),
            'For your ' . $years . '-year Anniversary, ' . ActivityHelpers::PetName($petWithSkills->getPet()) . ' made you a muffin!'
        );
    }
}