<?php

namespace App\Controller\MonsterOfTheWeek;

use App\Entity\Inventory;
use App\Enum\MonsterOfTheWeekEnum;
use App\Enum\PetSkillEnum;
use App\Functions\RecipeRepository;

final class MonsterOfTheWeekHelpers
{
    public static function getBasePrizeValues(string $monster): array
    {
        switch($monster)
        {
            // [ easy = easy, medium = easy * ~2.5, hard = easy * ~3 ]
            case MonsterOfTheWeekEnum::ANHUR: return [ 25, 60, 200 ];
            case MonsterOfTheWeekEnum::BOSHINOGAMI: return [ 40, 100, 300 ];
            case MonsterOfTheWeekEnum::CARDEA: return [ 10, 25, 70 ];
            case MonsterOfTheWeekEnum::DIONYSUS: return [ 100, 250, 700 ];
            case MonsterOfTheWeekEnum::HUEHUECOYOTL: return [ 25, 60, 200 ];
            default: throw new \InvalidArgumentException("Invalid monster");
        }
    }

    public static function getEasyPrizes(string $monster): array
    {
        switch($monster)
        {
            case MonsterOfTheWeekEnum::ANHUR: return [ 'Monster-summoning Scroll', 'Potion of Brawling', 'Wolf\'s Bane' ];
            case MonsterOfTheWeekEnum::BOSHINOGAMI: return [ 'Handicrafts Supply Box', 'Potion of Crafts' ];
            case MonsterOfTheWeekEnum::CARDEA: return [ 'Magpie Pouch, Magpie\'s Deal', 'Tile: Thieving Magpie' ];
            case MonsterOfTheWeekEnum::DIONYSUS: return [ 'Essence d\'Assortiment', 'Potion of Nature' ];
            case MonsterOfTheWeekEnum::HUEHUECOYOTL: return [ 'Potion of Music', 'Dancing Sword', 'LP' ];
            default: throw new \InvalidArgumentException("Invalid monster");
        }
    }

    public static function getMediumPrizes(string $monster): array
    {
        switch($monster)
        {
            case MonsterOfTheWeekEnum::ANHUR: return [ 'Tile: Giant Cave Toad', 'Monster Box', 'Very Strongbox' ];
            case MonsterOfTheWeekEnum::BOSHINOGAMI: return [ 'Hat Box' ];
            case MonsterOfTheWeekEnum::CARDEA: return [ 'Tile: Flying Keys, Only', 'Magic Crystal Ball', 'White Feathers', 'Tile: Triple Chest' ];
            case MonsterOfTheWeekEnum::DIONYSUS: return [ 'Tile: Statue Garden', 'Whisper Stone' ];
            case MonsterOfTheWeekEnum::HUEHUECOYOTL: return [ 'Magic Hourglass', 'Maraca', 'Tile: Very Cool Beans' ];
            default: throw new \InvalidArgumentException("Invalid monster");
        }
    }

    public static function getHardPrizes(string $monster): array
    {
        switch($monster)
        {
            case MonsterOfTheWeekEnum::ANHUR: return [ 'Skill Scroll: Brawl', 'Skill Scroll: Stealth' ];
            case MonsterOfTheWeekEnum::BOSHINOGAMI: return [ 'Scroll of Illusions', 'Skill Scroll: Crafts', 'Behatting Scroll' ];
            case MonsterOfTheWeekEnum::CARDEA: return [ 'Ruby Feather', 'Skill Scroll: Arcana', 'Skill Scroll: Science', 'Forgetting Scroll' ];
            case MonsterOfTheWeekEnum::DIONYSUS: return [ 'Skill Scroll: Nature' ];
            case MonsterOfTheWeekEnum::HUEHUECOYOTL: return [ 'Skill Scroll: Music' ];
            default: throw new \InvalidArgumentException("Invalid monster");
        }
    }

