<?php
namespace App\Enum;

final class SerializationGroup
{
    const LOG_IN = 'logIn';
    const MY_PETS = 'myPets';
    const MY_INVENTORY = 'myInventory';
    const PET_ACTIVITY_LOGS = 'petActivityLogs';

    // for viewing profiles:
    const PUBLIC_PROFILE = 'publicProfile';
    const SEMI_PRIVATE_PROFILE = 'semiPrivateProfile';
    const PRIVATE_PROFILE = 'privateProfile';

    const ENCYCLOPEDIA = 'encyclopedia';
}