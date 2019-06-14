<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;

class ResponseService
{
    private $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function success($data, $groups): JsonResponse
    {
        if(!is_array($groups)) $groups = [ $groups ];

        $json = [
            'success' => true,
            'data' => $this->serializer->serialize($data, 'json', [
                'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
                'groups' => $groups,
            ])
        ];

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    public function error($messages, $httpResponse): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'errors' => $messages,
        ], $httpResponse);
    }
}