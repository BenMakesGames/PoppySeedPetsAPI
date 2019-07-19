<?php
namespace App\Controller;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\PetSkills;
use App\Entity\User;
use App\Entity\UserNotificationPreferences;
use App\Enum\FlavorEnum;
use App\Enum\SerializationGroupEnum;
use App\Functions\ArrayFunctions;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserNotificationPreferencesRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserRepository;
use App\Repository\UserStatsRepository;
use App\Service\Filter\UserFilterService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\SessionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/account")
 */
class AccountController extends PsyPetsController
{
    /**
     * @Route("/register", methods={"POST"})
     */
    public function register(
        Request $request, EntityManagerInterface $em, ResponseService $responseService,
        SessionService $sessionService, UserRepository $userRepository, PetSpeciesRepository $petSpeciesRepository,
        UserPasswordEncoderInterface $userPasswordEncoder, InventoryService $inventoryService
    )
    {
        $petName = trim($request->request->get('petName'));
        $petImage = $request->request->get('petImage');
        $petColorA = $request->request->get('petColorA');
        $petColorB = $request->request->get('petColorB');

        $name = trim($request->request->get('playerName'));
        $email = $request->request->get('playerEmail');
        $password = $request->request->get('playerPassphrase');

        if($email === '')
            throw new UnprocessableEntityHttpException('Email address is required.');

        if(!\filter_var($email, FILTER_VALIDATE_EMAIL))
            throw new UnprocessableEntityHttpException('Email address is not valid.');

        if(\strlen($petName) < 1 || \strlen($petName) > 30)
            throw new UnprocessableEntityHttpException('Pet name must be between 1 and 30 characters long.');

        $species = $petSpeciesRepository->findOneBy([ 'image' => $petImage ]);

        if(!$species)
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

        $user->setPassword($userPasswordEncoder->encodePassword($user, $password));

        $sessionService->logIn($user);

        $em->persist($user);

        $petSkills = new PetSkills();

        $em->persist($petSkills);

        $pet = (new Pet())
            ->setOwner($user)
            ->setName($petName)
            ->setSpecies($species)
            ->setColorA($petColorA)
            ->setColorB($petColorB)
            ->setNeeds(mt_rand(10, 12), -9)
            ->setSkills($petSkills)
            ->setFavoriteFlavor(FlavorEnum::getRandom())
        ;

        $em->persist($pet);

        $inventoryService->receiveItem('Welcome Note', $user, null, 'This Welcome Note was waiting for ' . $user->getName() . ' in their house.');

        $preferences = (new UserNotificationPreferences())
            ->setUser($user)
        ;

        $em->persist($preferences);

        $em->flush();

        return $responseService->success();
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
        $sessionHours = $request->request->getInt('sessionHours', 0);

        if(!$email || !$password)
            throw new UnprocessableEntityHttpException('"email" and "passphrase" are both required.');

        $user = $userRepository->findOneBy([ 'email' => $email ]);

        if(!$user || !$userPasswordEncoder->isPasswordValid($user, $password))
            throw new AccessDeniedHttpException('Email and/or passphrase is not correct.');

        if($user->getIsLocked())
            throw new AccessDeniedHttpException('This account has been locked.');

        $sessionService->logIn($user, $sessionHours);

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getAccount(ResponseService $responseService)
    {
        return $responseService->success();
    }

    /**
     * @Route("/notificationPreferences", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getNotificationPreferences(
        UserNotificationPreferencesRepository $notificationPreferencesRepository, ResponseService $responseService
    )
    {
        return $responseService->success(
            $notificationPreferencesRepository->findOneBy([ 'user' => $this->getUser() ]),
            SerializationGroupEnum::NOTIFICATION_PREFERENCES
        );
    }

    /**
     * @Route("/rename", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function rename(Request $request, ResponseService $responseService, EntityManagerInterface $em)
    {
        $name = trim($request->request->get('name'));

        if(\strlen($name) < 2 || \strlen($name) > 30)
            throw new UnprocessableEntityHttpException('Name must be between 2 and 30 characters long.');

        $this->getUser()->setName($name);

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/search", methods={"GET"})
     */
    public function search(Request $request, UserFilterService $userFilterService, ResponseService $responseService)
    {
        return $responseService->success(
            $userFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::USER_PUBLIC_PROFILE ]
        );
    }

