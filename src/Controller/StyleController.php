<?php
namespace App\Controller;

use App\Entity\UserStyle;
use App\Enum\SerializationGroupEnum;
use App\Repository\UserStyleRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/style")
 */
class StyleController extends PoppySeedPetsController
{
    const PROPERTIES = [
        'backgroundColor',
        'petInfoBackgroundColor',
        'speechBubbleBackgroundColor',
        'textColor',
        'primaryColor',
        'textOnPrimaryColor',
        'tabBarBackgroundColor',
        'linkAndButtonColor',
        'buttonTextColor',
        'dialogLinkColor',
        'warningColor',
        'gainColor',
        'bonusAndSpiceColor',
        'bonusAndSpiceSelectedColor',
        'inputBackgroundColor',
        'inputTextColor'
    ];

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
     * @Route("", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function shareTheme(
        UserStyleRepository $userStyleRepository, ResponseService $responseService,
        Request $request, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();
        $name = trim($request->request->getAlnum('name'));

        if(strlen($name) < 1 || strlen($name) > 15)
            throw new UnprocessableEntityHttpException('Name must be between 1 and 15 characters.');

        $current = $userStyleRepository->findCurrent($user);

        if(!$current)
            throw new UnprocessableEntityHttpException('You have to save your current theme, first.');

        $existingTheme = $userStyleRepository->findBy([ 'user' => $user, 'name' => $name ]);

        if($existingTheme)
            throw new UnprocessableEntityHttpException('You already have a theme with that name.');

        $newTheme = (new UserStyle())
            ->setUser($user)
            ->setName($name)
        ;

        foreach(self::PROPERTIES as $property)
            $newTheme->{'set' . $property}($current->{'get' . $property}());

        $em->persist($newTheme);
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

        foreach(self::PROPERTIES as $property)
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
