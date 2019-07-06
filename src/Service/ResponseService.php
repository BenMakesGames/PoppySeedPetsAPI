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
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ResponseService
{
    /** @var PetActivityLog[] */
    private $activityLogs = [];
    private $em;
    private $serializer;
    private $security;
    private $normalizer;

    public function __construct(
        SerializerInterface $serializer, NormalizerInterface $normalizer, EntityManagerInterface $em, Security $security
    )
    {
        $this->serializer = $serializer;
        $this->normalizer = $normalizer;
        $this->em = $em;
        $this->security = $security;
    }

    public function itemActionSuccess($markdown, $data = []): JsonResponse
    {
        $data = array_merge($data, [
            'text' => $markdown
        ]);

        return $this->success($data);
    }

    /**
     * @param string|string[] $groups
     */
    public function success($data = null, $groups = [], ?User $user = null): JsonResponse
    {
        if(!\is_array($groups)) $groups = [ $groups ];

        if($this->security->getUser() && $this->security->getUser()->getIsAdmin())
            $groups[] = SerializationGroup::QUERY_ADMIN;

        $responseData = [
            'success' => true,
        ];

        if($data !== null)
            $responseData['data'] = $this->normalizer->normalize($data, null, [ 'groups' => $groups ]);

        if(count($this->activityLogs) > 0)
        {
            $responseData['activity'] = $this->normalizer->normalize($this->activityLogs, null, [ 'groups' => [ SerializationGroup::PET_ACTIVITY_LOGS ] ]);
        }

        $this->injectUserData($responseData, $user);

        $json = $this->serializer->serialize($responseData, 'json', [
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
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

        $this->injectUserData($responseData, $user);

        $json = $this->serializer->serialize($responseData, 'json', [
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
            'groups' => $groups,
        ]);

        return new JsonResponse($json, $httpResponse, [], true);
    }

    private function injectUserData(array &$responseData, ?User $user)
    {
        if(!$user)
        {
            $user = $this->security->getUser();

            if(!$user)
                $responseData['user'] = null;
        }

        if($user)
            $responseData['user'] = $this->normalizer->normalize($user, null, [ 'groups' => [ SerializationGroup::MY_ACCOUNT ] ]);
    }

    public function createActivityLog(Pet $pet, string $entry, ?PetChangesSummary $changes = null): PetActivityLog
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