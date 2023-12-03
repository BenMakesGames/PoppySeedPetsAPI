<?php
namespace App\Controller\Fireplace;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Service\PetAssistantService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/fireplace")]
class AssignHelperController extends AbstractController
{
    #[Route("/assignHelper/{pet}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function assignHelper(
        Pet $pet, ResponseService $responseService, EntityManagerInterface $em,
        PetAssistantService $petAssistantService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $petAssistantService->helpFireplace($user, $pet);

        $em->flush();

        return $responseService->success($user->getFireplace(), [
            SerializationGroupEnum::MY_FIREPLACE,
            SerializationGroupEnum::HELPER_PET
        ]);
    }
}
