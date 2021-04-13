<?php
namespace App\Controller;

use App\Entity\UserStyle;
use App\Enum\SerializationGroupEnum;
use App\Repository\UserStyleRepository;
use App\Service\Filter\UserStyleFilter;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/style")
 */
class StyleController extends PoppySeedPetsController
{
    /**
     * @Route("/following", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getThemesOfFollowedPlayers(
        Request $request, UserStyleFilter $userStyleFilter, ResponseService $responseService
    )
    {
        $user = $this->getUser();

        $userStyleFilter->setUser($user);

        $themes = $userStyleFilter->getResults($request->query);

        return $responseService->success($themes, [
            SerializationGroupEnum::FILTER_RESULTS,
            SerializationGroupEnum::PUBLIC_STYLE,
        ]);
    }

    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getThemes(UserStyleRepository $userStyleRepository, ResponseService $responseService)
    {
        $user = $this->getUser();
        $themes = $userStyleRepository->findBy([ 'user' => $user ]);

        return $responseService->success($themes, [ SerializationGroupEnum::MY_STYLE ]);
    }

    /**
     * @Route("/{theme}", methods={"DELETE"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function deleteTheme(
        UserStyle $theme, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if($theme->getUser()->getId() !== $user->getId())
            throw new AccessDeniedHttpException('That\'s not your theme!');

        $em->remove($theme);
        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/{theme}/rename", methods={"PATCH"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function renameTheme(
        UserStyle $theme, ResponseService $responseService, EntityManagerInterface $em,
        Request $request, UserStyleRepository $userStyleRepository
    )
    {
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

    /**
     * @Route("/{theme}/setCurrent", methods={"PATCH"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function setCurrent(
        UserStyle $theme, ResponseService $responseService, UserStyleRepository $userStyleRepository,
        EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if($theme->getName() === UserStyle::CURRENT)
            throw new NotFoundHttpException();

        $current = $userStyleRepository->findCurrent($user);

        if(!$current)
        {
            $current = (new UserStyle())
                ->setUser($user)
                ->setName(UserStyle::CURRENT)
            ;

            $em->persist($current);
        }

        foreach(UserStyle::PROPERTIES as $property)
            $current->{'set' . $property}($theme->{'get' . $property}());

        $em->flush();

        return $responseService->success($current, [ SerializationGroupEnum::MY_STYLE ]);
    }

    /**
     * @Route("", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function shareTheme(
        UserStyleRepository $userStyleRepository, ResponseService $responseService,
        Request $request, EntityManagerInterface $em
    )
    {
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

    /**
     * @Route("/current", methods={"PATCH"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function saveCurrentStyle(
        Request $request, UserStyleRepository $userStyleRepository, EntityManagerInterface $em,
        ResponseService $responseService
    )
    {
        $user = $this->getUser();

        $style = $userStyleRepository->findCurrent($user);

        if(!$style)
        {
            $style = (new UserStyle())
                ->setUser($user)
                ->setName(UserStyle::CURRENT)
            ;

            $em->persist($style);
        }

        foreach(UserStyle::PROPERTIES as $property)
        {
            $color = $request->request->get($property);

            if(!preg_match('/^#?[0-9a-fA-F]{6}$/', $color))
                continue;

            if(strlen($color) === 7)
                $color = substr($color, 1);

            $style->{'set' . $property}($color);
        }

        $em->flush();

        return $responseService->success();
    }
}
