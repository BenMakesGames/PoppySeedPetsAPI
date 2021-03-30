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
    public const MIRRORED = 'Mirrored'; // flips graphic
    public const INVERTED = 'Inverted'; // inverts pet colors
    public const VERY_INVERTED = 'Very Inverted'; // inverts pet & equipment colors
    public const WONDROUS_STRENGTH = 'Wondrous Strength';
    public const WONDROUS_STAMINA = 'Wondrous Stamina';
    public const WONDROUS_DEXTERITY = 'Wondrous Dexterity';
    public const WONDROUS_PERCEPTION = 'Wondrous Perception';
    public const WONDROUS_INTELLIGENCE = 'Wondrous Intelligence';

    // obtained through house time spent
    public const MIND_OVER_MATTER = 'Mind Over Matter';
    public const MATTER_OVER_MIND = 'Matter Over Mind';
    public const MODERATION = 'Moderation';

    public const FORCE_OF_WILL = 'Force of Will';
    public const FORCE_OF_NATURE = 'Force of Nature';
    public const BALANCE = 'Balance';

    // available by becoming a grandparent pet
    public const NEVER_EMBARRASSED = 'Never Embarrassed';
    public const EVERLASTING_LOVE = 'Everlasting Love';
    public const NOTHING_TO_FEAR = 'Nothing to Fear';

    // every pet gets one (list is in MeritRepository::POSSIBLE_STARTING_MERITS)
    public const BURPS_MOTHS = 'Burps Moths';
    public const FRIEND_OF_THE_WORLD = 'Friend of the World'; // treats rivals as friends; treats fwbs as mates; always gives in to relationship changes
    public const GOURMAND = 'Gourmand'; // larger stomach
    public const SPECTRAL = 'Spectral'; // opacity = 75%; +1 stealth
    public const PREHENSILE_TONGUE = 'Prehensile Tongue'; // +1 dex; ";P" instead of ";)"
    public const LOLLIGOVORE = 'Lolligovore'; // bonus from eating tentacles
    public const HYPERCHROMATIC = 'Hyperchromatic'; // randomly changes colors
    public const DREAMWALKER = 'Dreamwalker'; // gets items from dreams
    public const GREGARIOUS = 'Gregarious'; // can be in up to 4 groups
    public const SHEDS = 'Sheds';
    public const DARKVISION = 'Darkvision'; // can see in the dark

    // not yet implemented
    //public const PAST_LIFE = 'Past Life';
}
