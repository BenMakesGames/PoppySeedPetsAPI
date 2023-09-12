<?php
namespace App\Service;

use App\Entity\FieldGuideEntry;
use App\Entity\User;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotFoundException;
use App\Functions\UserUnlockedFeatureHelpers;
use App\Repository\FieldGuideEntryRepository;
use App\Repository\UserFieldGuideEntryRepository;
use Doctrine\ORM\EntityManagerInterface;

class FieldGuideService
{
    private FieldGuideEntryRepository $fieldGuideEntryRepository;
    private UserFieldGuideEntryRepository $userFieldGuideEntryRepository;
    private ResponseService $responseService;
    private EntityManagerInterface $em;

    public function __construct(
        FieldGuideEntryRepository $fieldGuideEntryRepository,
        UserFieldGuideEntryRepository $userFieldGuideEntryRepository,
        ResponseService $responseService,
        EntityManagerInterface $em
    )
    {
        $this->fieldGuideEntryRepository = $fieldGuideEntryRepository;
        $this->userFieldGuideEntryRepository = $userFieldGuideEntryRepository;
        $this->responseService = $responseService;
        $this->em = $em;
    }

    /**
     * @param string|FieldGuideEntry $entry
     */
    public function maybeUnlock(User $user, $entry, string $unlockComment)
    {
        if(is_string($entry))
            $entry = $this->fieldGuideEntryRepository->findOneByName($entry);

        if(!$entry)
            throw new PSPNotFoundException('There is no such Field Guide Entry.');

        $message = null;

        if($this->userFieldGuideEntryRepository->findOrCreate($user, $entry, $unlockComment)->wasCreated)
            $message = 'You unlocked a new entry in the Field Guide!';

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::FieldGuide))
        {
            UserUnlockedFeatureHelpers::create($this->em, $user, UnlockableFeatureEnum::FieldGuide);
            $message = 'You unlocked the Field Guide! (Check it out in the main menu!)';
        }

        if($message)
            $this->responseService->addFlashMessage($message);
    }

    /**
     * @param string|FieldGuideEntry $entry
     */
    public function hasUnlocked(User $user, $entry): bool
    {
        if(is_string($entry))
            $entry = $this->fieldGuideEntryRepository->findOneByName($entry);

        return $this->userFieldGuideEntryRepository->doesExist($user, $entry);
    }
}