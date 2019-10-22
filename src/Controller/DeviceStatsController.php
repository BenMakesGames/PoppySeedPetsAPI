<?php
namespace App\Controller;

use App\Entity\DeviceStats;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/deviceStats")
 */
class DeviceStatsController extends PoppySeedPetsController
{
    /**
     * @Route("", methods={"PUT"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function about(ResponseService $responseService, Request $request, EntityManagerInterface $em)
    {
        $user = $this->getUser();

        $userAgent = trim($request->request->get('userAgent', ''));
        $language = trim($request->request->get('language', ''));
        $touchPoints = (int)trim($request->request->get('touchPoints', 0));
        $windowWidth = (int)trim($request->request->get('windowWidth', 0));
        $screenWidth = (int)trim($request->request->get('screenWidth', 0));

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
