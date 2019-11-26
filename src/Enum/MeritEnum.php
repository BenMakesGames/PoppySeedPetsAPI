<?php
namespace App\Enum;

final class MeritEnum
{
    use Enum;

    // obtained through affection rewards
    public const EIDETIC_MEMORY = 'Eidetic Memory';
    public const BLACK_HOLE_TUM = 'Black Hole Tum';
    public const LUCKY = 'Lucky';
    public const MOON_BOUND = 'Moon-bound';
    public const NATURAL_CHANNEL = 'Natural Channel';
    public const NO_SHADOW_OR_REFLECTION = 'No Shadow; No Reflection';
    public const SOOTHING_VOICE = 'Soothing Voice';
    public const SPIRIT_COMPANION = 'Spirit Companion';
    public const PROTOCOL_7 = 'Protocol 7';
    public const INTROSPECTIVE = 'Introspective';
    public const VOLAGAMY = 'Volagamy';

    // obtained through items
    public const BEHATTED = 'Behatted';

    // obtained through house time spent
    public const MIND_OVER_MATTER = 'Mind Over Matter';
    public const MATTER_OVER_MIND = 'Matter Over Mind';
    public const MODERATION = 'Moderation';

    public const FORCE_OF_WILL = 'Force of Will';
    public const FORCE_OF_NATURE = 'Force of Nature';
    public const BALANCE = 'Balance';

    // not yet implemented
    //public const PAST_LIFE = 'Past Life';
}