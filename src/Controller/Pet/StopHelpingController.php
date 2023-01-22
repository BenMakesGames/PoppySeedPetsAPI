<?php
namespace App\Controller\Pet;

use App\Controller\PoppySeedPetsController;
use App\Entity\Pet;
use App\Service\PetAssistantService;
use App\Service\ResponseService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/pet")
 */
class StopHelpingController extends PoppySeedPetsController
{
    /**
     * @Route("/{pet}/stopHelping", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function stopHelping(Pet $pet, PetAssistantService $petAssistantService, ResponseService $responseService)
    {
        $user = $this->getUser();
        $petAssistantService->stopAssisting($user, $pet);

        return $responseService->success();
    }
}
