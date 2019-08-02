<?php
namespace App\Service\Filter;

use App\Repository\ArticleRepository;
use Doctrine\ORM\QueryBuilder;

class ArticleFilterService
{
    use FilterService;

    public const PAGE_SIZE = 10;

    private $repository;

    public function __construct(ArticleRepository $articleRepository)
    {
        $this->repository = $articleRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'createdOn' => [ 'a.createdOn' => 'desc' ], // first one is the default
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