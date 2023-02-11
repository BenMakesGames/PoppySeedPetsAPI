<?php
namespace App\Controller\Account;

use App\Entity\User;
use App\Entity\UserNotificationPreferences;
use App\Entity\UserStyle;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Functions\ProfanityFilterFunctions;
use App\Functions\StringFunctions;
use App\Repository\MeritRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserRepository;
use App\Service\InventoryService;
use App\Service\PetFactory;
use App\Service\ResponseService;
use App\Service\SessionService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Annotations\DoesNotRequireHouseHours;

/**
 * @Route("/account")
 */
class RegisterController extends AbstractController
{
    /**
     * @DoesNotRequireHouseHours()
     * @Route("/register", methods={"POST"})
     */
    public function register(
        Request $request, EntityManagerInterface $em, ResponseService $responseService,
        SessionService $sessionService, UserRepository $userRepository, PetSpeciesRepository $petSpeciesRepository,
        UserPasswordHasherInterface $userPasswordEncoder, InventoryService $inventoryService,
        MeritRepository $meritRepository, PetFactory $petFactory, Squirrel3 $squirrel3
    )
    {
        $theme = $request->request->get('theme');
        $petName = ProfanityFilterFunctions::filter(trim($request->request->get('petName')));
        $petImage = $request->request->get('petImage');
        $petColorA = $request->request->get('petColorA');
        $petColorB = $request->request->get('petColorB');

        $name = ProfanityFilterFunctions::filter(trim($request->request->get('playerName')));
        $email = $request->request->get('playerEmail');
        $passPhrase = $request->request->get('playerPassphrase');

        if($email === '')
            throw new UnprocessableEntityHttpException('Email address is required.');

        if(!\filter_var($email, FILTER_VALIDATE_EMAIL))
            throw new UnprocessableEntityHttpException('Email address is not valid.');

        if(str_ends_with($email, '@poppyseedpets.com') || str_ends_with($email, '.poppyseedpets.com'))
            throw new UnprocessableEntityHttpException('poppyseedpets.com e-mail addresses cannot be used.');

        if(\mb_strlen($petName) < 1)
            throw new UnprocessableEntityHttpException('Pet name must be between 1 and 30 characters long.');
        else if(\mb_strlen($petName) > 30)
            $petName = \mb_substr($petName, 0, 30);

        if(!StringFunctions::isISO88591($petName))
            throw new UnprocessableEntityHttpException('Your pet\'s name contains some mighty-strange characters! (Please limit yourself to the "Extended ASCII" character set.)');

        $species = $petSpeciesRepository->findOneBy([ 'image' => $petImage ]);

        if(!$species || !$species->getAvailableAtSignup())
            throw new UnprocessableEntityHttpException('Must choose your pet\'s appearance.');

        if(!preg_match('/[A-Fa-f0-9]{6}/', $petColorA))
            throw new UnprocessableEntityHttpException('Pet color A is not valid.');

        if(!preg_match('/[A-Fa-f0-9]{6}/', $petColorB))
            throw new UnprocessableEntityHttpException('Pet color B is not valid.');

        if(\mb_strlen($name) < 2)
            throw new UnprocessableEntityHttpException('Name must be between 2 and 30 characters long.');
        else if(\mb_strlen($name) > 30)
            $name = \mb_substr($name, 0, 30);

        if(!StringFunctions::isISO88591($name))
            throw new UnprocessableEntityHttpException('Your name contains some mighty-strange characters! (Please limit yourself to the "Extended ASCII" character set.)');

        if(\mb_strlen($passPhrase) < 10)
            throw new UnprocessableEntityHttpException('Pass phrase must be at least 10 characters long.');

        $existingUser = $userRepository->findOneBy([ 'email' => $email ]);

        if($existingUser)
            throw new UnprocessableEntityHttpException('Email address is already in use.');

        $user = (new User())
            ->setEmail($email)
            ->setName($name)
        ;

        $user->setPassword($userPasswordEncoder->hashPassword($user, $passPhrase));

        $session = $sessionService->logIn($user);

        $responseService->setSessionId($session->getSessionId());

        $em->persist($user);

        $favoriteFlavor = $squirrel3->rngNextFromArray([
            FlavorEnum::EARTHY, FlavorEnum::FRUITY, FlavorEnum::CREAMY, FlavorEnum::MEATY, FlavorEnum::PLANTY,
            FlavorEnum::FISHY, FlavorEnum::FATTY,
        ]);

        $startingMerit = $meritRepository->getRandomFirstPetStartingMerit();

        $pet = $petFactory->createPet($user, $petName, $species, $petColorA, $petColorB, $favoriteFlavor, $startingMerit);

        $pet
            ->setFoodAndSafety($squirrel3->rngNextInt(10, 12), -9)
            ->setScale($squirrel3->rngNextInt(90, 110))
        ;

        $inventoryService->receiveItem('Welcome Note', $user, null, 'This Welcome Note was waiting for ' . $user->getName() . ' in their house.', LocationEnum::HOME, true);

        $preferences = (new UserNotificationPreferences())
            ->setUser($user)
        ;

        $em->persist($preferences);

        $myTheme = (new UserStyle())
            ->setUser($user)
            ->setName(UserStyle::CURRENT)
        ;

        foreach(UserStyle::PROPERTIES as $property)
        {
            if(!array_key_exists($property, $theme))
            {
                $myTheme = null;
                break;
            }

            $color = $theme[$property];

            if(!preg_match('/^#?[0-9a-fA-F]{6}$/', $color))
            {
                $myTheme = null;
                break;
            }

            if(strlen($color) === 7)
                $color = substr($color, 1);

            $myTheme->{'set' . $property}($color);
        }

        if($myTheme)
            $em->persist($myTheme);

        $em->flush();

        return $responseService->success();
    }
}
