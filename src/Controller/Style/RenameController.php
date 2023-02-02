<?php
namespace App\Controller\Style;

use App\Entity\User;
use App\Entity\UserStyle;
use App\Repository\UserStyleRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/style")
 */
class RenameController extends AbstractController
{
    /**
     * @Route("/{theme}/rename", methods={"PATCH"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function renameTheme(
        UserStyle $theme, ResponseService $responseService, EntityManagerInterface $em,
        Request $request, UserStyleRepository $userStyleRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($theme->getUser()->getId() !== $user->getId())
            throw new AccessDeniedHttpException('That\'s not your theme!');

        if($theme->getName() === UserStyle::CURRENT)
            throw new UnprocessableEntityHttpException('That theme cannot be renamed!');

        $name = trim($request->request->get('name'));

        if(strlen($name) < 1 || strlen($name) > 15)
            throw new UnprocessableEntityHttpException('Name must be between 1 and 15 characters.');

        $existingTheme = $userStyleRepository->findOneBy([
            'user' => $user,
            'name' => $name
        ]);

        if($existingTheme && $existingTheme->getId() !== $theme->getId())
            throw new UnprocessableEntityHttpException('You already have a theme named "' . $name . '".');

        $theme->setName($name);

        $em->flush();

        return $responseService->success([
            'name' => $name
        ]);
    }
}
