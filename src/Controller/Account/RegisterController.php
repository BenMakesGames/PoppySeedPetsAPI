<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Controller\Account;

use App\Attributes\DoesNotRequireHouseHours;
use App\Entity\PetSpecies;
use App\Entity\User;
use App\Entity\UserStyle;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Exceptions\PSPFormValidationException;
use App\Functions\MeritRepository;
use App\Functions\ProfanityFilterFunctions;
use App\Functions\StringFunctions;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetFactory;
use App\Service\ResponseService;
use App\Service\SessionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/account")]
class RegisterController extends AbstractController
{
    #[DoesNotRequireHouseHours]
    #[Route("/register", methods: ["POST"])]
    public function register(
        Request $request, EntityManagerInterface $em, ResponseService $responseService, SessionService $sessionService,
        UserPasswordHasherInterface $userPasswordEncoder, InventoryService $inventoryService, PetFactory $petFactory,
        IRandom $rng
    )
    {
        $theme = $request->request->all('theme');
        $petName = ProfanityFilterFunctions::filter(trim($request->request->getString('petName')));
        $petImage = $request->request->getString('petImage');
        $petColorA = $request->request->get('petColorA');
        $petColorB = $request->request->get('petColorB');

        $name = ProfanityFilterFunctions::filter(trim($request->request->getString('playerName')));
        $email = $request->request->getString('playerEmail');
        $passPhrase = $request->request->getString('playerPassphrase');

        if($email === '')
            throw new PSPFormValidationException('Email address is required.');

        if(!\filter_var($email, FILTER_VALIDATE_EMAIL))
            throw new PSPFormValidationException('Email address is not valid.');

        if(str_ends_with($email, '@poppyseedpets.com') || str_ends_with($email, '.poppyseedpets.com'))
            throw new PSPFormValidationException('poppyseedpets.com e-mail addresses cannot be used.');

        if(\mb_strlen($petName) < 1)
            throw new PSPFormValidationException('Pet name must be between 1 and 30 characters long.');
        else if(\mb_strlen($petName) > 30)
            $petName = \mb_substr($petName, 0, 30);

        if(!StringFunctions::isISO88591($petName))
            throw new PSPFormValidationException('Your pet\'s name contains some mighty-strange characters! (Please limit yourself to the "Extended ASCII" character set.)');

        $species = $em->getRepository(PetSpecies::class)->findOneBy([ 'image' => $petImage ]);

        if(!$species || !$species->getAvailableAtSignup())
            throw new PSPFormValidationException('Must choose your pet\'s appearance.');

        if(!preg_match('/[A-Fa-f0-9]{6}/', $petColorA))
            throw new PSPFormValidationException('Pet color A is not valid.');

        if(!preg_match('/[A-Fa-f0-9]{6}/', $petColorB))
            throw new PSPFormValidationException('Pet color B is not valid.');

        if(\mb_strlen($name) < 2)
            throw new PSPFormValidationException('Name must be between 2 and 30 characters long.');
        else if(\mb_strlen($name) > 30)
            $name = \mb_substr($name, 0, 30);

        if(!StringFunctions::isISO88591($name))
            throw new PSPFormValidationException('Your name contains some mighty-strange characters! (Please limit yourself to the "Extended ASCII" character set.)');

        if(\mb_strlen($passPhrase) < User::MinPassphraseLength)
            throw new PSPFormValidationException('Pass phrase must be at least ' . User::MinPassphraseLength . ' characters long. (Pro tip: try using an actual phrase, or short sentence!)');

        if(\mb_strlen($passPhrase) > User::MaxPassphraseLength)
            throw new PSPFormValidationException('Pass phrase must not exceed ' . User::MaxPassphraseLength . ' characters.');

        $existingUser = $em->getRepository(User::class)->findOneBy([ 'email' => $email ]);

        if($existingUser)
            throw new PSPFormValidationException('Email address is already in use.');

        $user = new User(name: $name, email: $email);

        $user->setPassword($userPasswordEncoder->hashPassword($user, $passPhrase));

        $session = $sessionService->logIn($user);

        $responseService->setSessionId($session->getSessionId());

        $em->persist($user);

        $favoriteFlavor = $rng->rngNextFromArray([
            FlavorEnum::EARTHY, FlavorEnum::FRUITY, FlavorEnum::CREAMY, FlavorEnum::MEATY, FlavorEnum::PLANTY,
            FlavorEnum::FISHY, FlavorEnum::FATTY,
        ]);

        $startingMerit = MeritRepository::getRandomFirstPetStartingMerit($em, $rng);

        $pet = $petFactory->createPet($user, $petName, $species, $petColorA, $petColorB, $favoriteFlavor, $startingMerit);

        $pet
            ->setFoodAndSafety($rng->rngNextInt(10, 12), -9)
            ->setScale($rng->rngNextInt(90, 110))
        ;

        $inventoryService->receiveItem('Welcome Note', $user, null, 'This Welcome Note was waiting for ' . $user->getName() . ' in their house.', LocationEnum::HOME, true);

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
