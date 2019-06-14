<?php
namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\RandomService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SessionController extends AbstractController
{
    /**
     * @Route("/register", methods={"POST"})
     */
    public function register(
        Request $request, EntityManagerInterface $em, ResponseService $responseService,
        RandomService $randomService, UserRepository $userRepository,
        UserPasswordEncoderInterface $userPasswordEncoder
    )
    {
        $name = $request->request->get('name');
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        if(!filter_var($email, FILTER_VALIDATE_EMAIL))
            throw new UnprocessableEntityHttpException('Email address is not valid.');

        if(strlen($name) < 2 || strlen($name) > 30)
            throw new UnprocessableEntityHttpException('Name must be between 2 and 30 characters long.');

        if(strlen($password) < 10)
            throw new UnprocessableEntityHttpException('Pass phrase must be at least 10 characters long.');

        $existingUser = $userRepository->findOneBy([ 'email' => $email ]);

        if($existingUser)
            throw new UnprocessableEntityHttpException('Email address is already in use.');

        $user = new User();

        $sessionId = $randomService->getString(40);

        $user
            ->setEmail($email)
            ->setName($name)
            ->setLastActivity(new \DateTimeImmutable())
            ->setSessionExpiration((new \DateTimeImmutable())->modify('+8 hours'))
            ->setSessionId($sessionId)
        ;

        $user->setPassword($userPasswordEncoder->encodePassword($user, $password));

        $em->persist($user);
        $em->flush();

        return $responseService->success($user, 'logIn');
    }

    /**
     * @Route("/logIn", methods={"POST"})
     */
    public function logIn(
        Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $userPasswordEncoder,
        RandomService $randomService, EntityManagerInterface $em, ResponseService $responseService
    )
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        $user = $userRepository->findOneBy([ 'email' => $email ]);

        if(!$user || !$userPasswordEncoder->isPasswordValid($user, $password))
            throw new AccessDeniedHttpException('Username and/or password does not exist.');

        $sessionId = $randomService->getString(40);

        $user
            ->setLastActivity(new \DateTimeImmutable())
            ->setSessionExpiration((new \DateTimeImmutable())->modify('+8 hours'))
            ->setSessionId($sessionId)
        ;

        $em->flush();

        return $responseService->success($user, 'logIn');
    }

    /**
     * @Route("/logOut", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function logOut(EntityManagerInterface $em)
    {
        $user = $this->getUser();

        $user
            ->setLastActivity(new \DateTimeImmutable())
            ->setSessionExpiration(new \DateTimeImmutable())
        ;

        $em->flush();
    }
}