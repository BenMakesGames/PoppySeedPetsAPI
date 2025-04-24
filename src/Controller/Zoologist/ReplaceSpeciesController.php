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
use App\Exceptions\PSPNotUnlockedException;
use App\Exceptions\PSPPetNotFoundException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/zoologist")]
class ReplaceSpeciesController extends AbstractController
{
    #[Route("/replaceEntry", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function replace(
        EntityManagerInterface $em, Request $request, ResponseService $responseService
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Zoologist))
            throw new PSPNotUnlockedException('Zoologist');

        $petId = $request->request->getInt('petId');

        if($petId <= 0)
            throw new PSPFormValidationException('No pets were selected.');

        $pet = $em->getRepository(Pet::class)->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        $alreadyDiscovered = $em->getRepository(UserSpeciesCollected::class)->findOneBy([
            'user' => $user->getId(),
            'species' => $pet->getSpecies()->getId(),
        ]);

        if(!$alreadyDiscovered)
            throw new PSPFormValidationException('You have not shown a pet of this species to the zoologist yet.');

        if(
            $alreadyDiscovered->getPetName() == $pet->getName() &&
            $alreadyDiscovered->getColorA() == $pet->getPerceivedColorA() &&
            $alreadyDiscovered->getColorB() == $pet->getPerceivedColorB() &&
            $alreadyDiscovered->getScale() == $pet->getScale()
        )
            throw new PSPFormValidationException('This exact pet is already in the zoologist\'s records.');

        $alreadyDiscovered
            ->setPetName($pet->getName())
            ->setColorA($pet->getPerceivedColorA())
            ->setColorB($pet->getPerceivedColorB())
            ->setScale($pet->getScale())
        ;

        $em->flush();

        $responseService->addFlashMessage('"Got it!"');

        return $responseService->success();
    }
}