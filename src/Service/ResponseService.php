<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\User;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\SerializationGroupEnum;
use App\Model\PetChangesSummary;
use App\Model\WeatherData;
use App\Model\WeatherForecastData;
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
    private $em;
    private $serializer;
    private $security;
    private $normalizer;
    private $sessionId = 0;
    private $calendarService;
    private $petActivityLogRepository;
    private $weatherService;

    public function __construct(
        SerializerInterface $serializer, NormalizerInterface $normalizer, EntityManagerInterface $em, Security $security,
        CalendarService $calendarService, PetActivityLogRepository $petActivityLogRepository, WeatherService $weatherService
    )
    {
        $this->serializer = $serializer;
        $this->normalizer = $normalizer;
        $this->em = $em;
        $this->security = $security;
        $this->calendarService = $calendarService;
        $this->petActivityLogRepository = $petActivityLogRepository;
        $this->weatherService = $weatherService;
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

        $responseData['event'] = $this->calendarService->getEventData($user);

        if($user && $user->getIsAdmin())
            $responseData['serializationGroups'] = $groups;

        $this->injectUserData($responseData);

        if($this->reloadInventory) $responseData['reloadInventory'] = true;
        if($this->reloadPets) $responseData['reloadPets'] = true;

        $json = $this->serializer->serialize($responseData, 'json', [
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
        ]);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    /**
     * @return PetActivityLog[]
     */
    private function getUnreadMessages(?User $user): array
    {
        if($user === null)
            return $this->flashMessages;

        $unreadMessages = $this->petActivityLogRepository->findUnreadForUser($user);

        foreach($unreadMessages as $message)
            $message->setViewed();

        $this->em->flush();

        return array_merge(
            $this->flashMessages,
            $unreadMessages
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
        $user = $this->getUser();

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

        $this->em->persist($log);

        return $log;
    }

    public function setReloadPets($reload = true)
    {
        $this->reloadPets = $reload;
        return $this;
    }

    public function getReloadPets(): bool
    {
        return $this->reloadPets;
    }

    public function setReloadInventory($reload = true)
    {
        $this->reloadInventory = $reload;
        return $this;
    }

    public function getReloadInventory(): bool
    {
        return $this->reloadInventory;
    }

    public function addFlashMessage(string $message)
    {
        $log = (new PetActivityLog())
            ->setEntry($message)
            ->addInterestingness(PetActivityLogInterestingnessEnum::PLAYER_ACTION_RESPONSE)
        ;

        $this->flashMessages[] = $log;
    }
}
