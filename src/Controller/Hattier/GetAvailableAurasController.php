<?php
namespace App\Controller\Hattier;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Service\HattierService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route("/hattier")]
class GetAvailableAurasController extends AbstractController
{
    #[Route("/unlockedStyles", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getUnlockedAuras(HattierService $hattierService, ResponseService $responseService)
    {
        /** @var User $user */
        $user = $this->getUser();

        return $responseService->success(
            [
                'available' => $hattierService->getAurasAvailable($user),
            ],
            [ SerializationGroupEnum::MY_AURAS ]
        );
    }
}