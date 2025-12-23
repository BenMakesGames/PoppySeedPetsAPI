<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */

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