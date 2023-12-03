<?php
namespace App\Controller\Pet;

use App\Entity\Pet;
use App\Entity\User;
use App\Service\PetAssistantService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/pet")]
class StopHelpingController extends AbstractController
{
    #[Route("/{pet}/stopHelping", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function stopHelping(Pet $pet, PetAssistantService $petAssistantService, ResponseService $responseService)
    {
        /** @var User $user */
        $user = $this->getUser();

        $petAssistantService->stopAssisting($user, $pet);

        return $responseService->success();
    }
}
