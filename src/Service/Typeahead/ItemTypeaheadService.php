<?php
namespace App\Service\Typeahead;

use App\Repository\ItemRepository;
use Doctrine\ORM\QueryBuilder;

class ItemTypeaheadService extends TypeaheadService
{
    public function __construct(ItemRepository $itemRepository)
    {
        parent::__construct($itemRepository);
    }

    public function addQueryBuilderConditions(QueryBuilder $qb): QueryBuilder
    {
        return $qb;
    }
}
