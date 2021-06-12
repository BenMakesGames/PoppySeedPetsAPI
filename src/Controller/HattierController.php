<?php
namespace App\Controller;

use App\Enum\SerializationGroupEnum;
use App\Service\HattierService;
use App\Service\ResponseService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/hattier")
 */
class HattierController extends PoppySeedPetsController
{
    /**
     * @Route("/unlockedStyles", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getUnlockedAuras(HattierService $hattierService, ResponseService $responseService)
    {
        $user = $this->getUser();

        return $responseService->success(
            [
                'available' => $hattierService->getAurasAvailable($user),
            ],
            [ SerializationGroupEnum::MY_AURAS ]
        );
    }

    /**
     * @Route("/buy", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function applyAura(Request $request)
    {

    }
}