<?php
namespace App\Service\Filter;

use App\Entity\ItemTool;
use App\Entity\User;
use App\Enum\FlavorEnum;
use App\Repository\InventoryRepository;
use Doctrine\ORM\QueryBuilder;

class InventoryFilterService
{
    use FilterService;

    public const PAGE_SIZE = 100;

    private $repository;
    private $user;

    public function __construct(InventoryRepository $inventoryRepository)
    {
        $this->repository = $inventoryRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'itemname' => [ 'item.name' => 'asc' ], // first one is the default
            ],
            [
                'location' => [ $this, 'filterLocation' ],
                'user' => [ $this, 'filterUser' ],

                'name' => [ $this, 'filterName' ],
                'edible' => [ $this, 'filterEdible' ],
                'candy' => [ $this, 'filterCandy' ],
                'foodFlavors' => [ $this, 'filterFoodFlavors' ],
                'equipable' => [ $this, 'filterEquipable' ],
                'equipStats' => [ $this, 'filterEquipStats' ],
                'bonus' => [ $this, 'filterBonus' ],
                'aHat' => [ $this, 'filterAHat' ],
                'hasDonated' => [ $this, 'filterHasDonated' ],
            ]
        );
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('i')
            ->leftJoin('i.item', 'item')
        ;
    }

    public function filterUser(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('i.owner = :userId')
            ->setParameter('userId', $value)
        ;
    }

    public function filterLocation(QueryBuilder $qb, $value)
    {
        $qb
            ->andWhere('i.location = :location')
            ->setParameter('location', $value)
        ;
    }

    public function filterName(QueryBuilder $qb, $value)
    {
        $name = trim($value);

        if(!$name) return;

        $qb
            ->andWhere('item.name LIKE :nameLike')
            ->setParameter('nameLike', '%' . $name . '%')
        ;
    }

    public function filterEdible(QueryBuilder $qb, $value)
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('item.food IS NULL');
        else
            $qb->andWhere('item.food IS NOT NULL');
    }

    public function filterCandy(QueryBuilder $qb, $value)
    {
        if((int)(new \DateTimeImmutable())->format('n') !== 10)
            return;

        if(!in_array('food', $qb->getAllAliases()))
            $qb->leftJoin('item.food', 'food');

        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('food.isCandy=0');
        else
            $qb->andWhere('food.isCandy=1');
    }

    public function filterFoodFlavors(QueryBuilder $qb, $value)
    {
        if(!is_array($value)) $value = [ $value ];

        $value = array_map('strtolower', $value);
        $value = array_intersect($value, FlavorEnum::getValues());

        if(count($value) === 0) return;

        if(!in_array('food', $qb->getAllAliases()))
            $qb->leftJoin('item.food', 'food');

        $qb
            ->andWhere('item.food IS NOT NULL')
        ;

        foreach($value as $stat)
        {
            $qb->andWhere('food.' . $stat . ' > 0');
        }
    }

    public function filterAHat(QueryBuilder $qb, $value)
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('item.hat IS NULL');
        else
            $qb->andWhere('item.hat IS NOT NULL');
    }

    public function filterBonus(QueryBuilder $qb, $value)
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('item.enchants IS NULL');
        else
            $qb->andWhere('item.enchants IS NOT NULL');
    }

    public function filterEquipable(QueryBuilder $qb, $value)
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('item.tool IS NULL');
        else
            $qb->andWhere('item.tool IS NOT NULL');
    }

    public function filterEquipStats(QueryBuilder $qb, $value)
    {
        if(!is_array($value)) $value = [ $value ];

        $value = array_map('strtolower', $value);
        $value = array_intersect($value, ItemTool::MODIFIER_FIELDS);

        if(count($value) === 0) return;

        if(!in_array('tool', $qb->getAllAliases()))
            $qb->leftJoin('item.tool', 'tool');

        $qb
            ->andWhere('item.tool IS NOT NULL')
        ;

        foreach($value as $stat)
        {
            $qb->andWhere('tool.' . $stat . ' > 0');
        }
    }

    public function filterHasDonated(QueryBuilder $qb, $value)
    {
        if(!in_array('donations', $qb->getAllAliases()))
            $qb->leftJoin('item.museumDonations', 'donations', 'WITH', 'donations.user=:user');

        $qb->setParameter('user', $this->user);

        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('donations.id IS NULL');
        else
            $qb->andWhere('donations.id IS NOT NULL');
    }
}
