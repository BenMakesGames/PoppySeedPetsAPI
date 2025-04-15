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
use App\Entity\UserStyle;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class UserStyleFilter
{
    use FilterService;

    public const int PageSize = 12;

    /**
     * @var User
     */
    private User $user;

    private readonly EntityRepository $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repository = $em->getRepository(UserStyle::class);

        $this->filterer = new Filterer(
            self::PageSize,
            [
                'user' => [ 'themeOwner.name' => 'asc', 's.id' => 'desc' ], // first one is the default
            ],
            [
                'following' => $this->filterFollowing(...),
            ]
        );
    }

    public function allowedPageSizes(): array
    {
        return [ self::PageSize ];
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('s')
            ->andWhere('s.name != :current')
            ->setParameter('current', UserStyle::Current)
            ->join('s.user', 'themeOwner')
        ;
    }

    public function filterFollowing(QueryBuilder $qb, $value)
    {
        if(strtolower($value) === 'true' || (int)$value > 0)
        {
            return $qb
                ->join('themeOwner.followedBy', 'followedBy')
                ->andWhere('followedBy.user=:currentUser')
                ->setParameter('currentUser', $this->user)
            ;
        }

        return $qb;
    }

    function applyResultCache(Query $qb, string $cacheKey): Query
    {
        return $qb;
    }
}