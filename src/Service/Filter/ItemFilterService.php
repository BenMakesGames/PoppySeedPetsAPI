<?php
namespace App\Service\Filter;

use App\Entity\ItemFood;
use App\Entity\ItemTool;
use App\Entity\User;
use App\Enum\FlavorEnum;
use App\Repository\ItemRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

class ItemFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    private $repository;
    private $user;

    public function __construct(ItemRepository $itemRepository)
    {
        $this->repository = $itemRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'name' => [ 'i.name' => 'asc' ], // first one is the default
                'id' => [ 'i.id' => 'asc' ],
            ],
            [
                'name' => [ $this, 'filterName' ],
                'edible' => [ $this, 'filterEdible' ],
                'candy' => [ $this, 'filterCandy' ],
                'foodFlavors' => [ $this, 'filterFoodFlavors' ],
                'equipable' => [ $this, 'filterEquipable' ],
                'equipStats' => [ $this, 'filterEquipStats' ],
                'bonus' => [ $this, 'filterBonus' ],
                'notDonatedBy' => [ $this, 'filterNotDonatedBy' ],
                'aHat' => [ $this, 'filterAHat' ],
                'hasDonated' => [ $this, 'filterHasDonated' ],
            ]
        );
    }

    public function setUser(?User $user)
    {
        $this->user = $user;
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('i');
    }

    public function filterName(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('i.name LIKE :nameLike')
            ->setParameter('nameLike', '%' . $value . '%')
        ;
    }

    public function filterNotDonatedBy(QueryBuilder $qb, $value)
    {
        $qb
            ->leftJoin('i.museumDonations', 'm', Join::WITH, 'm.user=:user')
            ->andWhere('m.id IS NULL')
            ->setParameter('user', $value)
        ;
    }

    public function filterCandy(QueryBuilder $qb, $value)
    {
        if((int)(new \DateTimeImmutable())->format('n') !== 10)
            return;

        if(!in_array('food', $qb->getAllAliases()))
            $qb->leftJoin('i.food', 'food');

        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('food.isCandy=0');
        else
            $qb->andWhere('food.isCandy=1');
    }

    public function filterEdible(QueryBuilder $qb, $value)
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('i.food IS NULL');
        else
            $qb->andWhere('i.food IS NOT NULL');
    }

    public function filterFoodFlavors(QueryBuilder $qb, $value)
    {
        if(!is_array($value)) $value = [ $value ];

        $value = array_map('strtolower', $value);
        $value = array_intersect($value, FlavorEnum::getValues());

        if(count($value) === 0) return;

        if(!in_array('food', $qb->getAllAliases()))
            $qb->leftJoin('i.food', 'food');

        $qb
            ->andWhere('i.food IS NOT NULL')
        ;

        $statsMatch = array_map(function($s) {
            return 'food.' . $s . ' > 0';
        }, $value);

        $qb->andWhere(
            $qb->expr()->orX(
                'food.randomFlavor > 0',
                implode(' AND ', $statsMatch)
            )
        );
    }

    public function filterBonus(QueryBuilder $qb, $value)
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('i.enchants IS NULL');
        else
            $qb->andWhere('i.enchants IS NOT NULL');
    }

    public function filterAHat(QueryBuilder $qb, $value)
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('i.hat IS NULL');
        else
            $qb->andWhere('i.hat IS NOT NULL');
    }

    public function filterEquipable(QueryBuilder $qb, $value)
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('i.tool IS NULL');
        else
            $qb->andWhere('i.tool IS NOT NULL');
    }

    public function filterEquipStats(QueryBuilder $qb, $value)
    {
        if(!is_array($value)) $value = [ $value ];

        $value = array_map('strtolower', $value);
        $value = array_intersect($value, ItemTool::MODIFIER_FIELDS);

        if(count($value) === 0) return;

        if(!in_array('tool', $qb->getAllAliases()))
            $qb->leftJoin('i.tool', 'tool');

        $qb
            ->andWhere('i.tool IS NOT NULL')
        ;

        foreach($value as $stat)
        {
            $qb->andWhere('tool.' . $stat . ' > 0');
        }
    }

    public function filterHasDonated(QueryBuilder $qb, $value)
    {
        if($this->user === null)
            return;

        if(!in_array('donations', $qb->getAllAliases()))
            $qb->leftJoin('i.museumDonations', 'donations', 'WITH', 'donations.user=:user');

        $qb
            ->setParameter('user', $this->user)
        ;

        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('donations.id IS NULL');
        else
            $qb->andWhere('donations.id IS NOT NULL');
    }
}
