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


namespace App\Service\Filter;

use App\Entity\Pet;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class GuildMemberFilterService implements FilterServiceInterface
{
    use FilterService;

    public const int PageSize = 12;

    /** @var EntityRepository<Pet> */
    private readonly EntityRepository $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repository = $em->getRepository(Pet::class);

        $this->filterer = new Filterer(
            self::PageSize,
            [
                'rank' => [ 'guildMembership.level' => 'desc', 'guildMembership.joinedOn' => 'asc' ],
                'id' => [ 'p.id' => 'asc' ],
            ],
            [
                'guild' => $this->filterGuild(...),
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('p')
            ->join('p.guildMembership', 'guildMembership')
        ;
    }

    public function filterGuild(QueryBuilder $qb, mixed $value): void
    {
        $qb
            ->andWhere('guildMembership.guild=:guild')
            ->setParameter('guild', (int)$value)
        ;
    }

    function applyResultCache(Query $qb, string $cacheKey): Query
    {
        return $qb;
    }

    public function allowedPageSizes(): array
    {
        return [ self::PageSize ];
    }
}
