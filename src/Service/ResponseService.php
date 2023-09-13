<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\User;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\SerializationGroupEnum;
use App\Functions\ArrayFunctions;
use App\Model\PetChangesSummary;
use App\Repository\PetActivityLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ResponseService
{
    /** @var PetActivityLog[] */
    private $flashMessages = [];
    private $reloadInventory = false;
    private $reloadPets = false;
    private EntityManagerInterface $em;
    private SerializerInterface $serializer;
    private Security $security;
    private NormalizerInterface $normalizer;
    private $sessionId = 0;
    private PetActivityLogRepository $petActivityLogRepository;
    private WeatherService $weatherService;
    private UserMenuService $userMenuService;
    private PerformanceProfiler $performanceProfiler;

    public function __construct(
        SerializerInterface $serializer, NormalizerInterface $normalizer, EntityManagerInterface $em, Security $security,
        PetActivityLogRepository $petActivityLogRepository, WeatherService $weatherService,
        UserMenuService $userMenuService, PerformanceProfiler $performanceProfiler
    )
    {
        $this->serializer = $serializer;
        $this->normalizer = $normalizer;
        $this->em = $em;
        $this->security = $security;
        $this->petActivityLogRepository = $petActivityLogRepository;
        $this->weatherService = $weatherService;
        $this->userMenuService = $userMenuService;
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

        /** @var User $user */
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

        if($this->sessionId !== 0)
            $responseData['sessionId'] = $this->sessionId;

        $weather = $this->weatherService->getWeather(new \DateTimeImmutable(), null);

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

        $this->performanceProfiler->logExecutionTime(__METHOD__, microtime(true) - $time);

        return $response;
    }

    /**
     * @return PetActivityLog[]
     */
    private function getUnreadMessages(?User $user): array
    {
        if($user === null)
            return $this->flashMessages;

        $unreadMessages = $this->petActivityLogRepository->findUnreadForUser($user);

        // for whatever reason, doing this results in fewer serialization deadlocks
        // compared to foreach($unreadMessages as $message) $message->setViewed();
        $query = $this->em->createQuery('
            UPDATE App\\Entity\\PetActivityLog l
            SET l.viewed = 1
            WHERE l.id IN (:messageIds)
        ');

        $messageIds = array_map(
            fn(PetActivityLog $l) => $l->getId(),
            $unreadMessages
        );

        $query->setParameter('messageIds', $messageIds);

        $query->execute();

        $this->em->flush();

        return ArrayFunctions::unique(
            array_merge($this->flashMessages, $unreadMessages),
            fn(PetActivityLog $l) => $l->getEntry(),
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

    private function injectUserData(array &$responseData)
    {
        /** @var User $user */
        $user = $this->getUser();

        if($user)
        {
            $responseData['user'] = $this->normalizer->normalize($user, null, [ 'groups' => [ SerializationGroupEnum::MY_ACCOUNT ] ]);
            $responseData['user']['menu'] = $this->normalizer->normalize($this->userMenuService->getUserMenuItems($user), null, [ 'groups' => [ SerializationGroupEnum::MY_MENU ] ]);
        }
    }

    public function createActivityLog(Pet $pet, string $entry, string $icon, ?PetChangesSummary $changes = null): PetActivityLog
    {
        $log = (new PetActivityLog())
            ->setPet($pet)
            ->setEntry($entry)
            ->setChanges($changes)
            ->setIcon($icon)
        ;

        $this->em->persist($log);

        return $log;
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
        $log = (new PetActivityLog())
            ->setEntry($message)
            ->addInterestingness(PetActivityLogInterestingnessEnum::PLAYER_ACTION_RESPONSE)
        ;

        $this->flashMessages[] = $log;

        return $this;
    }
}
