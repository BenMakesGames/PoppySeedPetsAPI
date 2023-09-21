<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\User;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\SerializationGroupEnum;
use App\Functions\ArrayFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\SimpleDb;
use App\Functions\UserMenuFunctions;
use App\Model\PetChangesSummary;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Annotation\Groups;

class ResponseService
{
    /** @var FlashMessage[] */ private $flashMessages = [];
    private bool $reloadInventory = false;
    private bool $reloadPets = false;
    private EntityManagerInterface $em;
    private SerializerInterface $serializer;
    private Security $security;
    private NormalizerInterface $normalizer;
    private ?string $sessionId = null;
    private WeatherService $weatherService;
    private PerformanceProfiler $performanceProfiler;

    public function __construct(
        SerializerInterface $serializer, NormalizerInterface $normalizer, EntityManagerInterface $em, Security $security,
        WeatherService $weatherService, PerformanceProfiler $performanceProfiler
    )
    {
        $this->serializer = $serializer;
        $this->normalizer = $normalizer;
        $this->em = $em;
        $this->security = $security;
        $this->weatherService = $weatherService;
        $this->performanceProfiler = $performanceProfiler;
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

    private function getUser(): ?User
    {
        return $this->security->getUser();
    }

    /**
     * @param string[] $groups
     */
    public function success($data = null, array $groups = []): JsonResponse
    {
        $time = microtime(true);

        $user = $this->getUser();

        if($user && $user->getIsAdmin())
            $groups[] = SerializationGroupEnum::QUERY_ADMIN;

        $responseData = [
            'success' => true,
        ];

        if($data !== null)
            $responseData['data'] = $this->normalizer->normalize($data, null, [ 'groups' => $groups ]);

        $activity = $this->getUnreadMessages($user);

        if(count($activity) > 0)
            $responseData['activity'] = $this->normalizer->normalize($activity, null, [ 'groups' => [ SerializationGroupEnum::PET_ACTIVITY_LOGS ] ]);

        if($this->sessionId !== null)
            $responseData['sessionId'] = $this->sessionId;

        $weather = WeatherService::getWeather(new \DateTimeImmutable(), null);

        $responseData['weather'] = $this->normalizer->normalize([
            'today' => $weather,
            'forecast' => $this->weatherService->get24HourForecast(),
        ], null, [ 'groups' => [ SerializationGroupEnum::WEATHER ]]);

        if($user && $user->getIsAdmin())
            $responseData['serializationGroups'] = $groups;

        $this->injectUserData($responseData);

        if($this->reloadInventory) $responseData['reloadInventory'] = true;
        if($this->reloadPets) $responseData['reloadPets'] = true;

        $json = $this->serializer->serialize($responseData, 'json', [
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
        ]);

        $response = new JsonResponse($json, Response::HTTP_OK, [], true);

        if($user)
            $this->performanceProfiler->logExecutionTime(__METHOD__ . ' - with user', microtime(true) - $time);
        else
            $this->performanceProfiler->logExecutionTime(__METHOD__ . ' - without user', microtime(true) - $time);

        return $response;
    }

    /**
     * @return FlashMessage[]
     */
    private function getUnreadMessages(?User $user): array
    {
        if($user === null)
            return $this->flashMessages;

        $unreadMessages = $this->findUnreadForUser($user);

        // for whatever reason, doing this results in fewer serialization deadlocks
        $query = $this->em->createQuery('
            DELETE FROM App\\Entity\\UnreadPetActivityLog l
            WHERE l.petActivityLog IN (:messageIds)
        ');

        $messageIds = array_map(
            fn(FlashMessage $l) => $l->id,
            $unreadMessages
        );

        $query->setParameter('messageIds', $messageIds);

        $query->execute();

        $this->em->flush();

        return ArrayFunctions::unique(
            array_merge($this->flashMessages, $unreadMessages),
            fn(FlashMessage $l) => $l->entry,
        );
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

    /**
     * @return FlashMessage[]
     */
    public function findUnreadForUser(User $user): array
    {
        $time = microtime(true);

        $logs = SimpleDb::createReadOnlyConnection()
            ->query(
                'SELECT l.id,l.entry,l.icon,l.changes,l.interestingness
                FROM unread_pet_activity_log AS ul
                INNER JOIN pet AS p ON p.id = ul.pet_id
                INNER JOIN pet_activity_log AS l ON ul.pet_activity_log_id=l.id
                WHERE p.owner_id = :userId',
                [
                    ':userId' => $user->getId()
                ]
            )
            ->mapResults(
                fn($id, $entry, $icon, $changes, $interestingness)
                    => new FlashMessage($id, $entry, $icon, unserialize($changes), $interestingness)
            );

        $this->performanceProfiler->logExecutionTime(__METHOD__, microtime(true) - $time);

        return $logs;
    }

    private function injectUserData(array &$responseData)
    {
        $user = $this->getUser();

        if($user)
        {
            $responseData['user'] = $this->normalizer->normalize($user, null, [ 'groups' => [ SerializationGroupEnum::MY_ACCOUNT ] ]);
            $responseData['user']['menu'] = $this->normalizer->normalize(UserMenuFunctions::getUserMenuItems($this->em, $user), null, [ 'groups' => [ SerializationGroupEnum::MY_MENU ] ]);
        }
    }

    /**
     * @deprecated Use PetActivityLogFactory::createLog(...), instead
     */
    public function createActivityLog(Pet $pet, string $entry, string $icon, ?PetChangesSummary $changes = null): PetActivityLog
    {
        return PetActivityLogFactory::createUnreadLog($this->em, $pet, $entry)
            ->setChanges($changes)
            ->setIcon($icon)
        ;
    }

    public function setReloadPets($reload = true): self
    {
        $this->reloadPets = $reload;
        return $this;
    }

    public function getReloadPets(): bool
    {
        return $this->reloadPets;
    }

    public function setReloadInventory($reload = true): self
    {
        $this->reloadInventory = $reload;
        return $this;
    }

    public function getReloadInventory(): bool
    {
        return $this->reloadInventory;
    }

    public function addFlashMessage(string $message): self
    {
        $this->flashMessages[] = new FlashMessage(0, $message, '', null, PetActivityLogInterestingnessEnum::PLAYER_ACTION_RESPONSE);

        return $this;
    }
}

class FlashMessage
{
    /**
     * @Groups({"petActivityLogs"})
     */
    public int $id;

    /**
     * @Groups({"petActivityLogs"})
     */
    public string $entry;

    /**
     * @Groups({"petActivityLogs"})
     */
    public string $icon;

    /**
     * @Groups({"petActivityLogs"})
     */
    public ?PetChangesSummary $changes;

    /**
     * @Groups({"petActivityLogs"})
     */
    public int $interestingness;

    public function __construct(int $id, string $entry, string $icon, ?PetChangesSummary $changes, int $interestingness)
    {
        $this->id = $id;
        $this->entry = $entry;
        $this->icon = $icon;
        $this->changes = $changes;
        $this->interestingness = $interestingness;
    }
}