<?php

namespace App\Controller\MonsterOfTheWeek;

use App\Entity\Item;
use App\Enum\MonsterOfTheWeekEnum;
use App\Enum\PetSkillEnum;

final class MonsterOfTheWeekHelpers
{
    public static function getConsolationPrize(string $monster): string
    {
        return match($monster)
        {
            MonsterOfTheWeekEnum::ANHUR => 'Crooked Stick',
            MonsterOfTheWeekEnum::BOSHINOGAMI => 'Fluff',
            MonsterOfTheWeekEnum::CARDEA => 'String',
            MonsterOfTheWeekEnum::DIONYSUS => 'Blueberries',
            MonsterOfTheWeekEnum::HUEHUECOYOTL => 'Music Note',
            default => throw new \InvalidArgumentException("Invalid monster")
        };
    }

    public static function getBasePrizeValues(string $monster): array
    {
        return match($monster)
        {
            // [ easy = easy, medium = easy * ~3, hard = medium * ~2.5 ]
            MonsterOfTheWeekEnum::ANHUR => [ 25, 75, 200 ],
            MonsterOfTheWeekEnum::BOSHINOGAMI => [ 40, 120, 300 ],
            MonsterOfTheWeekEnum::CARDEA => [ 10, 30, 75 ],
            MonsterOfTheWeekEnum::DIONYSUS => [ 100, 300, 750 ],
            MonsterOfTheWeekEnum::HUEHUECOYOTL => [ 10, 30, 75 ],
            default => throw new \InvalidArgumentException("Invalid monster")
        };
    }

    public static function getSpiritNameWithArticle(string $monster): string
    {
        return match($monster)
        {
            MonsterOfTheWeekEnum::ANHUR => 'a Hunter of Anhur',
            MonsterOfTheWeekEnum::BOSHINOGAMI => 'some Boshinogami',
            MonsterOfTheWeekEnum::CARDEA => 'Cardea\'s Lockbearer',
            MonsterOfTheWeekEnum::DIONYSUS => 'Dionysus\'s Hunger',
            MonsterOfTheWeekEnum::HUEHUECOYOTL => 'Huehuecoyotl\'s Folly',
            default => throw new \InvalidArgumentException("Invalid monster"),
        };
    }

    public static function getEasyPrizes(string $monster): array
    {
        return match($monster)
        {
            MonsterOfTheWeekEnum::ANHUR => [ 'Monster-summoning Scroll', 'Potion of Brawling', 'Wolf\'s Bane' ],
            MonsterOfTheWeekEnum::BOSHINOGAMI => [ 'Handicrafts Supply Box', 'Potion of Crafts' ],
            MonsterOfTheWeekEnum::CARDEA => [ 'Magpie Pouch', 'Magpie\'s Deal', 'Tile: Thieving Magpie' ],
            MonsterOfTheWeekEnum::DIONYSUS => [ 'Essence d\'Assortiment', 'Potion of Nature' ],
            MonsterOfTheWeekEnum::HUEHUECOYOTL => [ 'Potion of Music', 'Dancing Sword', 'LP' ],
            default => throw new \InvalidArgumentException("Invalid monster")
        };
    }

    public static function getMediumPrizes(string $monster): array
    {
        return match ($monster)
        {
            MonsterOfTheWeekEnum::ANHUR => [ 'Tile: Giant Cave Toad', 'Monster Box', 'Very Strongbox' ],
            MonsterOfTheWeekEnum::BOSHINOGAMI => [ 'Hat Box' ],
            MonsterOfTheWeekEnum::CARDEA => [ 'Tile: Flying Keys, Only', 'Magic Crystal Ball', 'White Feathers', 'Tile: Triple Chest' ],
            MonsterOfTheWeekEnum::DIONYSUS => [ 'Tile: Statue Garden', 'Whisper Stone' ],
            MonsterOfTheWeekEnum::HUEHUECOYOTL => [ 'Magic Hourglass', 'Maraca', 'Tile: Very Cool Beans' ],
            default => throw new \InvalidArgumentException("Invalid monster"),
        };
    }

