<?php
namespace App\Controller;

use App\Entity\Pet;
use App\Entity\PetSkills;
use App\Entity\User;
use App\Enum\SerializationGroup;
use App\Functions\ArrayFunctions;
use App\Repository\UserRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\SessionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/account")
 */
class AccountController extends PsyPetsController
{
    const STARTING_PET_IMAGES = [
        'mammal/desikh'
    ];

    /**
     * @Route("/register", methods={"POST"})
     */
    public function register(
        Request $request, EntityManagerInterface $em, ResponseService $responseService,
        SessionService $sessionService, UserRepository $userRepository,
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

        if(!\filter_var($email, FILTER_VALIDATE_EMAIL))
            throw new UnprocessableEntityHttpException('Email address is not valid.');

        if(\strlen($petName) < 2 || \strlen($petName) > 30)
            throw new UnprocessableEntityHttpException('Pet name must be between 2 and 30 characters long.');

        if(!\in_array($petImage, self::STARTING_PET_IMAGES))
            throw new UnprocessableEntityHttpException('Must choose your pet\'s appearance.');

        if(!\preg_match('/[A-Fa-f0-9]{6}/', $petColorA))
            throw new UnprocessableEntityHttpException('Pet color A is not valid.');

        if(!\preg_match('/[A-Fa-f0-9]{6}/', $petColorB))
            throw new UnprocessableEntityHttpException('Pet color B is not valid.');

        if(\strlen($name) < 2 || \strlen($name) > 30)
            throw new UnprocessableEntityHttpException('Name must be between 2 and 30 characters long.');

        if(\strlen($password) < 10)
            throw new UnprocessableEntityHttpException('Pass phrase must be at least 10 characters long.');

        $existingUser = $userRepository->findOneBy([ 'email' => $email ]);

        if($existingUser)
            throw new UnprocessableEntityHttpException('Email address is already in use.');

        $user = (new User())
            ->setEmail($email)
            ->setName($name)
        ;

        $sessionService->logIn($user);

        $user->setPassword($userPasswordEncoder->encodePassword($user, $password));

        $em->persist($user);

        $petSkills = new PetSkills();

        $em->persist($petSkills);

        $pet = (new Pet())
            ->setOwner($user)
            ->setName($petName)
            ->setImage($petImage)
            ->setColorA($petColorA)
            ->setColorB($petColorB)
            ->setNeeds(mt_rand(10, 12), -9)
            ->setSkills($petSkills)
        ;

        $em->persist($pet);

        $em->flush();

        return $responseService->success(null, $user, SerializationGroup::LOG_IN);
    }

    /**
     * @Route("/logIn", methods={"POST"})
     */
    public function logIn(
        Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $userPasswordEncoder,
        SessionService $sessionService, EntityManagerInterface $em, ResponseService $responseService
    )
    {
        $email = $request->request->get('email');
        $password = $request->request->get('passphrase');

        if(!$email || !$password)
            throw new UnprocessableEntityHttpException('"email" and "passphrase" are both required.');

        $user = $userRepository->findOneBy([ 'email' => $email ]);

        if(!$user || !$userPasswordEncoder->isPasswordValid($user, $password))
            throw new AccessDeniedHttpException('Email and/or passphrase is not correct.');

        if($user->getIsLocked())
            throw new AccessDeniedHttpException('This account has been locked.');

        $sessionService->logIn($user);

        $em->flush();

        return $responseService->success(null, $user, SerializationGroup::LOG_IN);
    }

    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getAccount(ResponseService $responseService)
    {
        return $responseService->success(null, $this->getUser(), SerializationGroup::LOG_IN);
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

    /**
     * @Route("/collectWeeklyCarePackage", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function collectWeeklyBox(
        Request $request, EntityManagerInterface $em, ResponseService $responseService,
        InventoryService $inventoryService
    )
    {
        $user = $this->getUser();

        $type = $request->request->getInt('type');

        if($type < 1 || $type > 2)
            throw new UnprocessableEntityHttpException('Must specify a Care Package "type".');

        $days = (new \DateTimeImmutable())->diff($user->getLastAllowanceCollected())->days;

        if($days < 7)
            throw new UnprocessableEntityHttpException('It\'s too early to collect your weekly Care Package.');

        $user->setLastAllowanceCollected($user->getLastAllowanceCollected()->modify('+' . (floor($days / 7) * 7) . ' days'));

        $newInventory = [];

        if($type === 1)
        {
            for($i = 0; $i < 3; $i++)
                $newInventory[] = $inventoryService->giveCopyOfItem(ArrayFunctions::pick_one(['Carrot', 'Onion', 'Celery', 'Carrot', 'Sweet Beet']), $user, $user, $user->getName() . ' got this from a weekly Care Package.');

            for($i = 0; $i < 3; $i++)
                $newInventory[] = $inventoryService->giveCopyOfItem(ArrayFunctions::pick_one(['Orange', 'Red', 'Blackberries', 'Blueberries']), $user, $user, $user->getName() . ' got this from a weekly Care Package.');
        }
        else if($type === 2)
        {
            for($i = 0; $i < 5; $i++)
                $newInventory[] = $inventoryService->giveCopyOfItem(ArrayFunctions::pick_one(['Egg', 'Wheat Flour', 'Sugar', 'Milk']), $user, $user, $user->getName() . ' got this from a weekly Care Package.');

            for($i = 0; $i < 2; $i++)
                $newInventory[] = $inventoryService->giveCopyOfItem(ArrayFunctions::pick_one(['Corn Syrup', 'Aging Powder', 'Cocoa Beans']), $user, $user, $user->getName() . ' got this from a weekly Care Package.');
        }

        $em->flush();

        return $responseService->success($newInventory, $user, SerializationGroup::MY_INVENTORY);
    }

    /**
     * @Route("/{user}", methods={"GET"}, requirements={"user"="\d+"})
     */
    public function getProfile(User $user, ResponseService $responseService)
    {
        $currentUser = $this->getUser();

        $groups = [ SerializationGroup::PUBLIC_PROFILE ];

        if($currentUser)
        {
            $groups[] = SerializationGroup::SEMI_PRIVATE_PROFILE;

            // TODO: if mutual friends, add SerializationGroup::PRIVATE_PROFILE
        }

        return $responseService->success($user, null, $groups);
    }
}