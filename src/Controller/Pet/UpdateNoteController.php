<?php
namespace App\Controller\Pet;

use App\Entity\Pet;
use App\Entity\User;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/pet")
 */
class UpdateNoteController extends AbstractController
{
    /**
     * @Route("/{pet}/updateNote", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function updateNote(
        Pet $pet, Request $request, EntityManagerInterface $em, ResponseService $responseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        $note = trim($request->request->get('note', ''));

        if(\mb_strlen($note) > 1000)
            throw new PSPFormValidationException('Note cannot be longer than 1000 characters.');

        $pet->setNote($note);

        $em->flush();

        return $responseService->success();
    }
}
