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

use App\Entity\ItemTool;
use App\Entity\MarketListing;
use App\Entity\User;
use App\Enum\FlavorEnum;
use App\Exceptions\PSPFormValidationException;
use App\Functions\StringFunctions;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;

class MarketFilterService implements FilterServiceInterface
{
    use FilterService;

    public const int PageSize = 20;

    private ?User $user = null;

    private ObjectRepository $repository;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->repository = $doctrine->getRepository(MarketListing::class, 'readonly');

        $this->filterer = new Filterer(
            self::PageSize,
            [
                'name' => [ 'item.name' => 'asc' ], // first one is the default
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

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('l')
            ->select('l,item')
            ->leftJoin('l.item', 'item')
        ;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function filterName(QueryBuilder $qb, mixed $value, array $filters): void
    {
        $name = mb_trim($value);

        if(!$name) return;

        if(array_key_exists('nameExactMatch', $filters) && StringFunctions::isTruthy($filters['nameExactMatch']))
        {
            $qb
                ->andWhere('item.name = :nameLike')
                ->setParameter('nameLike', $name)
            ;
        }
        else
        {
            $qb
                ->andWhere('item.name LIKE :nameLike')
                ->setParameter('nameLike', '%' . StringFunctions::escapeMySqlWildcardCharacters($name) . '%')
            ;
        }
    }

    public function filterCandy(QueryBuilder $qb, mixed $value): void
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

    public function filterEdible(QueryBuilder $qb, mixed $value): void
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('item.food IS NULL');
        else
            $qb->andWhere('item.food IS NOT NULL');
    }

    public function filterFoodFlavors(QueryBuilder $qb, mixed $value): void
    {
        if(!is_array($value)) $value = [ $value ];

        $value = array_map(strtolower(...), $value);
        $value = array_intersect($value, array_map(fn(FlavorEnum $f) => $f->value, FlavorEnum::cases()));

        if(count($value) === 0) return;

        if(!in_array('food', $qb->getAllAliases()))
            $qb->leftJoin('item.food', 'food');

        $qb
            ->andWhere('item.food IS NOT NULL')
        ;

        foreach($value as $stat)
            $qb->andWhere('food.' . $stat . ' > 0');
    }

    public function filterEquipable(QueryBuilder $qb, mixed $value): void
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('item.tool IS NULL');
        else
            $qb->andWhere('item.tool IS NOT NULL');
    }

    public function filterBonus(QueryBuilder $qb, mixed $value): void
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('item.enchants IS NULL');
        else
            $qb->andWhere('item.enchants IS NOT NULL');
    }

    public function filterSpice(QueryBuilder $qb, mixed $value): void
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('item.spice IS NULL');
        else
            $qb->andWhere('item.spice IS NOT NULL');
    }

    public function filterAHat(QueryBuilder $qb, mixed $value): void
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('item.hat IS NULL');
        else
            $qb->andWhere('item.hat IS NOT NULL');
    }

    public function filterEquipStats(QueryBuilder $qb, mixed $value): void
    {
        if(!is_array($value)) $value = [ $value ];

        $value = array_map('strtolower', $value);
        $value = array_filter(ItemTool::ModifierFields, fn(string $modifier) => in_array(strtolower($modifier), $value));

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

    public function filterHasDonated(QueryBuilder $qb, mixed $value): void
    {
        if(!$this->user)
            return;

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

    public function filterItemGroup(QueryBuilder $qb, mixed $value): void
    {
        if(!in_array('itemGroups', $qb->getAllAliases()))
            $qb->leftJoin('item.itemGroups', 'itemGroup');

        $qb
            ->andWhere('itemGroup.name=:itemGroupName')
            ->setParameter('itemGroupName', $value)
        ;
    }

    public function filterIsFuel(QueryBuilder $qb, mixed $value): void
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('item.fuel = 0');
        else
            $qb->andWhere('item.fuel > 0');
    }

    public function filterIsFertilizer(QueryBuilder $qb, mixed $value): void
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('item.fertilizer = 0');
        else
            $qb->andWhere('item.fertilizer > 0');
    }

    public function filterIsTreasure(QueryBuilder $qb, mixed $value): void
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('item.treasure IS NULL');
        else
            $qb->andWhere('item.treasure IS NOT NULL');
    }

    public function filterIsRecyclable(QueryBuilder $qb, mixed $value): void
    {
        if(strtolower($value) === 'false' || !$value)
            $qb->andWhere('item.recycleValue = 0');
        else
            $qb->andWhere('item.recycleValue > 0');
    }

    function applyResultCache(Query $qb, string $cacheKey): Query
    {
        return $qb;
    }

    public function allowedPageSizes(): array
    {
        return [ self::PageSize ];
    }
}
