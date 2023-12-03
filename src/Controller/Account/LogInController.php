<?php
namespace App\Controller\Account;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Functions\PlayerLogHelpers;
use App\Functions\UserStyleFunctions;
use App\Service\ResponseService;
use App\Service\SessionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Annotations\DoesNotRequireHouseHours;

#[Route("/account")]
class LogInController extends AbstractController
{
    /**
     * @DoesNotRequireHouseHours()
     * @Route("/logIn", methods={"POST"})
     */
    public function logIn(
        Request $request, UserPasswordHasherInterface $userPasswordEncoder, SessionService $sessionService,
        EntityManagerInterface $em, ResponseService $responseService
    )
    {
        $email = $request->request->get('email');
        $password = $request->request->get('passphrase');
        $sessionHours = $request->request->getBoolean('rememberMe') ? (7 * 24) : 1;

        if(!$email || !$password)
            throw new PSPFormValidationException('"email" and "passphrase" are both required.');

        $user = $em->getRepository(User::class)->findOneBy([ 'email' => $email ]);

        if(!$user || !$userPasswordEncoder->isPasswordValid($user, $password))
            throw new AccessDeniedHttpException('Email and/or passphrase is not correct.');

        if($user->getIsLocked())
            throw new AccessDeniedHttpException('This account has been locked.');

        $session = $sessionService->logIn($user, $sessionHours);

        $user = $session->getUser();

        $loginFromPath = parse_url($request->server->get('HTTP_REFERER'), PHP_URL_PATH);

        if($loginFromPath === '/')
        {
            if($user->getUnreadNews() === 1)
                $user->setUnreadNews(0);
        }
        else if($loginFromPath === '/news')
        {
            $user->setUnreadNews(0);
        }

        PlayerLogHelpers::create(
            $em,
            $user,
            'You logged in from `' . $request->getClientIp() . '`.',
            [ 'Account & Security' ]
        );

        $em->flush();

        $responseService->setSessionId($session->getSessionId());

        $currentTheme = UserStyleFunctions::findCurrent($em, $user->getId());

        return $responseService->success(
            [ 'currentTheme' => $currentTheme ],
            [ SerializationGroupEnum::MY_STYLE ]
        );
    }
}
