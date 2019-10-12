<?php
namespace App\Enum;

final class StatusEffectEnum
{
    use Enum;

    public const ALERT = 'Alert';
    public const CAFFEINATED = 'Caffeinated';
    public const TIRED = 'Tired';
    public const INSPIRED = 'Inspired';
}