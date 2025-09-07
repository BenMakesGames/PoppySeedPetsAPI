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

use App\Entity\Pet;
use App\Entity\PetQuest;
use Doctrine\ORM\EntityManagerInterface;

class PetQuestRepository
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /**
     * @var array<string, PetQuest>
     */
    private array $petQuestPerRequestCache = [];

    public function findOrCreate(Pet $pet, string $name, mixed $default): PetQuest
    {
        $cacheKey = $pet->getId() . '-' . $name;

        if(!array_key_exists($cacheKey, $this->petQuestPerRequestCache))
        {
            $record = $this->em->getRepository(PetQuest::class)->findOneBy([
                'pet' => $pet,
                'name' => $name,
            ]);

            if(!$record)
            {
                $record = new PetQuest(pet: $pet, name: $name, value: $default);

                $this->em->persist($record);
            }

            $this->petQuestPerRequestCache[$cacheKey] = $record;
        }

        return $this->petQuestPerRequestCache[$cacheKey];
    }
}
