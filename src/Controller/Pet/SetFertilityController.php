<?php
namespace App\Controller\Pet;

use App\Entity\Pet;
use App\Enum\MeritEnum;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/pet")
 */
class SetFertilityController extends AbstractController
{
    /**
     * @Route("/{pet}/setFertility", methods={"PATCH"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function setPetFertility(
        Pet $pet, Request $request, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new AccessDeniedHttpException($pet->getName() . ' is not your pet.');

        if(!$pet->hasMerit(MeritEnum::VOLAGAMY))
            throw new AccessDeniedHttpException($pet->getName() . ' does not have the ' . MeritEnum::VOLAGAMY . ' Merit.');

        $fertility = $request->request->getBoolean('fertility');

        $pet->setIsFertile($fertility);

        $em->flush();

        return $responseService->success();
    }
}
