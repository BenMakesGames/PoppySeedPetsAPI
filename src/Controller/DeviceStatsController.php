<?php
declare(strict_types=1);

namespace App\Controller;

use App\Attributes\DoesNotRequireHouseHours;
use App\Entity\DeviceStats;
use App\Entity\User;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/deviceStats")]
class DeviceStatsController extends AbstractController
{
    #[DoesNotRequireHouseHours]
    #[Route("", methods: ["PUT"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function create(
        ResponseService $responseService,
        #[MapRequestPayload] DeviceStatsRequest $dto,
        EntityManagerInterface $em
    ) {
        /** @var User $user */
        $user = $this->getUser();

        if($dto->userAgent && $dto->language && $dto->windowWidth && $dto->screenWidth)
        {
            $deviceStats = (new DeviceStats())
                ->setUser($user)
                ->setUserAgent($dto->userAgent)
                ->setLanguage($dto->language)
                ->setTouchPoints($dto->touchPoints)
                ->setWindowWidth($dto->windowWidth)
                ->setScreenWidth($dto->screenWidth)
            ;

            $em->persist($deviceStats);
            $em->flush();
        }

        return $responseService->success();
    }
}

class DeviceStatsRequest
{
    public function __construct(
        public readonly string $userAgent,
        public readonly string $language,
        public readonly int $touchPoints,
        public readonly int $windowWidth,
        public readonly int $screenWidth,
    )
    {
    }
}
