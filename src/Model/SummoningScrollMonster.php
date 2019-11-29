<?php
namespace App\Model;

class SummoningScrollMonster
{
    /** @var string */
    public $name;

    /** @var string */
    public $majorReward;

    /** @var string[] */
    public $minorRewards;

    public static function CreateDragon(): SummoningScrollMonster
    {
        $monster = new SummoningScrollMonster();

        $monster->name = 'Dragon';
        $monster->majorReward = 'Dragon Vase';
        $monster->minorRewards = [ 'Scales', 'Gold Bar' ];

        return $monster;
    }

    public static function CreateBalrog(): SummoningScrollMonster
    {
        $monster = new SummoningScrollMonster();

        $monster->name = 'Balrog';
        $monster->majorReward = 'Blackonite';
        $monster->minorRewards = [ 'Quintessence', 'Talon' ];

        return $monster;
    }

    public static function CreateBasabasa(): SummoningScrollMonster
    {
        $monster = new SummoningScrollMonster();

        $monster->name = 'Basabasa';
        $monster->majorReward = 'Black Feathers';
        $monster->minorRewards = [ 'Feathers', 'Talon' ];

        return $monster;
    }

    public static function CreateIfrit(): SummoningScrollMonster
    {
        $monster = new SummoningScrollMonster();

        $monster->name = 'Ifrit';
        $monster->majorReward = 'Gold Crown';
        $monster->minorRewards = [ 'Quintessence', 'White Cloth' ];

        return $monster;
    }

    public static function CreateCherufe(): SummoningScrollMonster
    {
        $monster = new SummoningScrollMonster();

        $monster->name = 'Cherufe';
        $monster->majorReward = 'Meteorite';
        $monster->minorRewards = [ 'Liquid-hot Magma', 'Iron Ore' ];

        return $monster;
    }

}