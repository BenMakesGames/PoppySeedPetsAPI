<?php
namespace App\Model;

use App\Entity\Item;
use App\Entity\ItemFood;
use App\Entity\ItemGroup;
use App\Entity\Spice;
use App\Enum\FlavorEnum;

class FoodWithSpice
{
    public $name;
    public $food;
    public $love;
    public $junk;
    public $alcohol;
    public $caffeine;
    public $psychedelic;
    public $randomFlavor;
    public $containsTentacles;
    /** @var BonusItemChance[] */ public array $bonusItems = [];
    public $grantedSkills = [];
    public $grantedStatusEffects = [];
    public $earthy;
    public $fruity;
    public $tannic;
    public $spicy;
    public $creamy;
    public $meaty;
    public $planty;
    public $fishy;
    public $floral;
    public $fatty;
    public $oniony;
    public $chemically;
    /** @var Item[] */ public $leftovers = [];

    public function __construct(Item $item, ?Spice $spice)
    {
        $food = $item->getFood();

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

        foreach(FlavorEnum::getValues() as $flavor)
            $this->{$flavor} = $food->{'get' . $flavor}();

        if($food->getGrantedSkill())
            $this->grantedSkills[] = $food->getGrantedSkill();

        if($food->getGrantedStatusEffect() && $food->getGrantedStatusEffectDuration() > 0)
        {
            $this->grantedStatusEffects[] = [
                'effect' => $food->getGrantedStatusEffect(),
                'duration' => $food->getGrantedStatusEffectDuration()
            ];
        }

        if($food->getLeftovers())
            $this->leftovers[] = $food->getLeftovers();

        if($spice && $spice->getEffects())
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

            foreach(FlavorEnum::getValues() as $flavor)
                $this->{$flavor} += $effects->{'get' . $flavor}();

            if($effects->getLeftovers())
                $this->leftovers[] = $effects->getLeftovers();
        }
    }
}

class BonusItemChance
{
    public int $chance;
    /** @var ItemGroup */ public $itemGroup;

    public function __construct(ItemFood $food)
    {
        $this->chance = (int)$food->getChanceForBonusItem();
        $this->itemGroup = $food->getBonusItemGroup();
    }
}