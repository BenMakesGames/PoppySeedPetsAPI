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

    public function __construct(
        SerializerInterface $serializer, EntityManagerInterface $em, Security $security
    )
    {
        $this->serializer = $serializer;
        $this->em = $em;
        $this->security = $security;
    }

    public function itemActionSuccess($markdown, $useActions = []): JsonResponse
    {
        return $this->success([ 'text' => $markdown, 'useActions' => $useActions ], []);
    }

    /**
     * @param string|string[] $groups
     */
    public function success($data = null, $groups = [], ?User $user = null): JsonResponse
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

        $this->injectUserData($responseData, $groups, $user);

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

        $this->injectUserData($responseData, $groups, $user);

        $json = $this->serializer->serialize($responseData, 'json', [
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
            'groups' => $groups,
        ]);

        return new JsonResponse($json, $httpResponse, [], true);
    }

    private function injectUserData(array &$responseData, array &$groups, ?User $user)
    {

        if(!$user)
        {
            $user = $this->security->getUser();

            if(!$user)
                $responseData['user'] = null;
        }

        if($user)
        {
            $responseData['user'] = $user;
            $groups[] = SerializationGroup::MY_ACCOUNT;

            if($user->hasRole('ROLE_ADMIN'))
                $groups[] = SerializationGroup::ADMIN;
        }
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