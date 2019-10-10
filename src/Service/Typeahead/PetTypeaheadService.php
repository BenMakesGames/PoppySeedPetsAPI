<?php
namespace App\Service\Typeahead;

use App\Entity\User;
use App\Repository\PetRepository;
use Doctrine\ORM\QueryBuilder;

class PetTypeaheadService extends TypeaheadService
{
    private $user;

    public function __construct(PetRepository $petRepository)
    {
        parent::__construct($petRepository);
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function addQueryBuilderConditions(QueryBuilder $qb): QueryBuilder
    {
        return $qb
            ->andWhere('e.owner=:owner')
            ->setParameter('owner', $this->user)
        ;
    }
}
