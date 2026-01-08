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

namespace App\Model;

use App\Entity\Item;
use App\Entity\ItemFood;
use App\Entity\ItemGroup;
use App\Entity\Spice;
use App\Enum\FlavorEnum;
use App\Enum\StatusEffectEnum;

class FoodWithSpice
{
    public Item $baseItem;
    public string $name;
    public int $food;
    public int $love;
    public int $junk;
    public int $alcohol;
    public int $caffeine;
    public int $psychedelic;
    public int $randomFlavor;
    public int $containsTentacles;
    /** @var BonusItemChance[] */ public array $bonusItems = [];
    public array $grantedSkills = [];
    /** @var array{effect: StatusEffectEnum, duration: int}[] */ public array $grantedStatusEffects = [];
    public bool $grantsSelfReflection = false;
    public int $earthy;
    public int $fruity;
    public int $tannic;
    public int $spicy;
    public int $creamy;
    public int $meaty;
    public int $planty;
    public int $fishy;
    public int $floral;
    public int $fatty;
    public int $oniony;
    public int $chemically;
    /** @var Item[] */ public array $leftovers = [];

    public function __construct(Item $item, ?Spice $spice)
    {
        $food = $item->getFood();

        $this->baseItem = $item;
        $this->name = $item->getName();
        $this->food = $food->getFood();
        $this->love = $food->getLove();
        $this->junk = $food->getJunk();
        $this->alcohol = $food->getAlcohol();
        $this->caffeine = $food->getCaffeine();
        $this->psychedelic = $food->getPsychedelic();
        $this->randomFlavor = $food->getRandomFlavor();
        $this->containsTentacles = $food->getContainsTentacles() ? 2 : 0;

        if($food->getChanceForBonusItem())
            $this->bonusItems[] = new BonusItemChance($food);

        foreach(FlavorEnum::cases() as $flavor)
            $this->{$flavor->value} = $food->{'get' . $flavor->value}();

        if($food->getGrantedSkill())
            $this->grantedSkills[] = $food->getGrantedSkill();

        if($food->getGrantedStatusEffect() && $food->getGrantedStatusEffectDuration() > 0)
        {
            $this->grantedStatusEffects[] = [
                'effect' => $food->getGrantedStatusEffect(),
                'duration' => $food->getGrantedStatusEffectDuration()
            ];
        }

        if($food->getGrantsSelfReflection())
            $this->grantsSelfReflection = true;

        if($food->getLeftovers())
            $this->leftovers[] = $food->getLeftovers();

        if($spice)
        {
            $effects = $spice->getEffects();

            if($spice->getIsSuffix())
                $this->name .= ' ' . $spice->getName();
            else
                $this->name = $spice->getName() . ' ' . $this->name;

            $this->food += $effects->getFood();
            $this->love += $effects->getLove();
            $this->junk += $effects->getJunk();
            $this->alcohol += $effects->getAlcohol();
            $this->caffeine += $effects->getCaffeine();
            $this->psychedelic += $effects->getPsychedelic();
            $this->randomFlavor += $effects->getRandomFlavor();
            $this->containsTentacles += $effects->getContainsTentacles() ? 2 : 0;

            if($effects->getChanceForBonusItem())
                $this->bonusItems[] = new BonusItemChance($effects);

            if($effects->getGrantedSkill())
                $this->grantedSkills[] = $effects->getGrantedSkill();

            if($effects->getGrantedStatusEffect() && $effects->getGrantedStatusEffectDuration() > 0)
            {
                $this->grantedStatusEffects[] = [
                    'effect' => $effects->getGrantedStatusEffect(),
                    'duration' => $effects->getGrantedStatusEffectDuration()
                ];
            }

            if($effects->getGrantsSelfReflection())
                $this->grantsSelfReflection = true;

            foreach(FlavorEnum::cases() as $flavor)
                $this->{$flavor->value} += $effects->{'get' . $flavor->value}();

            if($effects->getLeftovers())
                $this->leftovers[] = $effects->getLeftovers();
        }
    }
}

class BonusItemChance
{
    public int $chance;
    public ?ItemGroup $itemGroup;

    public function __construct(ItemFood $food)
    {
        $this->chance = (int)$food->getChanceForBonusItem();
        $this->itemGroup = $food->getBonusItemGroup();
    }
}