<?php
namespace App\Service\Filter;

use App\Entity\ItemTool;
use App\Entity\User;
use App\Enum\FlavorEnum;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use Doctrine\ORM\QueryBuilder;

class MarketFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    private User $user;
    private InventoryRepository $repository;

    public function __construct(InventoryRepository $inventoryRepository)
    {
        $this->repository = $inventoryRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'name' => [ 'item.name' => 'asc' ], // first one is the default
            ],
            [
                'name' => [ $this, 'filterName' ],
                'edible' => [ $this, 'filterEdible' ],
                'candy' => [ $this, 'filterCandy' ],
                'spice' => [ $this, 'filterSpice' ],
                'foodFlavors' => [ $this, 'filterFoodFlavors' ],
                'equipable' => [ $this, 'filterEquipable' ],
                'equipStats' => [ $this, 'filterEquipStats' ],
                'bonus' => [ $this, 'filterBonus' ],
                'aHat' => [ $this, 'filterAHat' ],
                'hasDonated' => [ $this, 'filterHasDonated' ],
                'itemGroup' => [ $this, 'filterItemGroup' ],
                'isFuel' => [ $this, 'filterIsFuel' ],
                'isFertilizer' => [ $this, 'filterIsFertilizer' ],
                'isTreasure' => [ $this, 'filterIsTreasure' ],
            ],
            [
                'nameExactMatch'
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('i')
            ->select('i AS inventory,item,enchantment')
            ->andWhere('i.sellPrice IS NOT NULL')
            ->leftJoin('i.item', 'item')
            ->leftJoin('i.enchantment', 'enchantment')
            ->leftJoin('i.spice', 'spice')
            ->addGroupBy('item.name')
            ->addGroupBy('enchantment.name')
            ->addGroupBy('spice.name')
        ;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function filterName(QueryBuilder $qb, $value, $filters)
    {
        $name = trim($value);

        if(!$name) return;

        if(array_key_exists('nameExactMatch', $filters) && (bool)$filters['nameExactMatch'])
        {
            $qb
                ->andWhere('i.fullItemName = :nameLike')
                ->setParameter('nameLike', $name)
            ;
        }
        else
        {
            $qb
                ->andWhere('i.fullItemName LIKE :nameLike')
                ->setParameter('nameLike', '%' . $name . '%')
            ;
        }
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

    public function filterEdible(QueryBuilder $qb, $value)
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('item.food IS NULL');
        else
            $qb->andWhere('item.food IS NOT NULL');
    }

    public function filterFoodFlavors(QueryBuilder $qb, $value)
    {
        if(!is_array($value)) $value = [ $value ];

        $value = array_map('strtolower', $value);
        $value = array_intersect($value, FlavorEnum::getValues());

        if(count($value) === 0) return;

        if(!in_array('food', $qb->getAllAliases()))
            $qb->leftJoin('item.food', 'food');

        if(!in_array('spice', $qb->getAllAliases()))
            $qb->leftJoin('i.spice', 'spice');

        if(!in_array('spiceFood', $qb->getAllAliases()))
            $qb->leftJoin('spice.effects', 'spiceFood');

        $qb
            ->andWhere('item.food IS NOT NULL')
        ;

        foreach($value as $stat)
        {
            $qb->andWhere($qb->expr()->orX('food.' . $stat . ' > 0', 'spiceFood.' . $stat . ' > 0'));
        }
    }

    public function filterEquipable(QueryBuilder $qb, $value)
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('item.tool IS NULL');
        else
            $qb->andWhere('item.tool IS NOT NULL');
    }

    public function filterBonus(QueryBuilder $qb, $value)
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('item.enchants IS NULL');
        else
            $qb->andWhere('item.enchants IS NOT NULL');
    }

    public function filterSpice(QueryBuilder $qb, $value)
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('item.spice IS NULL');
        else
            $qb->andWhere('item.spice IS NOT NULL');
    }

    public function filterAHat(QueryBuilder $qb, $value)
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('item.hat IS NULL');
        else
            $qb->andWhere('item.hat IS NOT NULL');
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

        $qb
            ->setParameter('user', $this->user)
        ;

        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('donations.id IS NULL');
        else
            $qb->andWhere('donations.id IS NOT NULL');
    }

    public function filterItemGroup(QueryBuilder $qb, $value)
    {
        if(!in_array('itemGroups', $qb->getAllAliases()))
            $qb->leftJoin('item.itemGroups', 'itemGroup');

        $qb
            ->andWhere('itemGroup.name=:itemGroupName')
            ->setParameter('itemGroupName', $value)
        ;
    }

    public function filterIsFuel(QueryBuilder $qb, $value)
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('item.fuel = 0');
        else
            $qb->andWhere('item.fuel > 0');
    }

    public function filterIsFertilizer(QueryBuilder $qb, $value)
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('item.fertilizer = 0');
        else
            $qb->andWhere('item.fertilizer > 0');
    }

    public function filterIsTreasure(QueryBuilder $qb, $value)
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('item.treasure IS NULL');
        else
            $qb->andWhere('item.treasure IS NOT NULL');
    }
}
