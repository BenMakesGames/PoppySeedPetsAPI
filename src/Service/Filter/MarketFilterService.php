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

    public function __construct(ItemRepository $itemRepository)
    {
        $this->repository = $itemRepository;

        $this->filterer = new Filterer(
            self::PAGE_SIZE,
            [
                'name' => [ 'i.name' => 'asc' ], // first one is the default
            ],
            [
                'name' => [ $this, 'filterName' ],
                'edible' => [ $this, 'filterEdible' ],
                'foodFlavors' => [ $this, 'filterFoodFlavors' ],
                'equipable' => [ $this, 'filterEquipable' ],
                'equipStats' => [ $this, 'filterEquipStats' ],
                'aHat' => [ $this, 'filterAHat' ],
            ]
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('i')
            ->select('i AS item,MIN(inventory.sellPrice) AS minSellPrice')
            ->leftJoin('i.inventory', 'inventory')
            ->andWhere('inventory.sellPrice IS NOT NULL')
            ->andWhere('inventory.owner != :user')
            ->addGroupBy('i.name')
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

        $qb
            ->andWhere('i.name LIKE :nameLike')
            ->setParameter('nameLike', '%' . $name . '%')
        ;
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

        foreach($value as $stat)
        {
            $qb->andWhere('food.' . $stat . ' > 0');
        }
    }

    public function filterEquipable(QueryBuilder $qb, $value)
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('i.tool IS NULL');
        else
            $qb->andWhere('i.tool IS NOT NULL');
    }

    public function filterAHat(QueryBuilder $qb, $value)
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('i.hat IS NULL');
        else
            $qb->andWhere('i.hat IS NOT NULL');
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
}