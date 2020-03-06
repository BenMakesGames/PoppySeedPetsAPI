<?php
namespace App\Enum;

// each SerializationGroup has a corresponding model class in the front-end
final class SerializationGroupEnum
{
    use Enum;

    public const ITEM_ADMIN = 'itemAdmin';
    public const MY_ACCOUNT = 'myAccount';
    public const MY_PET = 'myPet';
    public const MY_INVENTORY = 'myInventory';
    public const MY_STATS = 'myStats';
    public const NOTIFICATION_PREFERENCES = 'notificationPreferences';
    public const PET_ACTIVITY_LOGS = 'petActivityLogs';
    public const PET_PUBLIC_PROFILE = 'petPublicProfile';
    public const FILTER_RESULTS = 'filterResults';
    public const ITEM_ENCYCLOPEDIA = 'itemEncyclopedia';
    public const PET_ENCYCLOPEDIA = 'petEncyclopedia';
    public const USER_PUBLIC_PROFILE = 'userPublicProfile';
    public const ARTICLE = 'article';
    public const MUSEUM = 'museum';
    public const QUERY_ADMIN = 'queryAdmin';
    public const PET_SHELTER_PET = 'petShelterPet';
    public const MARKET_ITEM = 'marketItem';
    public const TRADER_OFFER = 'traderOffer';
    public const KNOWN_RECIPE = 'knownRecipe';
    public const PARK_EVENT = 'parkEvent';
    public const PET_FRIEND = 'petFriend';
    public const PET_GROUP = 'petGroup';
    public const PET_GROUP_DETAILS = 'petGroupDetails';
    public const PET_GROUP_INDEX = 'petGroupIndex';
    public const GREENHOUSE_PLANT = 'greenhousePlant';
    public const MY_GREENHOUSE = 'myGreenhouse';
    public const MY_SEEDS = 'mySeeds';
    public const REMINDER = 'reminder';
    public const HOLLOW_EARTH = 'hollowEarth';
    public const USER_TYPEAHEAD = 'userTypeahead';
    public const AVAILABLE_MERITS = 'availableMerits';
    public const FIREPLACE_MANTLE = 'fireplaceMantle';
    public const MY_FIREPLACE = 'myFireplace';
    public const FIREPLACE_FUEL = 'fireplaceFuel';
    public const STORY = 'story';
    public const MY_BEEHIVE = 'myBeehive';
    public const MY_TRANSACTION = 'myTransaction';
    public const MERIT_ENCYCLOPEDIA = 'meritEncyclopedia';
}
