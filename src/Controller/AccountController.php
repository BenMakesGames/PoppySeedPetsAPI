<?php
namespace App\Controller;

use App\Entity\Inventory;
use App\Entity\PassphraseResetRequest;
use App\Entity\Pet;
use App\Entity\PetSkills;
use App\Entity\User;
use App\Entity\UserNotificationPreferences;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Functions\StringFunctions;
use App\Repository\InventoryRepository;
use App\Repository\PassphraseResetRequestRepository;
use App\Repository\PetRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserNotificationPreferencesRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserRepository;
use App\Repository\UserStatsRepository;
use App\Service\Filter\UserFilterService;
use App\Service\InventoryService;
use App\Service\PassphraseResetService;
use App\Service\ProfanityFilterService;
use App\Service\ResponseService;
use App\Service\SessionService;
use App\Service\Typeahead\UserTypeaheadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @Route("/account")
 */
class AccountController extends PoppySeedPetsController
{
    /**
     * @Route("/register", methods={"POST"})
     */
    public function register(
        Request $request, EntityManagerInterface $em, ResponseService $responseService,
        SessionService $sessionService, UserRepository $userRepository, PetSpeciesRepository $petSpeciesRepository,
        UserPasswordEncoderInterface $userPasswordEncoder, InventoryService $inventoryService,
        ProfanityFilterService $profanityFilterService
    )
    {
        $petName = $profanityFilterService->filter(trim($request->request->get('petName')));
        $petImage = $request->request->get('petImage');
        $petColorA = $request->request->get('petColorA');
        $petColorB = $request->request->get('petColorB');

        $name = $profanityFilterService->filter(trim($request->request->get('playerName')));
        $email = $request->request->get('playerEmail');
        $password = $request->request->get('playerPassphrase');

        if($email === '')
            throw new UnprocessableEntityHttpException('Email address is required.');

        if(!\filter_var($email, FILTER_VALIDATE_EMAIL))
            throw new UnprocessableEntityHttpException('Email address is not valid.');

        if(\strlen($petName) < 1 || \strlen($petName) > 30)
            throw new UnprocessableEntityHttpException('Pet name must be between 1 and 30 characters long.');

        $species = $petSpeciesRepository->findOneBy([ 'image' => $petImage ]);

        if(!$species || !$species->getAvailableAtSignup())
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

        $session = $sessionService->logIn($user);

        $responseService->setSessionId($session->getSessionId());

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
            ->setFavoriteFlavor(ArrayFunctions::pick_one([
                FlavorEnum::EARTHY, FlavorEnum::FRUITY, FlavorEnum::CREAMY, FlavorEnum::MEATY, FlavorEnum::PLANTY,
                FlavorEnum::FISHY, FlavorEnum::FATTY,
            ]))
        ;

        $em->persist($pet);

        $inventoryService->receiveItem('Welcome Note', $user, null, 'This Welcome Note was waiting for ' . $user->getName() . ' in their house.', LocationEnum::HOME, true);

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

        $session = $sessionService->logIn($user, $sessionHours);

        $user = $session->getUser();

        if($user->getUnlockedMuseum() === null && $user->getRegisteredOn() <= (new \DateTimeImmutable())->modify('-5 days'))
            $user->setUnlockedMuseum();

        $em->flush();

        $responseService->setSessionId($session->getSessionId());
        return $responseService->success();
    }

