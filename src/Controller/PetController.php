<?php
namespace App\Controller;

use App\Service\ResponseService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/pet")
 */
class PetController extends APIController
{
    /**
     * @Route("/my", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getMyPets(ResponseService $responseService)
    {
        return $responseService->success($this->getUser()->getPets(), 'myPets');
    }
}