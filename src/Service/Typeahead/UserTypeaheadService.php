<?php
namespace App\Service\Typeahead;

use App\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;

class UserTypeaheadService extends TypeaheadService
{
    public function __construct(UserRepository $userRepository)
    {
        parent::__construct($userRepository);
    }

    public function addQueryBuilderConditions(QueryBuilder $qb): QueryBuilder
    {
        return $qb;
    }
}