    /**
     * @Route("/logOut", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function logOut(EntityManagerInterface $em, ResponseService $responseService)
    {
        $user = $this->getUser();

        $user->logOut();

        $em->flush();

        return $responseService->success();
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

        $days = (new \DateTimeImmutable())->diff($user->getLastAllowanceCollected())->days;

        if($days < 7)
            throw new UnprocessableEntityHttpException('It\'s too early to collect your weekly Care Package.');

        if($type === 1)
        {
            $newInventory = $inventoryService->receiveItem('Fruits & Veggies Box', $user, $user, $user->getName() . ' got this from a weekly Care Package.');
        }
        else if($type === 2)
        {
            $newInventory = $inventoryService->receiveItem('Baker\'s Box', $user, $user, $user->getName() . ' got this from a weekly Care Package.');
        }
        else
            throw new UnprocessableEntityHttpException('Must specify a Care Package "type".');

        $user->setLastAllowanceCollected($user->getLastAllowanceCollected()->modify('+' . (floor($days / 7) * 7) . ' days'));

        $em->flush();

        return $responseService->success($newInventory, SerializationGroupEnum::MY_INVENTORY);
    }

    /**
     * @Route("/collect4thOfJulyBox", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function collect4thOfJulyBox(
        InventoryService $inventoryService, EntityManagerInterface $em, ResponseService $responseService,
        UserQuestRepository $userQuestRepository
    )
    {
        $now = new \DateTimeImmutable();

        if($now->format('m') != 7 || $now->format('d') < 3 || $now->format('d') > 5)
            throw new NotFoundHttpException();

        $user = $this->getUser();

        $gotBox = $userQuestRepository->findOrCreate($user, '4th of July, ' . $now->format('Y'), false);

        if($gotBox->getValue())
            throw new UnprocessableEntityHttpException('You already received the 4th of July Box this year.');

        $gotBox->setValue(true);

        $inventoryService->receiveItem('4th of July Box', $user, $user, 'Received on the ' . $now->format('jS') . ' of July, ' . $now->format('Y'));

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/stats", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getStats(ResponseService $responseService, UserStatsRepository $userStatsRepository)
    {
        $stats = $userStatsRepository->findBy([ 'user' => $this->getUser() ]);

        return $responseService->success($stats, SerializationGroupEnum::MY_STATS);
    }

    /**
     * @Route("/typeahead", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function typeaheadSearch(Request $request, ResponseService $responseService, UserRepository $userRepository)
    {
        $search = trim($request->query->get('search', ''));
        $maxResults = 5;

        if($search === '')
            throw new UnprocessableEntityHttpException('search must contain at least one character.');

        $users = $userRepository->createQueryBuilder('u')
            ->andWhere('u.name LIKE :nameLike')
            ->setParameter('nameLike', $search . '%')
            ->setMaxResults($maxResults)
            ->orderBy('u.name', 'ASC')
            ->getQuery()
            ->execute()
        ;

        if(count($users) < $maxResults)
        {
            $ids = array_map(function(User $u) { return $u->getId(); }, $users);

            $users = array_merge($users, $userRepository->createQueryBuilder('u')
                ->andWhere('u.name LIKE :nameLike')
                ->andWhere('u.id NOT IN (:ids)')
                ->setParameter('nameLike', '%' . $search . '%')
                ->setParameter('ids', $ids)
                ->setMaxResults($maxResults - count($users))
                ->orderBy('u.name', 'ASC')
                ->getQuery()
                ->execute()
            );
        }

        $suggestions = array_map(function(User $u) { return [ 'name' => $u->getName(), 'id' => $u->getId() ]; }, $users);

        return $responseService->success($suggestions);
    }

    /**
     * @Route("/{user}", methods={"GET"}, requirements={"user"="\d+"})
     */
    public function getProfile(User $user, ResponseService $responseService)
    {
        return $responseService->success($user, SerializationGroupEnum::USER_PUBLIC_PROFILE);
    }
}