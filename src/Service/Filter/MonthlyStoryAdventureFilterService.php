<?php
declare(strict_types=1);

namespace App\Service\Filter;

use App\Entity\MonthlyStoryAdventure;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class MonthlyStoryAdventureFilterService
{
    use FilterService;

    public const PageSize = 12;

    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repository = $em->getRepository(MonthlyStoryAdventure::class);

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
        return $this->repository->createQueryBuilder('a');
    }

    function applyResultCache(Query $qb, string $cacheKey): Query
    {
        return $qb;
    }
}
