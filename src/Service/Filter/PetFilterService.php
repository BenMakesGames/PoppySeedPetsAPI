<?php
namespace App\Service\Filter;

use App\Entity\Pet;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;

class PetFilterService
{
    use FilterService;

    public const PAGE_SIZE = 12;

    private ObjectRepository $repository;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->repository = $doctrine->getRepository(Pet::class, 'readonly');

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
                'isPregnant' => [ $this, 'filterIsPregnant' ],
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
        $qb
            ->andWhere('p.species=:speciesId')
            ->setParameter('speciesId', $value)
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
        if(is_array($value))
        {
            $qb
                ->andWhere('p.location IN (:locations)')
                ->setParameter('locations', $value)
            ;
        }
        else
        {
            $qb
                ->andWhere('p.location = :location')
                ->setParameter('location', $value)
            ;
        }
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

    public function filterIsPregnant(QueryBuilder $qb, $value)
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('p.pregnancy IS NULL');
        else
            $qb->andWhere('p.pregnancy IS NOT NULL');
    }

    function applyResultCache(Query $qb, string $cacheKey): Query
    {
        return $qb;
    }
}
