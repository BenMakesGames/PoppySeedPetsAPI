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

use App\Exceptions\PSPAccountLocked;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPHoursMustBeRun;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotEnoughCurrencyException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPNotUnlockedException;
use App\Exceptions\PSPSessionExpired;
use App\Exceptions\PSPTooManyRequests;
use App\Service\ResponseService;
use Doctrine\ORM\OptimisticLockException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

class ExceptionEventSubscriber implements EventSubscriberInterface
{
    private const string GenericErrorMessage = 'Hrm: something\'s gone awry. Reload and try again; if the problem persists, let Ben know, so he can fix it!';

    public function __construct(
        private readonly ResponseService $responseService,
        private readonly KernelInterface $kernel,
        private readonly LoggerInterface $logger
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException'
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();

        if($e instanceof HttpException)
        {
            $message = match ($e->getStatusCode())
            {
                429 => "You've made an awful lot of requests recently! Too many to be human! (The game thinks you're a bot; if you're not a bot, please let Ben know! https://docs.google.com/forms/d/e/1FAIpQLSczeBLNsktkSBbPZjyooHw5sEVJOBimJDS6xgEgIgFJvgqM8A/viewform?usp=sf_link )",
                403, 401 => $e->getMessage(),
                404 => 'Classic 404! The thing you were trying to do or interact with couldn\'t be found! That generally shouldn\'t happen... reload and try again?',
                422 => 'One or more required fields were missing. Please reload and try again.',
                default => self::GenericErrorMessage,
            };

            $event->setResponse($this->responseService->error($e->getStatusCode(), [ $message ]));
        }
        else if($e instanceof PSPNotFoundException) // includes PSPPetNotFoundExceptions
        {
            $event->setResponse($this->responseService->error(Response::HTTP_NOT_FOUND, [ $e->getMessage() ]));
        }
        else if($e instanceof PSPHoursMustBeRun)
        {
            $event->setResponse($this->responseService->error(470, [ $e->getMessage() ]));
        }
        else if($e instanceof PSPTooManyRequests)
        {
            $event->setResponse($this->responseService->error(420, [ $e->getMessage() ]));
        }
        else if($e instanceof PSPNotUnlockedException)
        {
            $event->setResponse($this->responseService->error(Response::HTTP_FORBIDDEN, [ $e->getMessage() ]));
        }
        else if($e instanceof PSPFormValidationException || $e instanceof PSPInvalidOperationException || $e instanceof PSPNotEnoughCurrencyException)
        {
            $event->setResponse($this->responseService->error(Response::HTTP_UNPROCESSABLE_ENTITY, [ $e->getMessage() ]));
        }
        else if($e instanceof PSPSessionExpired)
        {
            $event->setResponse($this->responseService->error(Response::HTTP_UNAUTHORIZED, [ $e->getMessage() ]));
        }
        else if($e instanceof PSPAccountLocked)
        {
            // technically, this should be a Forbidden exception, because we know who the user is. BUT:
            // the client is programmed to auto log a user out when they receive a 401 (Unauthorized),
            // and there are legit reasons a user might be Forbidden that we DON'T want them to be logged
            // out for (ex: accessing the Fireplace before they unlocked it). SO: we return an
            // unauthorized, instead >_>
            $event->setResponse($this->responseService->error(Response::HTTP_UNAUTHORIZED, [ $e->getMessage() ]));
        }
        else if($e instanceof OptimisticLockException)
        {
            $event->setResponse($this->responseService->error(420, [ self::GenericErrorMessage ]));
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
