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

use App\Entity\Song;
use App\Entity\User;
use App\Entity\UserUnlockedSong;
use App\Enum\PlayerActivityLogTagEnum;
use App\Functions\ArrayFunctions;
use App\Functions\PlayerLogFactory;
use Doctrine\ORM\EntityManagerInterface;

class JukeboxService
{
    public function __construct(
        private readonly EntityManagerInterface $em
    )
    {
    }

    /**
     * @var array<string, ?UserUnlockedSong>
     */
    private array $userSongsPerRequestCache = [];

    public function userHasUnlockedSong(User $user, Song $song): bool
    {
        $cacheKey = $user->getId() . '-' . $song->getId();

        if(!array_key_exists($cacheKey, $this->userSongsPerRequestCache))
        {
            $this->userSongsPerRequestCache[$cacheKey] = $this->em->getRepository(UserUnlockedSong::class)->findOneBy([
                'user' => $user,
                'song' => $song
            ]);
        }

        return $this->userSongsPerRequestCache[$cacheKey] !== null;
    }

    /**
     * @return array{name: string, filename: string, unlocked: bool, unlockedOn: ?\DateTimeImmutable, comment: ?string}[]
     */
    public function getSongsAvailable(User $user): array
    {
        $allSongs = $this->em->getRepository(Song::class)->findAll();

        $unlocked = $user->getUnlockedSongs()->toArray();

        return array_map(
            function(Song $song) use($unlocked) {
                $unlockedSong = ArrayFunctions::find_one($unlocked, fn(UserUnlockedSong $u) => $u->getSong()->getId() === $song->getId());

                if($unlockedSong)
                {
                    return [
                        'name' => $song->getName(),
                        'filename' => $song->getFilename(),
                        'unlocked' => true,
                        'unlockedOn' => $unlockedSong->getUnlockedOn(),
                        'comment' => $unlockedSong->getComment(),
                    ];
                }
                else
                {
                    return [
                        'name' => $song->getName(),
                        'filename' => $song->getFilename(),
                        'unlocked' => false,
                        'unlockedOn' => null,
                        'comment' => null,
                    ];
                }
            },
            $allSongs
        );
    }

    public function playerUnlockSong(User $user, Song $song, string $comment): UserUnlockedSong
    {
        $cacheKey = $user->getId() . '-' . $song->getId();

        if(!array_key_exists($cacheKey, $this->userSongsPerRequestCache) || $this->userSongsPerRequestCache[$cacheKey] === null)
        {
            $unlockedSong = $this->em->getRepository(UserUnlockedSong::class)->findOneBy([
                'user' => $user,
                'song' => $song
            ]);

            if(!$unlockedSong)
            {
                $unlockedSong = (new UserUnlockedSong(user: $user, song: $song))
                    ->setComment($comment)
                ;

                $this->em->persist($unlockedSong);

                PlayerLogFactory::create($this->em, $user, 'You unlocked the song "' . $song->getName() . '"!', [ PlayerActivityLogTagEnum::Music ]);
            }

            $this->userSongsPerRequestCache[$cacheKey] = $unlockedSong;
        }

        return $this->userSongsPerRequestCache[$cacheKey];
    }

    public function unlockStartingSongs(User $user): void
    {
        $startingSongs = $this->em->getRepository(Song::class)->findBy([
            'name' => [
                'frogs_life',
                'good_morning',
                'walking_home',
            ],
        ]);

        foreach($startingSongs as $song)
        {
            $cacheKey = $user->getId() . '-' . $song->getId();

            if(array_key_exists($cacheKey, $this->userSongsPerRequestCache) && $this->userSongsPerRequestCache[$cacheKey] !== null)
                continue;

            $existing = $this->em->getRepository(UserUnlockedSong::class)->findOneBy([
                'user' => $user,
                'song' => $song
            ]);

            if($existing)
            {
                $this->userSongsPerRequestCache[$cacheKey] = $existing;
                continue;
            }

            $unlockedSong = (new UserUnlockedSong(user: $user, song: $song))
                ->setComment('This song came with your Jukebox.')
            ;

            $this->em->persist($unlockedSong);

            $this->userSongsPerRequestCache[$cacheKey] = $unlockedSong;
        }
    }
}
