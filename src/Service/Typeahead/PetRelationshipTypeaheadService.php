<?php
namespace App\Service\Typeahead;

use App\Entity\Pet;
use App\Repository\PetRepository;
use Doctrine\ORM\QueryBuilder;

class PetRelationshipTypeaheadService extends TypeaheadService
{
    /** @var Pet */ private $pet;
    /** @var string[] */ private $relationships;

    public function __construct(PetRepository $petRepository)
    {
        parent::__construct($petRepository);
    }

    public function setParameters(Pet $pet, array $relationships)
    {
        $this->pet = $pet;
        $this->relationships = $relationships;
    }

    public function addQueryBuilderConditions(QueryBuilder $qb): QueryBuilder
    {
        return $qb
            ->join('e.petRelationships', 'r', 'WITH', 'r.relationship=:pet')
            ->andWhere('r.currentRelationship IN (:relationships)')
            ->setParameter('pet', $this->pet->getId())
            ->setParameter('relationships', $this->relationships)
        ;
    }
}
