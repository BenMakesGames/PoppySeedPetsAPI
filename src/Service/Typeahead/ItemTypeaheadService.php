<?php
declare(strict_types=1);

namespace App\Service\Typeahead;

use App\Entity\Item;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class ItemTypeaheadService extends TypeaheadService
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em->getRepository(Item::class));
    }

    public function addQueryBuilderConditions(QueryBuilder $qb): QueryBuilder
    {
        return $qb;
    }
}
