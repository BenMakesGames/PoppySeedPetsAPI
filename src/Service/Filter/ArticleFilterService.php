<?php
namespace App\Service\Filter;

use App\Repository\ArticleRepository;
use Doctrine\ORM\QueryBuilder;

class ArticleFilterService
{
    use FilterService;

    public const PAGE_SIZE = 10;

    public function __construct(ArticleRepository $articleRepository)
    {
        $this->filterer = new Filterer(
            $articleRepository, 'a',
            self::PAGE_SIZE,
            [
                'createdOn' => [ 'a.createdOn', 'desc' ], // first one is the default
            ],
            [
            ]
        );
    }
}