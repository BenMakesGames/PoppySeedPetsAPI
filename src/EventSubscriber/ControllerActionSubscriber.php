<?php
namespace App\EventSubscriber;

use App\Annotations\DoesNotRequireHouseHours;
use App\Entity\User;
use App\Service\HouseService;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\Security;

class ControllerActionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'beforeFilter',
            KernelEvents::RESPONSE => 'finalizeResponse'
        ];
    }

    private $security;
    private $houseService;
    private $annotationReader;
    private RateLimiterFactory $defaultRateLimiterFactory;
    private RateLimiterFactory $burstRateLimiterFactory;

    public function __construct(
        Security $security, HouseService $houseService, Reader $annotationReader,
        RateLimiterFactory $pspDefaultLimiter, RateLimiterFactory $pspBurstLimiter
    )
    {
        $this->security = $security;
        $this->houseService = $houseService;
        $this->annotationReader = $annotationReader;
        $this->defaultRateLimiterFactory = $pspDefaultLimiter;
        $this->burstRateLimiterFactory = $pspBurstLimiter;
    }

    public function beforeFilter(ControllerEvent $event)
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

        $burstLimiter = $this->burstRateLimiterFactory->create($user->getId());
        $defaultLimiter = $this->defaultRateLimiterFactory->create($user->getId());

        $burstLimiter->consume(1)->wait();
        $defaultLimiter->consume(1)->wait();
    }

    private function checkHouseHours(ControllerEvent $event)
    {
        $controllerAction = $event->getController();
        $method = new \ReflectionMethod(get_class($controllerAction[0]), $controllerAction[1]);
        $doesNotRequireHouseHours = $this->annotationReader->getMethodAnnotation($method, DoesNotRequireHouseHours::class);

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

        throw new HttpException(470, 'House hours must be run.');
    }

    private function convertJsonStringToArray(ControllerEvent $event)
    {
        $request = $event->getRequest();

        if ($request->getContentType() != 'json' || !$request->getContent())
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
