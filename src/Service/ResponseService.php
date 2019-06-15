<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class ResponseService
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param string|string[] $groups
     */
    public function success($data, $groups): JsonResponse
    {
        if(!\is_array($groups)) $groups = [ $groups ];

        $responseData = [
            'success' => true,
            'data' => $data
        ];

        $json = $this->serializer->serialize($responseData, 'json', [
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
            'groups' => $groups,
        ]);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    public function error(array $messages, int $httpResponse): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'errors' => $messages,
        ], $httpResponse);
    }
}