<?php
declare(strict_types=1);

namespace App\Controller\Style;

use App\Entity\User;
use App\Entity\UserStyle;
use App\Exceptions\PSPNotFoundException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/style")]
class DeleteController extends AbstractController
{
    #[Route("/{theme}", methods: ["DELETE"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function deleteTheme(
        UserStyle $theme, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($theme->getUser()->getId() !== $user->getId())
            throw new PSPNotFoundException('That theme could not be found.');

        $em->remove($theme);
        $em->flush();

        return $responseService->success();
    }
}
