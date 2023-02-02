<?php
namespace App\Controller\Dragon;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Repository\DragonRepository;
use App\Service\PetAssistantService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dragon")
 */
class AssignHelperController extends AbstractController
{
    /**
     * @Route("/assignHelper/{pet}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function assignHelper(
        Pet $pet, ResponseService $responseService, EntityManagerInterface $em,
        PetAssistantService $petAssistantService, DragonRepository $dragonRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $petAssistantService->helpDragon($user, $pet);

        $em->flush();

        $dragon = $dragonRepository->findAdult($user);

        return $responseService->success($dragon, [
            SerializationGroupEnum::MY_DRAGON,
            SerializationGroupEnum::HELPER_PET
        ]);
    }
}
