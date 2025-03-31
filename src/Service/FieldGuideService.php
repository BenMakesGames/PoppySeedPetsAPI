<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Service;

use App\Entity\FieldGuideEntry;
use App\Entity\User;
use App\Entity\UserFieldGuideEntry;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotFoundException;
use App\Functions\UserUnlockedFeatureHelpers;
use Doctrine\ORM\EntityManagerInterface;

class FieldGuideService
{
    public function __construct(
        private readonly ResponseService $responseService,
        private readonly EntityManagerInterface $em
    )
    {
    }

    public function maybeUnlock(User $user, string|FieldGuideEntry $entry, string $unlockComment): void
    {
        if(is_string($entry))
            $entry = $this->em->getRepository(FieldGuideEntry::class)->findOneBy([ 'name' => $entry ]);

        if(!$entry)
            throw new PSPNotFoundException('There is no such Field Guide Entry.');

        $message = null;

        if($this->findOrCreate($user, $entry, $unlockComment)->wasCreated)
            $message = 'You unlocked a new entry in the Field Guide!';

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::FieldGuide))
        {
            UserUnlockedFeatureHelpers::create($this->em, $user, UnlockableFeatureEnum::FieldGuide);
            $message = 'You unlocked the Field Guide! (Check it out in the main menu!)';
        }

        if($message)
            $this->responseService->addFlashMessage($message);
    }

    public function hasUnlocked(User $user, string|FieldGuideEntry $entry): bool
    {
        if(is_string($entry))
            $entry = $this->em->getRepository(FieldGuideEntry::class)->findOneBy([ 'name' => $entry ]);

        return $this->doesExist($user, $entry);
    }

    private $userEntryPerRequestCache = [];

    public function findOrCreate(User $user, FieldGuideEntry $entry, string $comment): FindOrCreateResponse
    {
        $cacheKey = $user->getId() . '-' . $entry->getId();
        $created = false;

        if(array_key_exists($cacheKey, $this->userEntryPerRequestCache))
        {
            $record = $this->userEntryPerRequestCache[$cacheKey];
        }
        else
        {
            $record = $this->em->getRepository(UserFieldGuideEntry::class)->findOneBy([
                'user' => $user,
                'entry' => $entry,
            ]);

            if(!$record)
            {
                $record = $this->create($user, $entry, $comment);
                $created = true;
            }

            $this->userEntryPerRequestCache[$cacheKey] = $record;
        }

        return new FindOrCreateResponse($record, $created);
    }

    private function create(User $user, FieldGuideEntry $entry, string $comment): UserFieldGuideEntry
    {
        $record = (new UserFieldGuideEntry())
            ->setUser($user)
            ->setEntry($entry)
            ->setComment($comment)
        ;

        $this->em->persist($record);

        return $record;
    }

    public function doesExist(User $user, FieldGuideEntry $entry)
    {
        $cacheKey = $user->getId() . '-' . $entry->getId();

        if(!array_key_exists($cacheKey, $this->userEntryPerRequestCache))
        {
            $record = $this->em->getRepository(UserFieldGuideEntry::class)->findOneBy([
                'user' => $user,
                'entry' => $entry,
            ]);

            $this->userEntryPerRequestCache[$cacheKey] = $record;
        }

        return $this->userEntryPerRequestCache[$cacheKey] !== null;
    }
}

class FindOrCreateResponse
{
    public bool $wasCreated;
    public UserFieldGuideEntry $entry;

    public function __construct(UserFieldGuideEntry $entry, bool $wasCreated)
    {
        $this->entry = $entry;
        $this->wasCreated = $wasCreated;
    }
}