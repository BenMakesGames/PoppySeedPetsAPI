<?php
declare(strict_types=1);

namespace App\Functions;

use App\Exceptions\PSPFormValidationException;
use Symfony\Component\HttpFoundation\Request;

final class RequestFunctions
{
    /**
     * @return int[]
     */
    public static function getUniqueIdsOrThrow(Request $request, string $keyName, string $exceptionMessage): array
    {
        if(!$request->request->has($keyName))
            throw new PSPFormValidationException($exceptionMessage);

        $itemIds = $request->request->all($keyName);

        $itemIds = array_map(fn($i) => (int)$i, $itemIds);
        $itemIds = array_filter($itemIds, fn($i) => $i > 0);
        $itemIds = array_unique($itemIds);

        if(count($itemIds) < 1)
            throw new PSPFormValidationException($exceptionMessage);

        return $itemIds;
    }
}