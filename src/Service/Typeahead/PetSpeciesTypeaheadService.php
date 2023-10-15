<?php
namespace App\Service\Typeahead;

use App\Entity\PetSpecies;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class PetSpeciesTypeaheadService extends TypeaheadService
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em->getRepository(PetSpecies::class));
    }

    public function addQueryBuilderConditions(QueryBuilder $qb): QueryBuilder
    {
        return $qb;
    }
}
