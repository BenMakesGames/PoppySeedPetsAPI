<?php
namespace App\Service\Filter;

use App\Repository\PetRepository;
use Doctrine\ORM\QueryBuilder;

class PetFilterService
{
    use FilterService;

    public const PAGE_SIZE = 12;

    private $repository;

    public function __construct(PetRepository $petRepository)
    {
        $this->repository = $petRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'id' => [ 'p.id' => 'asc' ],
            ],
            [
                'name' => [ $this, 'filterName' ],
                'species' => [ $this, 'filterSpecies' ],
                'owner' => [ $this, 'filterOwner' ],
                'location' => [ $this, 'filterLocation' ],
                'guild' => [ $this, 'filterGuild' ],
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('p');
    }

    public function filterName(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('p.name LIKE :nameLike')
            ->setParameter('nameLike', '%' . $value . '%')
        ;
    }

    public function filterSpecies(QueryBuilder $qb, $value)
    {
        if(!in_array('species', $qb->getAllAliases()))
            $qb->join('p.species', 'species');

        $qb
            ->andWhere($qb->expr()->orX(
                'species.name LIKE :speciesLike',
                'species.family LIKE :speciesLike'
            ))
            ->setParameter('speciesLike', '%' . $value . '%')
        ;
    }

    public function filterOwner(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('p.owner = :userId')
            ->setParameter('userId', $value)
        ;
    }

    public function filterLocation(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('p.location = :location')
            ->setParameter('location', $value)
        ;
    }

    public function filterGuild(QueryBuilder $qb, $value)
    {
        if(!in_array('guildMembership', $qb->getAllAliases()))
            $qb->join('p.guildMembership', 'guildMembership');

        $qb
            ->andWhere('guildMembership.guild=:guild')
            ->setParameter('guild', $value)
        ;
    }
}