    /**
     * @Route("/updateEmail", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function updateEmail(
        Request $request, ResponseService $responseService, UserPasswordEncoderInterface $passwordEncoder,
        UserRepository $userRepository
    )
    {
        $user = $this->getUser();

        if(!$passwordEncoder->isPasswordValid($user, $request->request->get('confirmPassphrase')))
            throw new AccessDeniedHttpException('Passphrase is not correct.');

        $newEmail = trim($request->request->get('newEmail'));

        if($newEmail === '' || !filter_var($newEmail, FILTER_VALIDATE_EMAIL))
            throw new UnprocessableEntityHttpException('Email address is not valid.');

        $alreadyInUse = $userRepository->findOneBy([ 'email' => $newEmail ]);

        if($alreadyInUse)
        {
            if($alreadyInUse->getId() === $user->getId())
                return $responseService->success(); // sure.
            else
            {
                if(strpos($newEmail, '+') === -1)
                {
                    $emailParts = explode('@', $newEmail);
                    $exampleEmail = $emailParts[0] . '+whatever@' . $emailParts[1];
                    throw new UnprocessableEntityHttpException('That e-mail address is already in use. If it\'s your e-mail address, many e-mail services allow you to put a "+" in your address, for example "' . $exampleEmail . '".');
                }
                else
                    throw new UnprocessableEntityHttpException('That e-mail address is already in use.');
            }
        }

        $user->setEmail($newEmail);

        return $responseService->success();
    }

    /**
     * @Route("/updatePassphrase", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function updatePassphrase(
        Request $request, ResponseService $responseService, UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if(!$passwordEncoder->isPasswordValid($user, $request->request->get('confirmPassphrase')))
            throw new AccessDeniedHttpException('Passphrase is not correct.');

        $newPassphrase = trim($request->request->get('newPassphrase'));

        if(strlen($newPassphrase) < 10)
            throw new UnprocessableEntityHttpException('Passphrase must be at least 10 characters long.');

        $user->setPassword($passwordEncoder->encodePassword($user, $newPassphrase));

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getAccount(ResponseService $responseService, EntityManagerInterface $em)
    {
        $user = $this->getUser();

        if($user->getUnlockedMuseum() === null && $user->getRegisteredOn() <= (new \DateTimeImmutable())->modify('-5 days'))
        {
            $user->setUnlockedMuseum();
            $em->flush();
        }

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
    public function logOut(EntityManagerInterface $em, ResponseService $responseService, SessionService $sessionService)
    {
        $sessionService->logOut();

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

        $canGetHandicraftsBox = $user->getUnlockedPark() && $user->getMaxPlants() > 3;
        $canGetGamingBox = $user->getUnlockedHollowEarth();

        if($type === 1)
        {
            $newInventory = $inventoryService->receiveItem('Fruits & Veggies Box', $user, $user, $user->getName() . ' got this as a weekly Care Package.', LocationEnum::HOME, true);
        }
        else if($type === 2)
        {
            $newInventory = $inventoryService->receiveItem('Baker\'s Box', $user, $user, $user->getName() . ' got this as a weekly Care Package.', LocationEnum::HOME, true);
        }
        else if($type === 3 && $canGetHandicraftsBox)
        {
            $newInventory = $inventoryService->receiveItem('Handicrafts Supply Box', $user, $user, $user->getName() . ' got this as a weekly Care Package.', LocationEnum::HOME, true);
        }
        else if($type === 4 && $canGetGamingBox)
        {
            $newInventory = $inventoryService->receiveItem('Gaming Box', $user, $user, $user->getName() . ' got this as a weekly Care Package.', LocationEnum::HOME, true);
        }
        else
            throw new UnprocessableEntityHttpException('Must specify a Care Package "type".');

        $user->setLastAllowanceCollected($user->getLastAllowanceCollected()->modify('+' . (floor($days / 7) * 7) . ' days'));

        $em->flush();

        return $responseService->success($newInventory, SerializationGroupEnum::MY_INVENTORY);
    }

    /**
     * @Route("/collectHolidayBox", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function collectHolidayBox(
        InventoryService $inventoryService, EntityManagerInterface $em, ResponseService $responseService,
        UserQuestRepository $userQuestRepository
    )
    {
        $now = new \DateTimeImmutable();

        $user = $this->getUser();

        $month = (int)$now->format('m');
        $day = (int)$now->format('d');

        if($month === 7 && $day >= 3 && $day <= 5)
        {
            $gotBox = $userQuestRepository->findOrCreate($user, '4th of July, ' . $now->format('Y'), false);

            if($gotBox->getValue())
                throw new UnprocessableEntityHttpException('You already received the 4th of July Box this year.');

            $gotBox->setValue(true);

            $inventoryService->receiveItem('4th of July Box', $user, $user, 'Received on the ' . $now->format('jS') . ' of July, ' . $now->format('Y'), LocationEnum::HOME, true);
        }
        else if(($month === 12 && $day === 31) || ($month === 1 && $day <= 2))
        {
            $year = $month === 12 ? ((int)$now->format('Y') + 1) : (int)$now->format('Y');

            $gotBox = $userQuestRepository->findOrCreate($user, 'New Year, ' . $year, false);

            if($gotBox->getValue())
                throw new UnprocessableEntityHttpException('You already received the New Year Box this year.');

            $gotBox->setValue(true);

            $inventoryService->receiveItem('New Year Box', $user, $user, 'Received on the ' . $now->format('jS') . ' of ' . $now->format('F') . ', ' . $now->format('Y'), LocationEnum::HOME, true);
        }
        else
            throw new AccessDeniedHttpException('No holiday box is available right now...');

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/requestPassphraseReset", methods={"POST"})
     */
    public function requestPassphraseReset(
        Request $request, UserRepository $userRepository, ResponseService $responseService,
        PassphraseResetService $passphraseResetService
    )
    {
        $email = trim($request->request->get('email', ''));

        if($email === '')
            throw new UnprocessableEntityHttpException('E-mail address is required.');

        if(!filter_var($email, FILTER_VALIDATE_EMAIL))
            throw new UnprocessableEntityHttpException('E-mail address is invalid.');

        $user = $userRepository->findOneBy([ 'email' => $email ]);

        if(!$user)
            throw new UnprocessableEntityHttpException('There is no user with that e-mail address.');

        $passphraseResetService->requestReset($user);

        return $responseService->success();
    }

