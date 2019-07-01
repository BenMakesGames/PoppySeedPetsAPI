<?php
namespace App\Enum;

// each SerializationGroup has a corresponding model class in the front-end
final class SerializationGroup
{
    const ITEM_ADMIN = 'itemAdmin';

    const MY_ACCOUNT = 'myAccount';
    const MY_PET = 'myPet';
    const MY_INVENTORY = 'myInventory';
    const MY_STATS = 'myStats';
    const PET_ACTIVITY_LOGS = 'petActivityLogs';
    const PET_PUBLIC_PROFILE = 'petPublicProfile';
    const FILTER_RESULTS = 'filterResults';
    const ITEM_ENCYCLOPEDIA = 'itemEncyclopedia';
    const PET_ENCYCLOPEDIA = 'petEncyclopedia';
    const USER_PUBLIC_PROFILE = 'userPublicProfile';
    const ARTICLE = 'article';
}