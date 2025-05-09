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

use App\Entity\ParkEvent;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class ParkEventHistoryFilterService
{
    use FilterService;

    public const int PageSize = 20;

    private ?User $user = null;

    /** @var EntityRepository<ParkEvent> */
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repository = $em->getRepository(ParkEvent::class);

        $this->filterer = new Filterer(
            self::PageSize,
            [
                'id' => [ 'e.id' => 'desc' ], // first one is the default
            ],
            [
            ]
        );
    }

    public function allowedPageSizes(): array
    {
        return [ self::PageSize ];
    }

    public function createQueryBuilder(): QueryBuilder
    {
        if(!$this->user) throw new \Exception('User not set.');

        return $this->repository->createQueryBuilder('e')
            ->leftJoin('e.participants', 'p')
            ->andWhere('p.owner = :user')
            ->setParameter('user', $this->user->getId())
        ;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    function applyResultCache(Query $qb, string $cacheKey): Query
    {
        return $qb;
    }
}