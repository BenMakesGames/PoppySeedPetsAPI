<?php
namespace App\Service;

use App\Entity\FieldGuideEntry;
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

    /**
     * @param string|FieldGuideEntry $entry
     */
    public function maybeUnlock(User $user, $entry, string $unlockComment)
    {
        if(is_string($entry))
            $entry = $this->fieldGuideEntryRepository->findOneByName($entry);

        if(!$entry)
            throw new \InvalidArgumentException('There is no such Field Guide Entry.');

        $this->userFieldGuideEntryRepository->findOrCreate($user, $entry, $unlockComment);

        if(!$user->getUnlockedFieldGuide())
            $user->setUnlockedFieldGuide();
    }
}