<?php
namespace App\Enum;

class PetActivityStatEnum
{
    use Enum;

    public const CRAFT = 'craft';
    public const MAGIC_BIND = 'magicbind';
    public const SMITH = 'smith';
    public const PLASTIC_PRINT = 'plasticprint';
    public const FISH = 'fish';
    public const GATHER = 'gather';
    public const HUNT = 'hunt';
    public const PROTOCOL_7 = 'protocol7';
    public const PROGRAM = 'program';
    public const UMBRA = 'umbra';
    public const PARK_EVENT = 'parkevent';
    public const HANG_OUT = 'hangout';
    public const OTHER = 'other';
}