<?php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;

class MaintenanceModeSubscriber implements EventSubscriberInterface
{
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if ($request->getPathInfo() === '/heartbeat') {
            return;
        }
        if (isset($_SERVER['APP_MAINTENANCE']) && $_SERVER['APP_MAINTENANCE']) {
            $response = new JsonResponse([
                'success' => false,
                'errors' => ["Poppy Seed Pets is getting a little software upgrade. It'll be back in just a feeewwww minutes!"]
            ]);

            $event->setResponse($response);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            // High priority (after CORS listener at 250) to short-circuit before heavy processing
            KernelEvents::REQUEST => ['onKernelRequest', 249],
        ];
    }
}
