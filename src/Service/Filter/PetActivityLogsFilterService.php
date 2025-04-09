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

use App\Entity\PetActivityLog;
use App\Exceptions\PSPFormValidationException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;

class PetActivityLogsFilterService
{
    use FilterService;

    public const PageSize = 20;

    private readonly ObjectRepository $repository;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->repository = $doctrine->getRepository(PetActivityLog::class, 'readonly');

        $this->filterer = new Filterer(
            self::PageSize,
            [
                'id' => [ 'l.id' => 'desc' ], // first one is the default
                'interestingness' => [ 'l.interestingness' => 'desc' ],
            ],
            [
                'pet' => $this->filterPet(...),
                'date' => $this->filterDate(...),
                'user' => $this->filterUser(...),
                'tags' => $this->filterTags(...),
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('l');
    }

    public function filterDate(QueryBuilder $qb, $value)
    {
        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $value);

        if($date === false)
            throw new PSPFormValidationException('"date" must be in yyyy-mm-dd format.');

        $date = $date->setTime(0, 0, 0);

        $this->filterer->setPageSize(200);

        $qb
            ->andWhere('l.createdOn >= :date')
            ->andWhere('l.createdOn < :datePlus1')
            ->setParameter('date', $date->format('Y-m-d 00:00:00'))
            ->setParameter('datePlus1', $date->modify('+1 day')->format('Y-m-d 00:00:00'))
        ;
    }

    public function filterPet(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('l.pet = :pet')
            ->setParameter('pet', (int)$value)
        ;
    }

    public function filterUser(QueryBuilder $qb, $value)
    {
        if(!in_array('pet', $qb->getAllAliases()))
            $qb->innerJoin('l.pet', 'pet');

        if(is_array($value))
            $qb->andWhere('pet.owner IN (:userId)');
        else
            $qb->andWhere('pet.owner=:userId');

        $qb->setParameter('userId', $value);
    }

    public function filterTags(QueryBuilder $qb, $value)
    {
        if(!in_array('tags', $qb->getAllAliases()))
            $qb->innerJoin('l.tags', 'tags');

        if(is_array($value))
            $qb->andWhere('tags.title IN (:tags)');
        else
            $qb->andWhere('tags.title=:tags');

        $qb->setParameter('tags', $value);
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
