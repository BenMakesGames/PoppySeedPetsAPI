<?php

namespace App\Enum;

final class PetBadgeEnum
{
    use Enum;

    public const COMPLETED_HEART_DIMENSION = 'completedHeartDimension';
    public const FIRST_PLACE_CHESS = 'chessWinner';
    public const FIRST_PLACE_JOUSTING = 'joustingWinner';
    public const FIRST_PLACE_KIN_BALL = 'kinBallWinner'; // TODO: create graphic
    public const CREATED_SENTIENT_BEETLE = 'createdSentientBeetle';
    public const MET_THE_FLUFFMONGER = 'fluffmonger'; // TODO: create graphic
    public const OUTSMARTED_A_THIEVING_MAGPIE = 'outsmartedAThievingMagpie';
    public const DECEIVED_A_VAMPIRE = 'deceivedAVampire'; // TODO: create graphic
    public const FOUND_CETGUELIS_TREASURE = 'foundCetguelisTreasure';
    public const HAD_A_BABY = 'parent';
    public const WAS_AN_ACCOUNTANT = 'increasedADragonsHoard'; // TODO: create graphic

    public const CLIMB_TO_TOP_OF_BEANSTALK = 'summitBeanstalk';
    public const SING_WITH_WHALES = 'singWithWhales';

    public const FOUND_METATRONS_FIRE = 'foundMetatronsFire';
    public const FOUND_VESICA_HYDRARGYRUM = 'foundVesicaHydrargyrum'; // TODO: create graphic
    public const FOUND_EARTHS_EGG = 'foundEarthsEgg';
    public const FOUND_MERKABA_OF_AIR = 'foundMerkabaOfAir'; // TODO: create graphic

    public const DEFEATED_NOETALAS_WING = 'defeatedNoetalasWing';
    public const DEFEATED_CRYSTALLINE_ENTITY = 'defeatedCrystallineEntity'; // TODO: graphic
    public const DEFEATED_BIVUS_RELEASE = 'defeatedBivusRelease';
}
