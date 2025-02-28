<?php
declare(strict_types=1);

namespace App\Service\Filter;

use App\Entity\Pet;
use App\Enum\PetBadgeEnum;
use App\Functions\StringFunctions;
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
                'level' => [ 'skills.level' => 'desc', 'p.name' => 'asc' ],
                'name' => [ 'p.name' => 'asc', 'p.id' => 'asc' ],
                'lastInteraction' => [ 'p.lastInteractionDate' => 'desc' ],
            ],
            [
                'name' => $this->filterName(...),
                'species' => $this->filterSpecies(...),
                'owner' => $this->filterOwner(...),
                'location' => $this->filterLocation(...),
                'guild' => $this->filterGuild(...),
                'merit' => $this->filterMerit(...),
                'toolOrHat' => $this->filterToolOrHat(...),
                'isPregnant' => $this->filterIsPregnant(...),
                'badge' => $this->filterBadge(...),
            ],
            [
                'nameExactMatch'
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('p')
            ->join('p.skills', 'skills');
    }

    public function filterName(QueryBuilder $qb, $value, $filters)
    {
        $name = trim($value);

        if(!$name) return;

        if(array_key_exists('nameExactMatch', $filters) && StringFunctions::isTruthy($filters['nameExactMatch']))
        {
            $qb
                ->andWhere('p.name = :nameLike')
                ->setParameter('nameLike', $name)
            ;
        }
        else
        {
            $qb
                ->andWhere('p.name LIKE :nameLike')
                ->setParameter('nameLike', '%' . StringFunctions::escapeMySqlWildcardCharacters($name) . '%')
            ;
        }
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
            ->setParameter('guild', (int)$value)
        ;
    }

    public function filterMerit(QueryBuilder $qb, $value)
    {
        if(!in_array('merits', $qb->getAllAliases()))
            $qb->join('p.merits', 'merits');

        $qb
            ->andWhere('merits.id=:meritId')
            ->setParameter('meritId', (int)$value)
        ;
    }

    public function filterIsPregnant(QueryBuilder $qb, $value)
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('p.pregnancy IS NULL');
        else
            $qb->andWhere('p.pregnancy IS NOT NULL');
    }

    public function filterBadge(QueryBuilder $qb, $value)
    {
        if(!in_array('badges', $qb->getAllAliases()))
            $qb->leftJoin('p.badges', 'badges');

        $qb
            ->andWhere('badges.badge=:badgeName')
            ->setParameter('badgeName', $value)
        ;
    }

    public function filterToolOrHat(QueryBuilder $qb, $value)
    {
        if(!in_array('hat', $qb->getAllAliases()))
            $qb->leftJoin('p.hat', 'hat');

        if(!in_array('tool', $qb->getAllAliases()))
            $qb->leftJoin('p.tool', 'tool');

        $qb
            ->andWhere($qb->expr()->orX('hat.item=:itemId', 'tool.item=:itemId'))
            ->setParameter('itemId', (int)$value)
        ;
    }

    function applyResultCache(Query $qb, string $cacheKey): Query
    {
        return $qb;
    }
}
