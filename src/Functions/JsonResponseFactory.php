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

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

final class JsonResponseFactory
{
    /**
     * Creates a JsonResponse with proper UTF-8 handling.
     * 
     * @param mixed $data The data to serialize
     * @param array<string> $groups Serialization groups to use
     * @param int $status HTTP status code
     * @param array<string, string> $headers HTTP headers
     */
    public static function create(
        SerializerInterface $serializer,
        mixed $data,
        array $groups = [],
        int $status = JsonResponse::HTTP_OK,
        array $headers = []
    ): JsonResponse {
        $json = $serializer->serialize($data, 'json', [
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE,
            'groups' => $groups
        ]);

        return new JsonResponse($json, $status, $headers, true);
    }
} 