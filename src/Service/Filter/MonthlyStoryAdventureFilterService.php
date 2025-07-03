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

use App\Entity\MonthlyStoryAdventure;
use App\Entity\User;
use App\Entity\UserMonthlyStoryAdventureStepCompleted;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class MonthlyStoryAdventureFilterService implements FilterServiceInterface
{
    use FilterService;

    public const int PageSize = 12;

    private EntityManagerInterface $em;
    /** @var EntityRepository<MonthlyStoryAdventure> */
    private EntityRepository $repository;
    private User $user;

    public function __construct(EntityManagerInterface $em, UserAccessor $userAccessor)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(MonthlyStoryAdventure::class);
        $this->user = $userAccessor->getUserOrThrow();

        $this->filterer = new Filterer(
            self::PageSize,
            [
                'releasenumber' => [ 'a.releaseNumber' => 'desc' ], // first one is the default
            ],
            [
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        $qb = $this->repository->createQueryBuilder('a')
            ->orderBy('a.releaseNumber', 'ASC');

        $adventuresCompleted = (int)$this->em->getRepository(UserMonthlyStoryAdventureStepCompleted::class)
            ->createQueryBuilder('c')
            ->select('COUNT(c)')
            ->where('c.user = :user')
            ->setParameter('user', $this->user)
            ->getQuery()
            ->getSingleScalarResult();

        if($adventuresCompleted < 10) // TODO: change this back to 50!
            $qb = $qb->andWhere('a.releaseNumber > 0');

        return $qb;
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
