<?php

namespace App\Functions;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class RequestFunctions
{
    /**
     * @return int[]
     */
    public static function getUniqueIdsOrThrow(Request $request, string $keyName, string $exceptionMessage): array
    {
        if(!$request->request->has($keyName))
            throw new UnprocessableEntityHttpException($exceptionMessage);

        $itemIds = $request->request->get($keyName);

        if(!is_array($itemIds)) $itemIds = [ $itemIds ];

        $itemIds = array_map(fn($i) => (int)$i, $itemIds);
        $itemIds = array_filter($itemIds, fn($i) => $i > 0);
        $itemIds = array_unique($itemIds);

        if(count($itemIds) < 1)
            throw new UnprocessableEntityHttpException($exceptionMessage);

        return $itemIds;
    }
}