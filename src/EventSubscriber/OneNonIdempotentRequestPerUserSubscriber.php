<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\EventSubscriber;

use App\Exceptions\PSPTooManyRequests;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class OneNonIdempotentRequestPerUserSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'startRequest',
            KernelEvents::TERMINATE => 'terminateResponse',
            KernelEvents::EXCEPTION => 'exception',
        ];
    }

    private $user;

    public function __construct(
        private readonly Security $security,
            private readonly CacheItemPoolInterface $cache
    )
    {

        $this->security = $security;
        $this->cache    = $cache;
    }

    public function startRequest(ControllerEvent $event)
    {
        if(!$event->getRequest()->isMethodIdempotent())
            return;

        $this->user = $this->security->getUser();

        if(!$this->user)
            return;

        $item = $this->cache->getItem('One POST #' . $this->user->getId());

        if($item->isHit())
        {
            $this->user = null;
            throw new PSPTooManyRequests();
        }

        $item
            ->set((new \DateTimeImmutable())->format('Y-m-d H:i:s'))
            ->expiresAfter((int)ini_get('max_execution_time'))
        ;
    }

    public function terminateResponse(TerminateEvent $event)
    {
        $this->done();
    }

    public function exception(ExceptionEvent $event) { $this->done(); }

    private function done()
    {
        if(!$this->user)
            return;

        $this->cache->deleteItem('One POST #' . $this->user->getId());
    }
}