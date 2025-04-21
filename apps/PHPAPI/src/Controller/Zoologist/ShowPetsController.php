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


namespace App\Controller\Zoologist;

use App\Entity\Pet;
use App\Entity\User;
use App\Entity\UserSpeciesCollected;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\GrammarFunctions;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/zoologist")]
class ShowPetsController extends AbstractController
{
    #[Route("/showPets", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function showPets(
        EntityManagerInterface $em, Request $request,
        UserStatsService $userStatsRepository, ResponseService $responseService, IRandom $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Zoologist))
            throw new PSPNotUnlockedException('Zoologist');

        $petIds = $request->request->all('petIds');

        if(count($petIds) === 0)
            throw new PSPFormValidationException('No pets were selected.');

        if(count($petIds) > 20)
            throw new PSPFormValidationException('Please select no more than 20 pets.');

        $pets = $em->getRepository(Pet::class)->findBy([
            'id' => $petIds,
            'owner' => $user->getId()
        ]);

        if(count($pets) < count($petIds))
            throw new PSPPetNotFoundException();

        $petSpecies = array_map(fn(Pet $pet) => $pet->getSpecies()->getId(), $pets);

        $alreadyDiscovered = $em->getRepository(UserSpeciesCollected::class)->findBy([
            'user' => $user->getId(),
            'species' => $petSpecies
        ]);

        if(count($alreadyDiscovered) === 1)
            throw new PSPInvalidOperationException('"Hm? The ' . $alreadyDiscovered[0]->getSpecies()->getName() . '? You\'ve shown one to me before."');
        else if(count($alreadyDiscovered) > 1)
            throw new PSPInvalidOperationException('"You\'ve already shown me some of those..." (Reload the page and try again??)');

        foreach($pets as $pet)
        {
            $discovery = (new UserSpeciesCollected())
                ->setUser($user)
                ->setSpecies($pet->getSpecies())
                ->setPetName($pet->getName())
                ->setColorA($pet->getPerceivedColorA())
                ->setColorB($pet->getPerceivedColorB())
                ->setScale($pet->getScale())
            ;

            $em->persist($discovery);
        }

        $userStatsRepository->incrementStat($user, 'Species Cataloged', count($pets));

        $em->flush();

        if(count($pets) === 1)
        {
            $message = $rng->rngNextFromArray([
                'Ah, the ' . $pets[0]->getSpecies()->getName() . '! Wonderful! I\'ll get to work on sequencing its DNA immediately!',
                'Lovely; lovely! The ' . $pets[0]->getSpecies()->getName() . ' is one of my favorite members of the ' . $pets[0]->getSpecies()->getFamily() . ' family!',
                $pets[0]->getName() . ' is such a handsome ' . $pets[0]->getSpecies()->getName() . '! I\'m sure the spirals of their DNA are every bit as elegant!',
                'Ooh, ' . GrammarFunctions::indefiniteArticle($pets[0]->getSpecies()->getName()) . ' ' . $pets[0]->getSpecies()->getName() . '! Such a fascinating species - thank you for showing me one!',
            ]);

            $responseService->addFlashMessage('"' . $message . '"');
        }
        else if(count($pets) >= 5)
        {
            $message = $rng->rngNextFromArray([
                'You know how to make a zoologist\'s day!',
                'You\'re a regular Charles Darwin!',
                'Thank you! I\'ll get started immediately!',
                'Did I drink some Dreamwalker\'s Tea? This is unbelievable - thank you!',
            ]);

            $responseService->addFlashMessage('"' . count($pets) . ' species all at once?! ' . $message . '"');
        }
        else
            $responseService->addFlashMessage('"Wonderful! I\'ll get to work on these right away!"');

        return $responseService->success();
    }
}