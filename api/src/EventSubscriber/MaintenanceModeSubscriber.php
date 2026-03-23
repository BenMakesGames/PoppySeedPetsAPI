<?php
namespace App\EventSubscriber;

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;

class MaintenanceModeSubscriber implements EventSubscriberInterface
{
    public function onKernelRequest(RequestEvent $event): void
    {
        if (isset($_SERVER['APP_MAINTENANCE']) && $_SERVER['APP_MAINTENANCE']) {
            $response = new JsonResponse([
                'success' => false,
                'errors' => ["Poppy Seed Pets is getting a little software upgrade. It'll be back in just a feeewwww minutes!"]
            ]);

            $event->setResponse($response);
            $event->stopPropagation();
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // High priority (after CORS listener at 250) to short-circuit before heavy processing
            KernelEvents::REQUEST => ['onKernelRequest', 249],
        ];
    }
}
