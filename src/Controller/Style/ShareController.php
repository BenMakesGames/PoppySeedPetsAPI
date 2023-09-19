<?php
namespace App\Controller\Style;

use App\Entity\User;
use App\Entity\UserStyle;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Functions\UserStyleFunctions;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/style")
 */
class ShareController extends AbstractController
{
    /**
     * @Route("", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function shareTheme(
        ResponseService $responseService, Request $request, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();
        $name = trim($request->request->get('name'));

        if(strlen($name) < 1 || strlen($name) > 15)
            throw new PSPFormValidationException('Name must be between 1 and 15 characters.');

        $current = UserStyleFunctions::findCurrent($em, $user->getId());

        if(!$current)
            throw new PSPInvalidOperationException('You have to save your current theme, first.');

        $theme = $em->getRepository(UserStyle::class)->findOneBy([ 'user' => $user, 'name' => $name ]);

        if(!$theme)
        {
            $numberOfThemes = UserStyleFunctions::countThemesByUser($em, $user);

            if($numberOfThemes >= 10)
                throw new PSPInvalidOperationException('You may not have more than 10 themes! Sorry...');

            $theme = (new UserStyle())
                ->setUser($user)
                ->setName($name)
            ;

            $em->persist($theme);
        }

        foreach(UserStyle::PROPERTIES as $property)
            $theme->{'set' . $property}($current->{'get' . $property}());

        $em->flush();

        return $responseService->success();
    }
}
