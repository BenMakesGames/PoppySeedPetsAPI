<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\User;
use App\Enum\SerializationGroupEnum;
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
    private $reloadInventory = false;
    private $reloadPets = false;
    private $em;
    private $serializer;
    private $security;
    private $normalizer;
    private $sessionId = 0;
    private $calendarService;

    public function __construct(
        SerializerInterface $serializer, NormalizerInterface $normalizer, EntityManagerInterface $em, Security $security,
        CalendarService $calendarService
    )
    {
        $this->serializer = $serializer;
        $this->normalizer = $normalizer;
        $this->em = $em;
        $this->security = $security;
        $this->calendarService = $calendarService;
    }

    public function setSessionId(?string $sessionId)
    {
        $this->sessionId = $sessionId;
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
    public function success($data = null, $groups = []): JsonResponse
    {
        if(!\is_array($groups)) $groups = [ $groups ];

        if($this->security->getUser() && $this->security->getUser()->getIsAdmin())
            $groups[] = SerializationGroupEnum::QUERY_ADMIN;

        $responseData = [
            'success' => true,
        ];

        if($data !== null)
            $responseData['data'] = $this->normalizer->normalize($data, null, [ 'groups' => $groups ]);

        if(count($this->activityLogs) > 0)
            $responseData['activity'] = $this->normalizer->normalize($this->activityLogs, null, [ 'groups' => [ SerializationGroupEnum::PET_ACTIVITY_LOGS ] ]);

        if($this->sessionId !== 0)
            $responseData['sessionId'] = $this->sessionId;

        $responseData['event'] = $this->calendarService->getEventData($this->security->getUser());

        if($this->security->getUser() && $this->security->getUser()->getIsAdmin())
            $responseData['serializationGroups'] = $groups;

        $this->injectUserData($responseData);

        if($this->reloadInventory) $responseData['reloadInventory'] = true;
        if($this->reloadPets) $responseData['reloadPets'] = true;

        $json = $this->serializer->serialize($responseData, 'json', [
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
        ]);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    public function error(int $httpResponse, array $messages): JsonResponse
    {
        $responseData = [
            'success' => false,
            'errors' => $messages,
        ];

        $groups = [];

        $this->injectUserData($responseData);

        $json = $this->serializer->serialize($responseData, 'json', [
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
            'groups' => $groups,
        ]);

        return new JsonResponse($json, $httpResponse, [], true);
    }

    private function injectUserData(array &$responseData)
    {
        $user = $this->security->getUser();

        if(!$user)
            $responseData['user'] = null;
        else
            $responseData['user'] = $this->normalizer->normalize($user, null, [ 'groups' => [ SerializationGroupEnum::MY_ACCOUNT ] ]);
    }

    public function createActivityLog(Pet $pet, string $entry, string $icon, ?PetChangesSummary $changes = null): PetActivityLog
    {
        $log = (new PetActivityLog())
            ->setPet($pet)
            ->setEntry($entry)
            ->setChanges($changes)
            ->setIcon($icon)
        ;

        $this->activityLogs[] = $log;

        $this->em->persist($log);

        return $log;
    }

    public function addReloadPets()
    {
        $this->reloadPets = true;
    }

    public function addReloadInventory()
    {
        $this->reloadInventory = true;
    }

    public function addActivityLog(PetActivityLog $log)
    {
        $this->activityLogs[] = $log;
    }
}
