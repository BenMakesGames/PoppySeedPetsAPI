<?php
namespace App\Controller\Style;

use App\Entity\User;
use App\Entity\UserStyle;
use App\Repository\UserStyleRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
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
        UserStyleRepository $userStyleRepository, ResponseService $responseService,
        Request $request, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();
        $name = trim($request->request->get('name'));

        if(strlen($name) < 1 || strlen($name) > 15)
            throw new UnprocessableEntityHttpException('Name must be between 1 and 15 characters.');

        $current = $userStyleRepository->findCurrent($user);

        if(!$current)
            throw new UnprocessableEntityHttpException('You have to save your current theme, first.');

        $theme = $userStyleRepository->findOneBy([ 'user' => $user, 'name' => $name ]);

        if(!$theme)
        {
            $numberOfThemes = $userStyleRepository->countThemesByUser($user);

            if($numberOfThemes === 10)
                throw new UnprocessableEntityHttpException('You already have 10 themes! Sorry...');

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
