<?php
namespace App\EventSubscriber;

use App\Functions\StringFunctions;
use App\Service\ResponseService;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

class ExceptionEventSubscriber implements EventSubscriberInterface
{
    private $responseService;
    private $kernel;

    public function __construct(ResponseService $responseService, KernelInterface $kernel)
    {
        $this->responseService = $responseService;
        $this->kernel = $kernel;
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
            if($e->getMessage() && strpos($e->getMessage(), 'App\\Entity\\Inventory') !== false)
                $message = 'That item doesn\'t exist... weird. Maybe it got used up? Reload and try again.';
            else
                $message = $e->getMessage() ? $e->getMessage() : 'Generic 4044444444444444!!1!';

            $event->setResponse($this->responseService->error(
                Response::HTTP_NOT_FOUND,
                [ $message ]
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
        else if($this->kernel->getEnvironment() !== 'dev')
        {
            $event->setResponse($this->responseService->error(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [ $e->getMessage() ]
            ));
        }
    }
}