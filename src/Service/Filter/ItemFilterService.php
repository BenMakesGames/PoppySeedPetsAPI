<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Service\Filter;

use App\Entity\Item;
use App\Entity\ItemTool;
use App\Entity\User;
use App\Enum\FlavorEnum;
use App\Functions\CacheHelpers;
use App\Functions\StringFunctions;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;

class ItemFilterService
{
    use FilterService;

    public const int PageSize = 20;

    private readonly ObjectRepository $repository;
    private ?User $user;
    private bool $useResultCache;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->repository = $doctrine->getRepository(Item::class, 'readonly');
        $this->useResultCache = true;

        $this->filterer = new Filterer(
            self::PageSize,
            [
                'name' => [ 'i.name' => 'asc' ], // first one is the default
                'id' => [ 'i.id' => 'asc' ],
            ],
            [
                'name' => $this->filterName(...),
                'edible' => $this->filterEdible(...),
                'candy' => $this->filterCandy(...),
                'spice' => $this->filterSpice(...),
                'foodFlavors' => $this->filterFoodFlavors(...),
                'equipable' => $this->filterEquipable(...),
                'equipStats' => $this->filterEquipStats(...),
                'bonus' => $this->filterBonus(...),
                'notDonatedBy' => $this->filterNotDonatedBy(...),
                'aHat' => $this->filterAHat(...),
                'hasDonated' => $this->filterHasDonated(...),
                'itemGroup' => $this->filterItemGroup(...),
                'isFuel' => $this->filterIsFuel(...),
                'isFertilizer' => $this->filterIsFertilizer(...),
                'isTreasure' => $this->filterIsTreasure(...),
                'isRecyclable' => $this->filterIsRecyclable(...),
            ],
            [
                'nameExactMatch'
            ]
        );
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('i');
    }

    public function filterName(QueryBuilder $qb, $value, $filters): void
    {
        $name = mb_trim($value);

        if(!$name) return;

        if(array_key_exists('nameExactMatch', $filters) && StringFunctions::isTruthy($filters['nameExactMatch']))
        {
            $qb
                ->andWhere('i.name = :nameLike')
                ->setParameter('nameLike', $name)
            ;
        }
        else
        {
            $qb
                ->andWhere('i.name LIKE :nameLike')
                ->setParameter('nameLike', '%' . StringFunctions::escapeMySqlWildcardCharacters($name) . '%')
            ;
        }
    }

    public function filterNotDonatedBy(QueryBuilder $qb, mixed $value): void
    {
        $qb
            ->leftJoin('i.museumDonations', 'm', Join::WITH, 'm.user=:user')
            ->andWhere('m.id IS NULL')
            ->setParameter('user', (int)$value)
        ;

        $this->useResultCache = false;
    }

    public function filterCandy(QueryBuilder $qb, mixed $value): void
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

    public function filterEdible(QueryBuilder $qb, mixed $value): void
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('i.food IS NULL');
        else
            $qb->andWhere('i.food IS NOT NULL');
    }

    public function filterFoodFlavors(QueryBuilder $qb, mixed $value): void
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

    public function filterBonus(QueryBuilder $qb, mixed $value): void
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('i.enchants IS NULL');
        else
            $qb->andWhere('i.enchants IS NOT NULL');
    }

    public function filterSpice(QueryBuilder $qb, mixed $value): void
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('i.spice IS NULL');
        else
            $qb->andWhere('i.spice IS NOT NULL');
    }

    public function filterAHat(QueryBuilder $qb, mixed $value): void
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('i.hat IS NULL');
        else
            $qb->andWhere('i.hat IS NOT NULL');
    }

    public function filterEquipable(QueryBuilder $qb, mixed $value): void
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('i.tool IS NULL');
        else
            $qb->andWhere('i.tool IS NOT NULL');
    }

    public function filterEquipStats(QueryBuilder $qb, mixed $value): void
    {
        if(!is_array($value)) $value = [ $value ];

        $value = array_map('strtolower', $value);
        $value = array_intersect($value, ItemTool::ModifierFields);

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

    public function filterHasDonated(QueryBuilder $qb, mixed $value): void
    {
        if(!$this->user)
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

        $this->useResultCache = false;
    }

    public function filterItemGroup(QueryBuilder $qb, mixed $value): void
    {
        if(!in_array('itemGroups', $qb->getAllAliases()))
            $qb->leftJoin('i.itemGroups', 'itemGroup');

        $qb
            ->andWhere('itemGroup.name=:itemGroupName')
            ->setParameter('itemGroupName', $value)
        ;
    }

    public function filterIsFuel(QueryBuilder $qb, mixed $value): void
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('i.fuel = 0');
        else
            $qb->andWhere('i.fuel > 0');
    }

    public function filterIsFertilizer(QueryBuilder $qb, mixed $value): void
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('i.fertilizer = 0');
        else
            $qb->andWhere('i.fertilizer > 0');
    }

    public function filterIsTreasure(QueryBuilder $qb, mixed $value): void
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('i.treasure IS NULL');
        else
            $qb->andWhere('i.treasure IS NOT NULL');
    }

    public function filterIsRecyclable(QueryBuilder $qb, mixed $value): void
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('i.recycleValue = 0');
        else
            $qb->andWhere('i.recycleValue > 0');
    }

    function applyResultCache(Query $qb, string $cacheKey): AbstractQuery
    {
        if($this->useResultCache)
            return $qb->enableResultCache(24 * 60 * 60, CacheHelpers::getCacheItemName(self::class . '_' . $cacheKey));
        else
            return $qb;
    }

    public function allowedPageSizes(): array
    {
        return [ self::PageSize ];
    }
}
