<?php
namespace App\Model;

use App\Enum\Enum;

class SummoningScrollMonster
{
    public string $name;
    public string $nameWithArticle;
    public string $majorReward;

    /** @var string[] */
    public array $minorRewards;

    public ?string $element = null;
    public ?string $fieldGuideEntry = null;

    public static function CreateDragon(): SummoningScrollMonster
    {
        $monster = new SummoningScrollMonster();

        $monster->name = 'Dragon';
        $monster->nameWithArticle = 'a Dragon';
        $monster->majorReward = 'Dragon Vase';
        $monster->minorRewards = [ 'Scales', 'Gold Bar' ];
        $monster->element = SummoningScrollMonsterElementEnum::FIRE;

        return $monster;
    }

    public static function CreateBalrog(): SummoningScrollMonster
    {
        $monster = new SummoningScrollMonster();

        $monster->name = 'Balrog';
        $monster->nameWithArticle = 'a Balrog';
        $monster->majorReward = 'Blackonite';
        $monster->minorRewards = [ 'Quintessence', 'Talon' ];
        $monster->element = SummoningScrollMonsterElementEnum::FIRE;

        return $monster;
    }

    public static function CreateBasabasa(): SummoningScrollMonster
    {
        $monster = new SummoningScrollMonster();

        $monster->name = 'Basabasa';
        $monster->nameWithArticle = 'a Basabasa';
        $monster->majorReward = 'Black Feathers';
        $monster->minorRewards = [ 'Feathers', 'Talon' ];
        $monster->element = SummoningScrollMonsterElementEnum::FIRE;

        return $monster;
    }

    public static function CreateIfrit(): SummoningScrollMonster
    {
        $monster = new SummoningScrollMonster();

        $monster->name = 'Ifrit';
        $monster->nameWithArticle = 'Ifrit';
        $monster->majorReward = 'Gold Crown';
        $monster->minorRewards = [ 'Quintessence', 'White Cloth' ];
        $monster->element = SummoningScrollMonsterElementEnum::FIRE;

        return $monster;
    }

    public static function CreateCherufe(): SummoningScrollMonster
    {
        $monster = new SummoningScrollMonster();

        $monster->name = 'Cherufe';
        $monster->nameWithArticle = 'Cherufe';
        $monster->majorReward = 'Meteorite';
        $monster->minorRewards = [ 'Liquid-hot Magma', 'Iron Ore' ];
        $monster->element = SummoningScrollMonsterElementEnum::FIRE;

        return $monster;
    }

    public static function CreateCrystallineEntity(): SummoningScrollMonster
    {
        $monster = new SummoningScrollMonster();

        $monster->name = 'Crystalline Entity';
        $monster->nameWithArticle = 'a Crystalline Entity';
        $monster->majorReward = 'Forgetting Scroll';
        $monster->minorRewards = [ 'Fiberglass', 'Gypsum' ];
        $monster->element = SummoningScrollMonsterElementEnum::ELECTRICITY;

        return $monster;
    }

    public static function CreateBivusRelease(): SummoningScrollMonster
    {
        $monster = new SummoningScrollMonster();

        $monster->name = 'Bivu\'s Release';
        $monster->nameWithArticle = 'Bivu\'s Release';
        $monster->majorReward = 'Collimated Lance';
        $monster->minorRewards = [ 'Photon', 'Gravitational Waves' ];
        $monster->element = SummoningScrollMonsterElementEnum::FIRE;

        $monster->fieldGuideEntry = 'Bivu';

        return $monster;
    }

    public static function CreateSpaceJelly(): SummoningScrollMonster
    {
        $monster = new SummoningScrollMonster();

        $monster->name = 'Intergalactic Space Jelly';
        $monster->nameWithArticle = 'an Intergalactic Space Jelly';
        $monster->majorReward = 'Transparent Bow';
        $monster->minorRewards = [ 'Pectin', 'Jellyfish Jelly' ];
        $monster->element = SummoningScrollMonsterElementEnum::ELECTRICITY;

        return $monster;
    }

    public static function CreateDiscipleOfHunCame(): SummoningScrollMonster
    {
        $monster = new SummoningScrollMonster();

        $monster->name = 'Disciple of Hun-Came';
        $monster->nameWithArticle = 'a Disciple of Hun-Came';
        $monster->majorReward = 'Blackonite';
        $monster->minorRewards = [ 'Scales', 'Quintessence' ];
        $monster->element = SummoningScrollMonsterElementEnum::DARKNESS;

        $monster->fieldGuideEntry = 'Hun-Came';

        return $monster;
    }

}

class SummoningScrollMonsterElementEnum
{
    use Enum;

    public const FIRE = 'Fire';
    public const ELECTRICITY = 'Electricity';
    public const DARKNESS = 'Darkness';
}