    public static function getHardPrizes(string $monster): array
    {
        return match($monster)
        {
            MonsterOfTheWeekEnum::ANHUR => [ 'Skill Scroll: Brawl', 'Skill Scroll: Stealth' ],
            MonsterOfTheWeekEnum::BOSHINOGAMI => [ 'Scroll of Illusions', 'Skill Scroll: Crafts', 'Behatting Scroll' ],
            MonsterOfTheWeekEnum::CARDEA => [ 'Ruby Feather', 'Skill Scroll: Arcana', 'Skill Scroll: Science', 'Forgetting Scroll' ],
            MonsterOfTheWeekEnum::DIONYSUS => [ 'Skill Scroll: Nature' ],
            MonsterOfTheWeekEnum::HUEHUECOYOTL => [ 'Skill Scroll: Music' ],
            default => throw new \InvalidArgumentException("Invalid monster"),
        };
    }

    public static function getItemValue(string $monster, Item $item): int
    {
        return match($monster)
        {
            MonsterOfTheWeekEnum::ANHUR => self::getItemValueForAnhur($item),
            MonsterOfTheWeekEnum::BOSHINOGAMI => self::getItemValueForBoshinogami($item),
            MonsterOfTheWeekEnum::CARDEA => self::getItemValueForCardea($item),
            MonsterOfTheWeekEnum::DIONYSUS => self::getItemValueForDionysus($item),
            MonsterOfTheWeekEnum::HUEHUECOYOTL => self::getItemValueForHuehuecoyotl($item),
            default => 0,
        };
    }

    public static function getItemValueForAnhur(Item $item): int
    {
        $points = 0;

        if($item->getTool())
        {
            $effects = $item->getTool();

            $points = max($points, $effects->getBrawl() + ($effects->getFocusSkill() == PetSkillEnum::BRAWL ? 2 : 0));
        }

        if($item->getEnchants() && $item->getEnchants()->getEffects())
        {
            $effects = $item->getEnchants()->getEffects();
            $points = max($points, $effects->getBrawl() + ($effects->getFocusSkill() == PetSkillEnum::BRAWL ? 2 : 0));
        }

        if($item->getFood())
        {
            $effects = $item->getFood();

            $points = max($points, $effects->getGrantedSkill() == PetSkillEnum::BRAWL ? 2 : 0);
        }

        return $points;
    }

    public static function getItemValueForBoshinogami(Item $item): int
    {
        if(!$item->getHat() || $item->getName() === 'Anniversary Poppy Seed* Muffin')
            return 0;

        $points = $item->getRecycleValue() + ceil($item->getMuseumPoints() * 1.5) - 1;

        if(str_ends_with($item->getName(), 'Baabble'))
            $points += 10;

        return $points;
    }

    public static function getItemValueForCardea(Item $item): int
    {
        if(!$item->hasItemGroup('Key') && $item->getName() !== 'Password')
            return 0;

        $points = 2 + floor($item->getRecycleValue() / 3);

        if($item->getTool() && $item->getTool()->getLeadsToAdventure())
            $points += 3;

        return $points;
    }

    public static function getItemValueForDionysus(Item $item): int
    {
        if(!$item->getFood())
            return 0;

        $food = $item->getFood();

        return $food->getFood() + $food->getLove() +
            ($food->getAlcohol() + $food->getCaffeine() + $food->getPsychedelic()) * 2;
    }

    public static function getItemValueForHuehuecoyotl(Item $item): int
    {
        if($item->getName() === 'Musical Scales')
            return 2;

        $points = 0;

        if($item->getTool())
        {
            $effects = $item->getTool();

            $points = max($points, $effects->getMusic() + ($effects->getFocusSkill() == PetSkillEnum::MUSIC ? 2 : 0));
        }

        if($item->getEnchants() && $item->getEnchants()->getEffects())
        {
            $effects = $item->getEnchants()->getEffects();
            $points = max($points, $effects->getMusic() + ($effects->getFocusSkill() == PetSkillEnum::MUSIC ? 2 : 0));
        }

        if($item->getFood())
        {
            $effects = $item->getFood();

            $points = max($points, $effects->getGrantedSkill() == PetSkillEnum::MUSIC ? 2 : 0);
        }

        if($item->hasItemGroup('Musical Instrument'))
            $points += 2;

        return $points;
    }
}