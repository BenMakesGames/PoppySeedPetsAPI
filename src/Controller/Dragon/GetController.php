<?php
namespace App\Controller\Dragon;

use App\Entity\User;
use App\Exceptions\PSPNotFoundException;
use App\Functions\DragonHelpers;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route("/dragon")]
class GetController extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     */
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getDragon(
        ResponseService $responseService, EntityManagerInterface $em,
        NormalizerInterface $normalizer
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $dragon = DragonHelpers::getAdultDragon($em, $user);

        if(!$dragon)
            throw new PSPNotFoundException('You don\'t have an adult dragon!');

        return $responseService->success(DragonHelpers::createDragonResponse($em, $normalizer, $user, $dragon));
    }
}
