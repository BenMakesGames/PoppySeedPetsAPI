<?php
namespace App\Service\Filter;

use App\Entity\User;
use App\Repository\ItemRepository;
use App\Repository\PetActivityLogRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PetActivityLogsFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    private $repository;

    public function __construct(PetActivityLogRepository $petActivityLogRepository)
    {
        $this->repository = $petActivityLogRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'id' => [ 'l.id' => 'desc' ], // first one is the default
            ],
            [
                'pet' => [ $this, 'filterPet' ],
                'date' => [ $this, 'filterDate' ],
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
            throw new UnprocessableEntityHttpException('"date" must be in yyyy-mm-dd format.');

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
            ->setParameter('pet', $value)
        ;
    }
}
