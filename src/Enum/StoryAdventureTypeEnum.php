<?php
declare(strict_types=1);

namespace App\Enum;

class StoryAdventureTypeEnum
{
    use Enum;

    public const STORY = 'Story';
    public const MINE_GOLD = 'MineGold';
    public const COLLECT_STONE = 'CollectStone';
    public const HUNT = 'HuntAutoScaling';
    public const GATHER = 'Gather';
    public const TREASURE_HUNT = 'TreasureHunt';
    public const WANDERING_MONSTER = 'WanderingMonster';
    public const RANDOM_RECRUIT = 'RecruitTown';
}