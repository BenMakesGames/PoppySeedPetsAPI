<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\User;
use App\Enum\SerializationGroup;
use App\Model\PetChangesSummary;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

class ResponseService
{
    /** @var PetActivityLog[] */
    private $activityLogs = [];
    private $em;
    private $serializer;
    private $security;

    public function __construct(SerializerInterface $serializer, EntityManagerInterface $em)
    {
        $this->serializer = $serializer;
        $this->em = $em;
    }

    /**
     * @param string|string[] $groups
     */
    public function success($data, ?User $user, $groups): JsonResponse
    {
        if(!\is_array($groups)) $groups = [ $groups ];

        $responseData = [
            'success' => true,
        ];

        if($data)
            $responseData['data'] = $data;

        if(count($this->activityLogs) > 0)
        {
            $responseData['activity'] = $this->activityLogs;
            $groups[] = SerializationGroup::PET_ACTIVITY_LOGS;
        }

        if($user)
        {
            $responseData['user'] = $user;
            $groups[] = SerializationGroup::LOG_IN;
        }

        $json = $this->serializer->serialize($responseData, 'json', [
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
            'groups' => $groups,
        ]);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    public function error(int $httpResponse, array $messages, ?User $user = null): JsonResponse
    {
        $responseData = [
            'success' => false,
            'errors' => $messages,
        ];

        $groups = [];

        if($user)
        {
            $responseData['user'] = $user;
            $groups[] = SerializationGroup::LOG_IN;
        }

        $json = $this->serializer->serialize($responseData, 'json', [
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
            'groups' => $groups,
        ]);

        return new JsonResponse($json, $httpResponse, [], true);
    }

    public function createActivityLog(Pet $pet, string $entry, ?PetChangesSummary $changes = null)
    {
        $log = (new PetActivityLog())
            ->setPet($pet)
            ->setEntry($entry)
            ->setChanges($changes)
        ;

        $this->activityLogs[] = $log;

        $this->em->persist($log);

        return $log;
    }
}