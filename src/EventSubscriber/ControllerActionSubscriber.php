<?php
namespace App\EventSubscriber;

use App\Entity\PetActivityLog;
use App\Enum\SerializationGroup;
use App\Service\HouseService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

class ControllerActionSubscriber implements EventSubscriberInterface
{
    private $houseService;
    private $serializer;

    public function __construct(HouseService $houseService, SerializerInterface $serializer)
    {
        $this->houseService = $houseService;
        $this->serializer = $serializer;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'convertJsonStringToArray',
            KernelEvents::RESPONSE => 'finalizeResponse'
        ];
    }

    public function convertJsonStringToArray(ControllerEvent $event)
    {
        $request = $event->getRequest();
        if ($request->getContentType() != 'json' || !$request->getContent()) {
            return;
        }
        $data = \json_decode($request->getContent(), true);
        if (\json_last_error() !== JSON_ERROR_NONE) {
            throw new BadRequestHttpException('Invalid JSON body: ' . \json_last_error_msg());
        }
        $request->request->replace(is_array($data) ? $data : array());
    }

    public function finalizeResponse(ResponseEvent $event)
    {
        $event->getResponse()->headers->set('X-Powered-By', 'PSYC-101');

        if(\count($this->houseService->activityLogs) > 0)
        {
            $content = \json_decode($event->getResponse()->getContent(), true);

            if(array_key_exists('activity', $content))
                $content['activity'] = array_merge($content['activity'], $this->houseService->activityLogs);
            else
                $content['activity'] = $this->houseService->activityLogs;

            $event->getResponse()->setContent($this->serializer->serialize($content, 'json', [ 'groups' => [ SerializationGroup::PET_ACTIVITY_LOGS ] ]));
        }
    }
}