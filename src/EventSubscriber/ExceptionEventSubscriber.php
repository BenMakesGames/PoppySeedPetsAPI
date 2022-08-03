<?php
namespace App\EventSubscriber;

use App\Functions\StringFunctions;
use App\Service\ResponseService;
use Doctrine\ORM\EntityNotFoundException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

class ExceptionEventSubscriber implements EventSubscriberInterface
{
    private $logger;
    private $responseService;
    private $kernel;

    public function __construct(ResponseService $responseService, KernelInterface $kernel, LoggerInterface $logger)
    {
        $this->responseService = $responseService;
        $this->kernel = $kernel;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException'
        ];
    }

    private function getGenericErrorCodeString(HttpException $exception)
    {
        $errorString = (string)$exception->getStatusCode();
        $lastChar = substr($errorString, -1);

        return 'Generic ' . $errorString . str_repeat($lastChar, mt_rand(6, 10)) . '!!1!';
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $e = $event->getThrowable();

        if($e instanceof HttpException)
        {
            if($e->getStatusCode() === 470)
            {
                $event->setResponse($this->responseService->error(470, [ 'House hours must be run before you can continue playing.' ]));
            }
            else if($e->getStatusCode() === 429)
            {
                $event->setResponse($this->responseService->error(429, [ "You've made an awful lot of requests recently! Too many to be human! (The game thinks you're a bot; if you're not a bot, please let Ben know! https://docs.google.com/forms/d/e/1FAIpQLSczeBLNsktkSBbPZjyooHw5sEVJOBimJDS6xgEgIgFJvgqM8A/viewform?usp=sf_link )" ]));
            }
            else if($e->getStatusCode() === 404)
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
            else
            {
                $message = 'Hrm: something\'s gone awry. Reload and try again; if the problem persists, let Ben know, so he can fix it!';

                $event->setResponse($this->responseService->error(
                    $e->getStatusCode(),
                    [ $message ]
                ));
            }
        }
        else if($this->kernel->getEnvironment() !== 'dev')
        {
            $event->setResponse($this->responseService->error(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [ 'A nasty error occurred! Don\'t worry: Ben has been e-mailed... he\'ll get it sorted. In the meanwhile, just try reloading and trying again!' ]
            ));

            $throwable = $event->getThrowable();

            $this->logger->critical($throwable->getMessage(), [ 'trace' => $throwable->getTraceAsString() ]);
        }
    }
}
