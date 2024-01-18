<?php
namespace App\Service\Filter;

use App\Entity\PetActivityLog;
use App\Exceptions\PSPFormValidationException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;

class PetActivityLogsFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    private readonly ObjectRepository $repository;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->repository = $doctrine->getRepository(PetActivityLog::class, 'readonly');

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'id' => [ 'l.id' => 'desc' ], // first one is the default
                'interestingness' => [ 'l.interestingness' => 'desc' ],
            ],
            [
                'pet' => [ $this, 'filterPet' ],
                'date' => [ $this, 'filterDate' ],
                'user' => [ $this, 'filterUser' ],
                'tags' => [ $this, 'filterTags' ],
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('l');
    }

    public function filterDate(QueryBuilder $qb, $value)
    {
        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $value);

        if($date === false)
            throw new PSPFormValidationException('"date" must be in yyyy-mm-dd format.');

        $date = $date->setTime(0, 0, 0);

        $this->filterer->setPageSize(200);

        $qb
            ->andWhere('l.createdOn >= :date')
            ->andWhere('l.createdOn < :datePlus1')
            ->setParameter('date', $date->format('Y-m-d 00:00:00'))
            ->setParameter('datePlus1', $date->modify('+1 day')->format('Y-m-d 00:00:00'))
        ;
    }

    public function filterPet(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('l.pet = :pet')
            ->setParameter('pet', (int)$value)
        ;
    }

    public function filterUser(QueryBuilder $qb, $value)
    {
        if(!in_array('pet', $qb->getAllAliases()))
            $qb->innerJoin('l.pet', 'pet');

        if(is_array($value))
            $qb->andWhere('pet.owner IN (:userId)');
        else
            $qb->andWhere('pet.owner=:userId');

        $qb->setParameter('userId', $value);
    }

    public function filterTags(QueryBuilder $qb, $value)
    {
        if(!in_array('tags', $qb->getAllAliases()))
            $qb->innerJoin('l.tags', 'tags');

        if(is_array($value))
            $qb->andWhere('tags.title IN (:tags)');
        else
            $qb->andWhere('tags.title=:tags');

        $qb->setParameter('tags', $value);
    }

    function applyResultCache(Query $qb, string $cacheKey): Query
    {
        return $qb;
    }
}
