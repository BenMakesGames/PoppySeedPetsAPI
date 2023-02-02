<?php
namespace App\Controller\Style;

use App\Entity\User;
use App\Entity\UserStyle;
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
class SaveCurrentController extends AbstractController
{
    /**
     * @Route("/current", methods={"PATCH"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function saveCurrentStyle(
        Request $request, UserStyleRepository $userStyleRepository, EntityManagerInterface $em,
        ResponseService $responseService
    )
    {
        /** @var User $user */
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
