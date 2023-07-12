<?php
namespace App\Controller\Pet;

use App\Entity\Pet;
use App\Entity\User;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\ProfanityFilterFunctions;
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
class CostumeController extends AbstractController
{
    /**
     * @Route("/{pet}/costume", methods={"PATCH"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function setCostume(
        Pet $pet, Request $request, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        $costume = trim($request->request->get('costume'));

        if(\mb_strlen($costume) > 30)
            throw new PSPFormValidationException('Costume description cannot be longer than 30 characters.');

        $costume = ProfanityFilterFunctions::filter($costume);

        if(\mb_strlen($costume) > 30)
            $costume = \mb_substr($costume, 0, 30);

        $pet->setCostume($costume);

        $em->flush();

        return $responseService->success();
    }
}
