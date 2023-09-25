<?php
namespace App\Service\Typeahead;

use App\Entity\Pet;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class PetTypeaheadService extends TypeaheadService
{
    private ?User $user = null;
    private ?int $speciesId = null;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em->getRepository(Pet::class));
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function setSpeciesId(int $speciesId)
    {
        $this->speciesId = $speciesId;
    }

    public function addQueryBuilderConditions(QueryBuilder $qb): QueryBuilder
    {
        if($this->user)
            $qb->andWhere('e.owner=:owner')->setParameter('owner', $this->user);

        if($this->speciesId)
            $qb->andWhere('e.species=:species')->setParameter('species', $this->speciesId);

        return $qb;
    }
}
