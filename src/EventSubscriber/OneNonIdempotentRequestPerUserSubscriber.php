<?php
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

    private $security;
    private $cache;
    private $user;

    public function __construct(Security $security, CacheItemPoolInterface $cache)
    {
        $this->security = $security;
        $this->cache = $cache;
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

    public function exception(ExceptionEvent $event)
    {
        $this->done();
    }

    private function done()
    {
        if(!$this->user)
            return;

        $this->cache->deleteItem('One POST #' . $this->user->getId());
    }
}