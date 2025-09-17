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


namespace App\Service\Typeahead;

use App\Entity\Pet;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends TypeaheadService<Pet>
 */
class PetRelationshipTypeaheadService extends TypeaheadService
{
    private Pet $pet;
    /** @var string[] */ private array $relationships;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em->getRepository(Pet::class));
    }

    /**
     * @param string[] $relationships
     */
    public function setParameters(Pet $pet, array $relationships): void
    {
        $this->pet = $pet;
        $this->relationships = $relationships;
    }

    public function addQueryBuilderConditions(QueryBuilder $qb): QueryBuilder
    {
        return $qb
            ->join('e.petRelationships', 'r', 'WITH', 'r.relationship=:pet')
            ->andWhere('r.currentRelationship IN (:relationships)')
            ->setParameter('pet', $this->pet->getId())
            ->setParameter('relationships', $this->relationships)
        ;
    }
}
