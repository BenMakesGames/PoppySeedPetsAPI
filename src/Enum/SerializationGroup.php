<?php
namespace App\Enum;

// each SerializationGroup has a corresponding model class in the front-end
final class SerializationGroup
{
    const ADMIN = 'admin';

    const MY_ACCOUNT = 'myAccount';
    const MY_PET = 'myPet';
    const MY_INVENTORY = 'myInventory';
    const MY_STATS = 'myStats';
    const PET_ACTIVITY_LOGS = 'petActivityLogs';
    const FILTER_RESULTS = 'filterResults';
    const ITEM_ENCYCLOPEDIA = 'itemEncyclopedia';
    const PET_ENCYCLOPEDIA = 'petEncyclopedia';
    const PUBLIC_PROFILE = 'publicProfile';
}