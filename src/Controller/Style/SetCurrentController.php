<?php
namespace App\Controller\Style;

use App\Entity\User;
use App\Entity\UserStyle;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Functions\UserStyleFunctions;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/style")]
class SetCurrentController extends AbstractController
{
    #[Route("/{theme}/setCurrent", methods: ["PATCH"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function setCurrent(
        UserStyle $theme, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($theme->getName() === UserStyle::CURRENT)
            throw new PSPInvalidOperationException('You\'re already using that theme!');

        $current = UserStyleFunctions::findCurrent($em, $user->getId());

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
}
