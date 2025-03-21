<?php
declare(strict_types=1);

namespace App\Controller\Account;

use App\Attributes\DoesNotRequireHouseHours;
use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Functions\PlayerLogFactory;
use App\Functions\UserStyleFunctions;
use App\Service\ResponseService;
use App\Service\SessionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/account")]
final class LogInController extends AbstractController
{
    #[DoesNotRequireHouseHours]
    #[Route("/logIn", methods: ["POST"])]
    public function logIn(
        #[MapRequestPayload] LogInRequest $logInRequest, Request $request,
        UserPasswordHasherInterface $userPasswordEncoder, SessionService $sessionService,
        EntityManagerInterface $em, ResponseService $responseService
    )
    {
        if(!$logInRequest->email || !$logInRequest->passphrase)
            throw new PSPFormValidationException('"email" and "passphrase" are both required.');

        $user = $em->getRepository(User::class)->findOneBy([ 'email' => $logInRequest->email ]);

        if(!$user || !$userPasswordEncoder->isPasswordValid($user, $logInRequest->passphrase))
            throw new AccessDeniedHttpException('Email and/or passphrase is not correct.');

        if($user->getIsLocked())
            throw new AccessDeniedHttpException('This account has been locked.');

        $session = $sessionService->logIn($user);

        $user = $session->getUser();

        $loginFromPath = parse_url($request->server->getString('HTTP_REFERER', 'https://poppyseedpets.com/'), PHP_URL_PATH);

        if($loginFromPath === '/')
        {
            if($user->getUnreadNews() === 1)
                $user->setUnreadNews(0);
        }
        else if($loginFromPath === '/news')
        {
            $user->setUnreadNews(0);
        }

        PlayerLogFactory::create(
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

final class LogInRequest
{
    public function __construct(
        public readonly ?string $email,
        public readonly ?string $passphrase,
    )
    {
    }
}