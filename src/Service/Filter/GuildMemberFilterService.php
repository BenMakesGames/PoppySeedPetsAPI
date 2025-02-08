<?php
declare(strict_types=1);

namespace App\Service\Filter;

use App\Repository\PetRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class GuildMemberFilterService
{
    use FilterService;

    public const PAGE_SIZE = 12;

    private readonly EntityRepository $repository;

    public function __construct(PetRepository $petRepository)
    {
        $this->repository = $petRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'rank' => [ 'guildMembership.level' => 'desc', 'guildMembership.joinedOn' => 'asc' ],
                'id' => [ 'p.id' => 'asc' ],
            ],
            [
                'guild' => $this->filterGuild(...),
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('p')
            ->join('p.guildMembership', 'guildMembership')
        ;
    }

    public function filterGuild(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('guildMembership.guild=:guild')
            ->setParameter('guild', (int)$value)
        ;
    }

    function applyResultCache(Query $qb, string $cacheKey): Query
    {
        return $qb;
    }
}
