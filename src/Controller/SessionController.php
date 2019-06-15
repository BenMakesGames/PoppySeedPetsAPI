<?php
namespace App\Controller;

use App\Entity\Pet;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\RandomService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SessionController extends APIController
{
    const STARTING_PET_IMAGES = [
        'desikh'
    ];

    /**
     * @Route("/register", methods={"POST"})
     */
    public function register(
        Request $request, EntityManagerInterface $em, ResponseService $responseService,
        RandomService $randomService, UserRepository $userRepository,
        UserPasswordEncoderInterface $userPasswordEncoder
    )
    {
        $petName = $request->request->get('petName');
        $petImage = $request->request->get('petImage');
        $petColorA = $request->request->get('petColorA');
        $petColorB = $request->request->get('petColorB');

        $name = $request->request->get('playerName');
        $email = $request->request->get('playerEmail');
        $password = $request->request->get('playerPassphrase');

        if($email === '')
            throw new UnprocessableEntityHttpException('Email address is required.');

        if(!filter_var($email, FILTER_VALIDATE_EMAIL))
            throw new UnprocessableEntityHttpException('Email address is not valid.');

        if(strlen($petName) < 2 || strlen($petName) > 30)
            throw new UnprocessableEntityHttpException('Pet name must be between 2 and 30 characters long.');

        if(!in_array($petImage, self::STARTING_PET_IMAGES))
            throw new UnprocessableEntityHttpException('Must choose your pet\'s appearance.');

        if(!preg_match('/[A-Fa-f0-9]{6}/', $petColorA))
            throw new UnprocessableEntityHttpException('Pet color A is not valid.');

        if(!preg_match('/[A-Fa-f0-9]{6}/', $petColorB))
            throw new UnprocessableEntityHttpException('Pet color B is not valid.');

        if(strlen($name) < 2 || strlen($name) > 30)
            throw new UnprocessableEntityHttpException('Name must be between 2 and 30 characters long.');

        if(strlen($password) < 10)
            throw new UnprocessableEntityHttpException('Pass phrase must be at least 10 characters long.');

        $existingUser = $userRepository->findOneBy([ 'email' => $email ]);

        if($existingUser)
            throw new UnprocessableEntityHttpException('Email address is already in use.');

        $user = (new User())
            ->setEmail($email)
            ->setName($name)
            ->setLastActivity()
            ->setSessionId($randomService->getString(40))
        ;

        $user->setPassword($userPasswordEncoder->encodePassword($user, $password));

        $em->persist($user);

        $pet = (new Pet())
            ->setOwner($user)
            ->setName($petName)
            ->setImage($petImage)
            ->setColorA($petColorA)
            ->setColorB($petColorB)
        ;

        $em->persist($pet);

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
        $password = $request->request->get('passphrase');

        $user = $userRepository->findOneBy([ 'email' => $email ]);

        if(!$user || !$userPasswordEncoder->isPasswordValid($user, $password))
            throw new AccessDeniedHttpException('Username and/or password does not exist.');

        $sessionId = $randomService->getString(40);

        $user
            ->setLastActivity()
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

        $user->logOut();

        $em->flush();
    }
}