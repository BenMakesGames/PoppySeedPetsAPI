<?php
namespace App\Enum;

class FieldGuideEntryTypeEnum
{
    use Enum;

    public const ANIMAL = 'animal';
    public const PLANT = 'plant';
    public const CELESTIAL = 'celestial';
    public const SYNTHETIC = 'synthetic';
    public const CRYPTID = 'cryptid';
    public const LOCATION = 'location';
    public const CEREMONY = 'ceremony';
}