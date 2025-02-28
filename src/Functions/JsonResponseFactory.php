<?php
declare(strict_types=1);

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