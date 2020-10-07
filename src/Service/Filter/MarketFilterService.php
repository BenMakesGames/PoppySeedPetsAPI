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

    /**
     * @var User
     */
    private $user;

    private $repository;

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
                'foodFlavors' => [ $this, 'filterFoodFlavors' ],
                'equipable' => [ $this, 'filterEquipable' ],
                'equipStats' => [ $this, 'filterEquipStats' ],
                'bonus' => [ $this, 'filterBonus' ],
                'aHat' => [ $this, 'filterAHat' ],
                'hasDonated' => [ $this, 'filterHasDonated' ],
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('i')
            ->select('i AS inventory,item,enchantment,MIN(i.sellPrice) AS minSellPrice')
            ->leftJoin('i.item', 'item')
            ->leftJoin('i.enchantment', 'enchantment')
            ->andWhere('i.sellPrice IS NOT NULL')
            ->andWhere('i.owner != :user')
            ->addGroupBy('item.name')
            ->addGroupBy('enchantment.name')
            ->setParameter('user', $this->user->getId())
        ;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function filterName(QueryBuilder $qb, $value)
    {
        $name = trim($value);

        if(!$name) return;

        if(!in_array('enchantment', $qb->getAllAliases()))
            $qb->leftJoin('item.enchantment', 'enchantment');

        $qb
            ->andWhere($qb->expr()->orX(
                'item.name LIKE :nameLike',
                'enchantment.name LIKE :nameLike'
            ))
            ->setParameter('nameLike', '%' . $name . '%')
        ;
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

        $qb
            ->andWhere('item.food IS NOT NULL')
        ;

        foreach($value as $stat)
        {
            $qb->andWhere('food.' . $stat . ' > 0');
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
}
