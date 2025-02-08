<?php
declare(strict_types=1);

namespace App\Enum;

final class PetActivityStatEnum
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
    public const UMBRA = 'umbra'; // do NOT rename this to "Arcana" - this actually represents time spent in the Umbra!
    public const PARK_EVENT = 'parkevent';
    public const OTHER = 'other';
}
