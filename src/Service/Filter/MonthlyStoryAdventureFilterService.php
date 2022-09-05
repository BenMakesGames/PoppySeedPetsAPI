<?php
namespace App\Service\Filter;

use App\Repository\MonthlyStoryAdventureRepository;
use Doctrine\ORM\QueryBuilder;

class MonthlyStoryAdventureFilterService
{
    use FilterService;

    public const PAGE_SIZE = 12;

    private MonthlyStoryAdventureRepository $repository;

    public function __construct(MonthlyStoryAdventureRepository $monthlyStoryAdventureRepository)
    {
        $this->repository = $monthlyStoryAdventureRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'releaseNumber' => [ 'a.releaseNumber' => 'desc' ], // first one is the default
            ],
            [
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('a');
    }
}
