<?php
namespace App\Service\Filter;

use App\Repository\MeritRepository;
use Doctrine\ORM\QueryBuilder;

class MeritFilterService
{
    use FilterService;

    public const PAGE_SIZE = 10;

    private $repository;

    public function __construct(MeritRepository $meritRepository)
    {
        $this->repository = $meritRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'name' => [ 'm.name' => 'asc' ], // first one is the default
            ],
            [
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('m');
    }
}
