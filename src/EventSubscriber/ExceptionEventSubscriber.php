<?php
namespace App\EventSubscriber;

use App\Service\ResponseService;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionEventSubscriber implements EventSubscriberInterface
{
    private $responseService;

    public function __construct(ResponseService $responseService)
    {
        $this->responseService = $responseService;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException'
        ];
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $e = $event->getException();

        if($e instanceof HttpException)
        {
            $event->setResponse($this->responseService->error(
                $e->getStatusCode(),
                [ $e->getMessage() ]
            ));
        }
        else if($e instanceof EntityNotFoundException)
        {
            if(strpos($e->getMessage(), 'App\\Entity\\Inventory') !== false)
                $message = 'That item doesn\'t exist... weird. Maybe it got used up? Reload and try again.';
            else if(strpos($e->getMessage(), 'App\\Entity\\Pet') !== false)
                $message = 'That pet doesn\'t exist... weird. Reload and try again?';
            else
                $message = 'The thing you were trying to interact with doesn\'t exist! That generally shouldn\'t happen... reload and try again?';

            $event->setResponse($this->responseService->error(
                Response::HTTP_NOT_FOUND,
                [ $message ]
            ));
        }
    }
}