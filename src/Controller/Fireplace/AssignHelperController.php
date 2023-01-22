<?php
namespace App\Controller\Fireplace;

use App\Controller\PoppySeedPetsController;
use App\Entity\Pet;
use App\Enum\SerializationGroupEnum;
use App\Service\PetAssistantService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/fireplace")
 */
class AssignHelperController extends PoppySeedPetsController
{
    /**
     * @Route("/assignHelper/{pet}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function assignHelper(
        Pet $pet, ResponseService $responseService, EntityManagerInterface $em,
        PetAssistantService $petAssistantService
    )
    {
        $user = $this->getUser();

        $petAssistantService->helpFireplace($user, $pet);

        $em->flush();

        return $responseService->success($user->getFireplace(), [
            SerializationGroupEnum::MY_FIREPLACE,
            SerializationGroupEnum::HELPER_PET
        ]);
    }
}
