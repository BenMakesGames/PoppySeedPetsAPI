<?php
namespace App\Controller\Dragon;

use App\Entity\Pet;
use App\Entity\User;
use App\Functions\DragonHelpers;
use App\Service\PetAssistantService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route("/dragon")]
class AssignHelperController extends AbstractController
{
    #[Route("/assignHelper/{pet}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function assignHelper(
        Pet $pet, ResponseService $responseService, EntityManagerInterface $em,
        PetAssistantService $petAssistantService, NormalizerInterface $normalizer
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $petAssistantService->helpDragon($user, $pet);

        $em->flush();

        $dragon = DragonHelpers::getAdultDragon($em, $user);

        return $responseService->success(DragonHelpers::createDragonResponse($em, $normalizer, $user, $dragon));
    }
}
