<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use App\Attributes\DoesNotRequireHouseHours;
use App\Entity\User;
use App\Exceptions\PSPHoursMustBeRun;
use App\Service\HouseService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class ControllerActionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'beforeFilter',
            KernelEvents::RESPONSE => 'finalizeResponse'
        ];
    }

    public function __construct(
        private readonly Security $security,
        private readonly HouseService $houseService,
        private readonly RateLimiterFactory $pspDefaultLimiter
    )
    {
    }

    public function beforeFilter(ControllerEvent $event): void
    {
        if(is_array($event->getController()))
        {
            $this->checkRateLimiters($event);
            $this->checkHouseHours($event);
        }

        $this->convertJsonStringToArray($event);
    }

    private function checkRateLimiters(ControllerEvent $event)
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if(!$user)
            return;

        $defaultLimiter = $this->pspDefaultLimiter->create((string)$user->getId());

        $defaultLimiter->reserve(1, 15)->wait();
    }

    private function checkHouseHours(ControllerEvent $event)
    {
        $controllerAction = $event->getController();
        $method = new \ReflectionMethod(get_class($controllerAction[0]), $controllerAction[1]);
        $doesNotRequireHouseHours = $method->getAttributes(DoesNotRequireHouseHours::class);

        if($doesNotRequireHouseHours)
            return;

        /** @var User $user */
        $user = $this->security->getUser();

        if(!$user)
            return;

        $item = $this->houseService->getHouseRunLock($user);

        if($item->isHit())
            return;

        if(!$this->houseService->needsToBeRun($user))
            return;

        throw new PSPHoursMustBeRun();
    }

    private function convertJsonStringToArray(ControllerEvent $event)
    {
        $request = $event->getRequest();

        if ($request->getContentTypeFormat() != 'json' || !$request->getContent())
            return;

        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE)
            throw new BadRequestHttpException('Invalid JSON body: ' . json_last_error_msg());

        $request->request->replace(is_array($data) ? $data : array());
    }

    public function finalizeResponse(ResponseEvent $event)
    {
        $event->getResponse()->headers->set('X-Powered-By', 'PSYC-101');
    }
}
