<?php
namespace App\Enum;

// each SerializationGroup has a corresponding model class in the front-end
final class SerializationGroupEnum
{
    use Enum;

    const ITEM_ADMIN = 'itemAdmin';
    const MY_ACCOUNT = 'myAccount';
    const MY_PET = 'myPet';
    const MY_INVENTORY = 'myInventory';
    const MY_STATS = 'myStats';
    const NOTIFICATION_PREFERENCES = 'notificationPreferences';
    const PET_ACTIVITY_LOGS = 'petActivityLogs';
    const PET_PUBLIC_PROFILE = 'petPublicProfile';
    const FILTER_RESULTS = 'filterResults';
    const ITEM_ENCYCLOPEDIA = 'itemEncyclopedia';
    const PET_ENCYCLOPEDIA = 'petEncyclopedia';
    const USER_PUBLIC_PROFILE = 'userPublicProfile';
    const ARTICLE = 'article';
    const MUSEUM = 'museum';
    const QUERY_ADMIN = 'queryAdmin';
    const PET_SHELTER_PET = 'petShelterPet';
    const MARKET_ITEM = 'marketItem';
    const TRADER_OFFER = 'traderOffer';
    const KNOWN_RECIPE = 'knownRecipe';
    const PARK_EVENT = 'parkEvent';
    const PET_FRIEND = 'petFriend';
    const GREENHOUSE_PLANT = 'greenhousePlant';
    const MY_SEEDS = 'mySeeds';
    const REMINDER = 'reminder';
    const HOLLOW_EARTH = 'hollowEarth';
    const USER_TYPEAHEAD = 'userTypeahead';
    const AVAILABLE_MERITS = 'availableMerits';
}