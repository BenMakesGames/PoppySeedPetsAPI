<?php
namespace App\Service\Filter;

use App\Entity\ItemFood;
use App\Entity\ItemTool;
use App\Enum\FlavorEnum;
use App\Repository\ItemRepository;
use Doctrine\ORM\QueryBuilder;

class ItemFilterService
{
    use FilterService;

    public const PAGE_SIZE = 20;

    private $repository;

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
                'name' => array($this, 'filterName'),
                'edible' => array($this, 'filterEdible'),
                'foodFlavors' => array($this, 'filterFoodFlavors'),
                'equipable' => array($this, 'filterEquipable'),
                'equipStats' => array($this, 'filterEquipStats')
            ]
        );
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