    public static function getInventoryValue(string $monster, Inventory $item): int
    {
        switch($monster)
        {
            case MonsterOfTheWeekEnum::ANHUR: return self::getInventoryValueForAnhur($item);
            case MonsterOfTheWeekEnum::BOSHINOGAMI: return self::getInventoryValueForBoshinogami($item);
            case MonsterOfTheWeekEnum::CARDEA: return self::getInventoryValueForCardea($item);
            case MonsterOfTheWeekEnum::DIONYSUS: return self::getInventoryValueForDionysus($item);
            case MonsterOfTheWeekEnum::HUEHUECOYOTL: return self::getInventoryValueForHuehuecoyotl($item);
            default: return 0;
        }
    }

    public static function getInventoryValueForAnhur(Inventory $item): int
    {
        $points = 0;

        if($item->getItem()->getTool())
        {
            $effects = $item->getItem()->getTool();

            $points = max($points, $effects->getBrawl() + ($effects->getFocusSkill() == PetSkillEnum::BRAWL ? 2 : 0));
        }

        if($item->getItem()->getEnchants() && $item->getItem()->getEnchants()->getEffects())
        {
            $effects = $item->getItem()->getEnchants()->getEffects();
            $points = max($points, $effects->getBrawl() + ($effects->getFocusSkill() == PetSkillEnum::BRAWL ? 2 : 0));
        }

        if($item->getItem()->getFood())
        {
            $effects = $item->getItem()->getFood();

            $points = max($points, $effects->getGrantedSkill() == PetSkillEnum::BRAWL ? 2 : 0);
        }

        return $points;
    }

    public static function getInventoryValueForBoshinogami(Inventory $item): int
    {
        if(!$item->getItem()->getHat())
            return 0;

        $points = 0;

        if($item->getItem()->getFood())
            $points = $item->getItem()->getFood()->getGrantedSkill() ? 4 : 1;
        else if($item->getItem()->getName() === 'Tiny Black Hole')
            $points = 5;
        else if($item->getItem()->getUseActions())
            $points = 8;
        else if($item->getItem()->getTool() || $item->getItem()->getEnchants() || $item->getItem()->getSpice())
            $points = 7;
        else
            $points = 15;

        if(str_ends_with($item->getItem()->getName(), 'Baabble'))
            $points += 20;

        $points += $item->getItem()->getRecycleValue() + $item->getItem()->getMuseumPoints() - 1;

        return $points;
    }

    public static function getInventoryValueForCardea(Inventory $item): int
    {
        if(!$item->getItem()->hasItemGroup('Key') && $item->getItem()->getName() !== 'Password')
            return 0;

        $points = 2 + floor($item->getItem()->getRecycleValue() / 3);

        if($item->getItem()->getTool() && $item->getItem()->getTool()->getLeadsToAdventure())
            $points += 3;

        return $points;
    }

    public static function getInventoryValueForDionysus(Inventory $item): int
    {
        if(!$item->getItem()->getFood())
            return 0;

        $food = $item->getItem()->getFood();

        return $food->getFood() + $food->getLove() +
            ($food->getAlcohol() + $food->getCaffeine() + $food->getPsychedelic()) * 2;
    }

    public static function getInventoryValueForHuehuecoyotl(Inventory $item): int
    {
        if($item->getItem()->getName() === 'Musical Scales')
            return 2;

        $points = 0;

        if($item->getItem()->getTool())
        {
            $effects = $item->getItem()->getTool();

            $points = max($points, $effects->getMusic() + ($effects->getFocusSkill() == PetSkillEnum::MUSIC ? 2 : 0));
        }

        if($item->getItem()->getEnchants() && $item->getItem()->getEnchants()->getEffects())
        {
            $effects = $item->getItem()->getEnchants()->getEffects();
            $points = max($points, $effects->getMusic() + ($effects->getFocusSkill() == PetSkillEnum::MUSIC ? 2 : 0));
        }

        if($item->getItem()->getFood())
        {
            $effects = $item->getItem()->getFood();

            $points = max($points, $effects->getGrantedSkill() == PetSkillEnum::MUSIC ? 2 : 0);
        }

        if($item->getItem()->hasItemGroup('Musical Instrument'))
            $points += 2;

        return $points;
    }
}