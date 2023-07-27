<?php
namespace App\Controller\Style;

use App\Entity\User;
use App\Entity\UserStyle;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Repository\UserStyleRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
            throw new PSPNotFoundException('That theme could not be found.');

        if($theme->getName() === UserStyle::CURRENT)
            throw new PSPInvalidOperationException('That theme cannot be renamed!');

        $name = trim($request->request->get('name'));

        if(strlen($name) < 1 || strlen($name) > 15)
            throw new PSPFormValidationException('Name must be between 1 and 15 characters.');

        $existingTheme = $userStyleRepository->findOneBy([
            'user' => $user,
            'name' => $name
        ]);

        if($existingTheme && $existingTheme->getId() !== $theme->getId())
            throw new PSPFormValidationException('You already have a theme named "' . $name . '".');

        $theme->setName($name);

        $em->flush();

        return $responseService->success([
            'name' => $name
        ]);
    }
}
