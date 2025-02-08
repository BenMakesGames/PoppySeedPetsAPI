<?php
declare(strict_types=1);

namespace App\Service\Typeahead;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class UserTypeaheadService extends TypeaheadService
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em->getRepository(User::class));
    }

    public function addQueryBuilderConditions(QueryBuilder $qb): QueryBuilder
    {
        return $qb;
    }
}
