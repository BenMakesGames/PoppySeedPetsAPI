<?php
namespace App\Service\PetActivity;

use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Functions\ActivityHelpers;
use App\Model\ComputedPetSkills;
use App\Repository\ItemRepository;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\MuseumService;
use App\Service\ResponseService;

class UserBirthdayService
{
    private UserQuestRepository $userQuestRepository;
    private ResponseService $responseService;
    private InventoryService $inventoryService;
    private MuseumService $museumService;
    private ItemRepository $itemRepository;

    public function __construct(
        UserQuestRepository $userQuestRepository, ResponseService $responseService, InventoryService $inventoryService,
        MuseumService $museumService, ItemRepository $itemRepository
    )
    {
        $this->userQuestRepository = $userQuestRepository;
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->museumService = $museumService;
        $this->itemRepository = $itemRepository;
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

        $anniversaryMuffin = $this->itemRepository->findOneByName('Anniversary Poppy Seed* Muffin');

        $this->inventoryService->receiveItem($anniversaryMuffin, $user, $user, $petWithSkills->getPet()->getName() . ' made this for your ' . $years . '-year Anniversary!', LocationEnum::HOME, true);
        $this->museumService->forceDonateItem($user, $anniversaryMuffin, $petWithSkills->getPet()->getName() . ' made this for your ' . $years . '-year Anniversary!');

        $birthdayPresentsReceived->setValue($birthdayPresentsReceived->getValue() + 1);

        return $this->responseService->createActivityLog(
            $petWithSkills->getPet(),
            'For your ' . $years . '-year Anniversary, ' . ActivityHelpers::PetName($petWithSkills->getPet()) . ' made you a muffin!',
            ''
        );
    }
}