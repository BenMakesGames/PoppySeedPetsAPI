<?php
namespace App\Controller;

use App\Annotations\DoesNotRequireHouseHours;
use App\Entity\PushSubscription;
use App\Enum\SerializationGroupEnum;
use App\Repository\PushSubscriptionRepository;
use App\Repository\ReminderRepository;
use App\Repository\UserNotificationPreferencesRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/notification")
 */
class NotificationController extends AbstractController
{
    /**
     * @DoesNotRequireHouseHours()
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getNotificationSettings(
        ResponseService $responseService, UserNotificationPreferencesRepository $userNotificationPreferencesRepository,
        PushSubscriptionRepository $pushSubscriptionRepository, ReminderRepository $reminderRepository
    )
    {
        $user = $this->getUser();

        $preferences = $userNotificationPreferencesRepository->findOneBy([
            'user' => $user->getId()
        ]);

        $subscriptions = $pushSubscriptionRepository->findBy([
            'user' => $user->getId(),
        ]);

        $reminders = $reminderRepository->findBy([
            'user' => $user->getId(),
        ]);

        return $responseService->success(
            [
                'preferences' => $preferences,
                'reminders' => $reminders,
                'pushSubscriptions' => $subscriptions
            ],
            [
                SerializationGroupEnum::NOTIFICATION_PREFERENCES, SerializationGroupEnum::REMINDER
            ]
        );
    }

    /**
     * @DoesNotRequireHouseHours()
     * @Route("/pushSubscription", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function addPushSubscription(
        Request $request, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        if(!$request->request->has('endpoint') || !$request->request->has('keys'))
            throw new UnprocessableEntityHttpException('Push endpoint and keys are required.');

        $endpoint = $request->request->get('endpoint');

        if(!filter_var($endpoint, FILTER_VALIDATE_URL))
            throw new UnprocessableEntityHttpException('Push endpoint must be a URL.');

        $keys = $request->request->get('keys');

        if(!is_array($keys) || !isset($keys['p256dh']) || !isset($keys['auth']))
            throw new UnprocessableEntityHttpException('Push keys must contain p256dh and auth keys.');

        $p256dh = $keys['p256dh'];
        $auth = $keys['auth'];

        $subscription = (new PushSubscription())
            ->setUser($this->getUser())
            ->setEndpoint($endpoint)
            ->setP256dh($p256dh)
            ->setAuth($auth)
        ;

        $em->persist($subscription);
        $em->flush();

        return $responseService->success();
    }

    /**
     * @DoesNotRequireHouseHours()
     * @Route("/pushSubscription/{subscription}/delete", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function deletePushSubscription(
        ResponseService $responseService, PushSubscription $subscription, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if($subscription->getUser()->getId() !== $user->getId())
            throw new NotFoundHttpException('That subscription does not exist.');

        $em->remove($subscription);
        $em->flush();

        return $responseService->success();
    }
}
