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

use App\Entity\User;
use App\Functions\StringFunctions;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class UserFilterService
{
    use FilterService;

    public const int PageSize = 20;

    private ?User $user = null;

    /** @var EntityRepository<User> */
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repository = $em->getRepository(User::class);

        $this->filterer = new Filterer(
            self::PageSize,
            [
                'lastactivity' => [ 'u.lastActivity' => 'desc' ], // first one is the default
                'registeredon' => [ 'u.registeredOn' => 'asc' ],
                'name' => [ 'u.name' => 'asc' ],
                'id' => [ 'u.id' => 'asc' ],
            ],
            [
                'name' => $this->filterName(...),
                'followedBy' => $this->filterFollowedBy(...),
                'following' => $this->filterFollowing(...),
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('u');
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function filterName(QueryBuilder $qb, mixed $value): void
    {
        $qb
            ->andWhere('u.name LIKE :nameLike')
            ->setParameter('nameLike', '%' . StringFunctions::escapeMySqlWildcardCharacters($value) . '%')
        ;
    }

    public function filterFollowedBy(QueryBuilder $qb, mixed $value): void
    {
        if($this->user && ($value === $this->user->getId() || $this->user->hasRole('ROLE_ADMIN')))
        {
            if(!in_array('f', $qb->getAllAliases()))
                $qb->leftJoin('u.followedBy', 'f');

            $qb
                ->andWhere('f.user = :followedBy')
                ->setParameter('followedBy', (int)$value)
            ;
        }
    }

    public function filterFollowing(QueryBuilder $qb, mixed $value): void
    {
        if($this->user && ($value === $this->user->getId() || $this->user->hasRole('ROLE_ADMIN')))
        {
            if(!in_array('g', $qb->getAllAliases()))
                $qb->leftJoin('u.following', 'g');

            $qb
                ->andWhere('g.following = :following')
                ->setParameter('following', (int)$value)
            ;
        }
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
