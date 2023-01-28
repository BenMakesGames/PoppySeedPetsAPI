<?php
namespace App\Controller\Greenhouse;

use App\Controller\PoppySeedPetsController;
use App\Entity\Pet;
use App\Enum\SerializationGroupEnum;
use App\Service\GreenhouseService;
use App\Service\PetAssistantService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/greenhouse")
 */
class AssignHelperController extends PoppySeedPetsController
{
    /**
     * @Route("/assignHelper/{pet}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function assignHelper(
        Pet $pet, ResponseService $responseService, EntityManagerInterface $em, GreenhouseService $greenhouseService
    )
    {
        $user = $this->getUser();

        PetAssistantService::helpGreenhouse($user, $pet);

        $em->flush();

        return $responseService->success(
            $greenhouseService->getGreenhouseResponseData($user),
            [ SerializationGroupEnum::GREENHOUSE_PLANT, SerializationGroupEnum::MY_GREENHOUSE, SerializationGroupEnum::HELPER_PET ]
        );
    }
}
