<?php

namespace App\Functions;

use App\Entity\User;
use App\Exceptions\PSPInvalidOperationException;

class HouseSittingHelpers
{
    public static function canHouseSit(SimpleDb $db, int $houseSitterId, int $houseSittingForId): bool
    {
        $count = (int)($db
            ->query('SELECT COUNT(id) FROM house_sitter WHERE user_id = ? AND house_sitter_id = ?', [ $houseSittingForId, $houseSitterId ])
            ->getSingleValue());

        return $count > 0;
    }

    public static function canHouseSitOrThrow(SimpleDb $db, User $currentUser, int $houseSittingForId)
    {
        if(!self::canHouseSit($db, $currentUser->getId(), $houseSittingForId))
            throw new PSPInvalidOperationException('You cannot house sit for that player.');
    }
}