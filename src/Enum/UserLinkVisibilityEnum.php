<?php
declare(strict_types=1);

namespace App\Enum;

class UserLinkVisibilityEnum
{
    use Enum;

    public const LOGGED_IN = 'LoggedIn';
    public const FOLLOWED = 'Followed';
}