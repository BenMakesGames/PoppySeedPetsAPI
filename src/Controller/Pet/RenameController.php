<?php
namespace App\Controller\Pet;

use App\Entity\Pet;
use App\Enum\MeritEnum;
use App\Functions\PetRenamingHelpers;
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
class RenameController extends AbstractController
{
    /**
     * @Route("/{pet}/rename", methods={"PATCH"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function setPetFertility(
        Pet $pet, Request $request, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new AccessDeniedHttpException($pet->getName() . ' is not your pet.');

        if($pet->getRenamingCharges() <= 0)
            throw new AccessDeniedHttpException($pet->getName() . ' does not have any Renaming Charges.');

        PetRenamingHelpers::renamePet($responseService, $pet, $request->request->get('name', ''));

        $pet->setRenamingCharges($pet->getRenamingCharges() - 1);

        $em->flush();

        return $responseService->success([ 'name' => $pet->getName() ]);
    }
}
