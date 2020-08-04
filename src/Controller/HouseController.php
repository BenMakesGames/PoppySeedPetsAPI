<?php
namespace App\Controller;

use App\Annotations\DoesNotRequireHouseHours;
use App\Service\HouseService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/house")
 */
class HouseController extends PoppySeedPetsController
{
    /**
     * @DoesNotRequireHouseHours()
     * @Route("/runHours", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function runHours(ResponseService $responseService, HouseService $houseService, EntityManagerInterface $em)
    {
        $user = $this->getUser();

        $houseService->run($user);
        $em->flush();

        return $responseService->success();
    }
}
