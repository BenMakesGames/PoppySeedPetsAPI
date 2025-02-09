<?php
declare(strict_types=1);

namespace App\Controller;

use App\Attributes\DoesNotRequireHouseHours;
use App\Entity\DeviceStats;
use App\Entity\User;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/deviceStats")]
class DeviceStatsController extends AbstractController
{
    #[DoesNotRequireHouseHours]
    #[Route("", methods: ["PUT"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function about(ResponseService $responseService, Request $request, EntityManagerInterface $em)
    {
        /** @var User $user */
        $user = $this->getUser();

        $userAgent = trim($request->request->getString('userAgent'));
        $language = trim($request->request->getString('language'));
        $touchPoints = $request->request->getInt('touchPoints');
        $windowWidth = $request->request->getInt('windowWidth');
        $screenWidth = $request->request->getInt('screenWidth');

        if($userAgent && $language && $windowWidth && $screenWidth)
        {
            $deviceStats = (new DeviceStats())
                ->setUser($user)
                ->setUserAgent($userAgent)
                ->setLanguage($language)
                ->setTouchPoints($touchPoints)
                ->setWindowWidth($windowWidth)
                ->setScreenWidth($screenWidth)
            ;

            $em->persist($deviceStats);
            $em->flush();
        }

        return $responseService->success();
    }
}
