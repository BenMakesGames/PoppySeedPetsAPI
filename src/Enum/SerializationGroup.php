<?php
namespace App\Enum;

// each SerializationGroup has a corresponding model class in the front-end
final class SerializationGroup
{
    const MY_ACCOUNT = 'myAccount';
    const MY_PET = 'myPet';
    const MY_INVENTORY = 'myInventory';
    const PET_ACTIVITY_LOGS = 'petActivityLogs';

    // for viewing profiles:
    const PUBLIC_PROFILE = 'publicProfile';
    const SEMI_PRIVATE_PROFILE = 'semiPrivateProfile';
    const PRIVATE_PROFILE = 'privateProfile';

    // encyclopedias:
    const ITEM_ENCYCLOPEDIA = 'itemEncyclopedia';
    const FILTER_RESULTS = 'filterResults';
}