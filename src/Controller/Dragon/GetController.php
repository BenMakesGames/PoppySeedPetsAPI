<?php
namespace App\Controller\Dragon;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Repository\DragonRepository;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dragon")
 */
class GetController extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getDragon(ResponseService $responseService, DragonRepository $dragonRepository)
    {
        /** @var User $user */
        $user = $this->getUser();

        $dragon = $dragonRepository->findAdult($user);

        if(!$dragon)
            throw new NotFoundHttpException('You don\'t have an adult dragon!');

        return $responseService->success($dragon, [
            SerializationGroupEnum::MY_DRAGON,
            SerializationGroupEnum::HELPER_PET,
        ]);
    }
}