    /**
     * @Route("/requestPassphraseReset/{code}", methods={"POST"})
     */
    public function resetPassphrase(
        string $code, Request $request, PassphraseResetRequestRepository $passwordResetRequestRepository,
        UserPasswordEncoderInterface $userPasswordEncoder, EntityManagerInterface $em, ResponseService $responseService
    )
    {
        $passphrase = trim($request->request->get('passphrase', ''));

        if(strlen($passphrase) < 10)
            throw new UnprocessableEntityHttpException('Passphrase must be at least 10 characters long. (Pro tip: try using an actual phrase, or short sentence!)');

        $resetRequest = $passwordResetRequestRepository->findOneBy([ 'code' => $code ]);

        if(!$resetRequest || $resetRequest->getExpiresOn() <= new \DateTimeImmutable())
            throw new NotFoundHttpException('This reset URL is invalid, or expired.');

        $user = $resetRequest->getUser();

        $user->setPassword($userPasswordEncoder->encodePassword($user, $passphrase));

        $em->remove($resetRequest);

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
     * @Route("/myHouse", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getHouse(
        PetRepository $petRepository, InventoryRepository $inventoryRepository, ResponseService $responseService,
        NormalizerInterface $normalizer
    )
    {
        $user = $this->getUser();

        $petsAtHome = $petRepository->findBy([
            'owner' => $user->getId(),
            'inDaycare' => false,
        ]);

        $inventory = $inventoryRepository->findBy([
            'owner' => $this->getUser(),
            'location' => LocationEnum::HOME
        ]);

        return $responseService->success([
            'inventory' => $normalizer->normalize($inventory, null, [ 'groups' => [ SerializationGroupEnum::MY_INVENTORY ] ]),
            'pets' => $normalizer->normalize($petsAtHome, null, [ 'groups' => [ SerializationGroupEnum::MY_PET ] ])
        ]);
    }

    /**
     * @Route("/typeahead", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function typeaheadSearch(
        Request $request, ResponseService $responseService, UserTypeaheadService $userTypeaheadService
    )
    {
        try
        {
            $suggestions = $userTypeaheadService->search('name', $request->query->get('search', ''), 5);

            return $responseService->success($suggestions, SerializationGroupEnum::USER_TYPEAHEAD);
        }
        catch(\InvalidArgumentException $e)
        {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }
    }

    /**
     * @Route("/{user}", methods={"GET"}, requirements={"user"="\d+"})
     */
    public function getProfile(
        User $user, ResponseService $responseService, PetRepository $petRepository, InventoryRepository $inventoryRepository,
        NormalizerInterface $normalizer
    )
    {
        $pets = $petRepository->findBy([ 'owner' => $user, 'inDaycare' => false ]);

        $data = [
            'user' => $normalizer->normalize($user, null, [ 'groups' => [ SerializationGroupEnum::USER_PUBLIC_PROFILE ] ]),
            'pets' => $normalizer->normalize($pets, null, [ 'groups' => [ SerializationGroupEnum::USER_PUBLIC_PROFILE ] ]),
        ];

        if($user->getUnlockedFireplace())
        {
            $mantle = $inventoryRepository->findBy(['owner' => $user, 'location' => LocationEnum::MANTLE]);

            $data['mantle'] = $normalizer->normalize($mantle, null, [ 'groups' => [ SerializationGroupEnum::FIREPLACE_MANTLE ] ]);
        }

        return $responseService->success($data);
    }
}