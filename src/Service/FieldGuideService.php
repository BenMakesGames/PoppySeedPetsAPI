<?php
namespace App\Service;

use App\Entity\User;
use App\Repository\FieldGuideEntryRepository;
use App\Repository\UserFieldGuideEntryRepository;

class FieldGuideService
{
    private FieldGuideEntryRepository $fieldGuideEntryRepository;
    private UserFieldGuideEntryRepository $userFieldGuideEntryRepository;

    public function __construct(
        FieldGuideEntryRepository $fieldGuideEntryRepository, UserFieldGuideEntryRepository $userFieldGuideEntryRepository
    )
    {
        $this->fieldGuideEntryRepository = $fieldGuideEntryRepository;
        $this->userFieldGuideEntryRepository = $userFieldGuideEntryRepository;
    }

    public function maybeUnlock(User $user, string $entryName, string $unlockComment)
    {
        $entry = $this->fieldGuideEntryRepository->findOneByName($entryName);

        if(!$entry)
            throw new \InvalidArgumentException('There is no Field Guide Entry named "' . $entryName . '"');

        $this->userFieldGuideEntryRepository->findOrCreate($user, $entry, $unlockComment);

        if(!$user->getUnlockedFieldGuide())
            $user->setUnlockedFieldGuide();
    }
}