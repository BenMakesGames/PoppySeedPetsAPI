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


namespace App\Controller\Pet;

use App\Entity\Pet;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\MeritRepository;
use App\Functions\PetActivityLogFactory;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/pet")]
class TalentAndExpertiseController
{
    #[Route("/{pet}/pickTalent", requirements: ["pet" => "\d+"], methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function pickTalent(
        Pet $pet, Request $request, ResponseService $responseService, EntityManagerInterface $em, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        if($pet->getOwner()->getId() !== $userAccessor->getUserOrThrow()->getId())
            throw new PSPPetNotFoundException();

        if($pet->getCanPickTalent() !== 'talent')
            throw new PSPInvalidOperationException('This pet is not ready to have a talent picked.');

        $talent = $request->request->getString('talent');

        if(!in_array($talent, [ MeritEnum::MIND_OVER_MATTER, MeritEnum::MATTER_OVER_MIND, MeritEnum::MODERATION ]))
            throw new PSPFormValidationException('You gotta\' choose one of the talents!');

        $merit = MeritRepository::findOneByName($em, $talent);

        $pet->addMerit($merit);

        if($talent === MeritEnum::MIND_OVER_MATTER)
        {
            $pet->getSkills()
                ->increaseStat('intelligence')
                ->increaseStat('perception')
                ->increaseStat('dexterity')

                ->increaseStat($rng->rngNextFromArray([ 'intelligence', 'perception' ]))
                ->increaseStat($rng->rngNextFromArray([ 'intelligence', 'perception', 'dexterity' ]))
            ;
        }
        else if($talent === MeritEnum::MATTER_OVER_MIND)
        {
            $pet->getSkills()
                ->increaseStat('strength')
                ->increaseStat('stamina')
                ->increaseStat('dexterity')

                ->increaseStat($rng->rngNextFromArray([ 'strength', 'stamina' ]))
                ->increaseStat($rng->rngNextFromArray([ 'strength', 'stamina', 'dexterity' ]))
            ;
        }
        else if($talent === MeritEnum::MODERATION)
        {
            $pet->getSkills()
                ->increaseStat('strength')
                ->increaseStat('stamina')
                ->increaseStat('dexterity')
                ->increaseStat('intelligence')
                ->increaseStat('perception')
            ;
        }

        $pet->getSkills()->setTalent();

        PetActivityLogFactory::createUnreadLog($em, $pet, str_replace('%pet.name%', $pet->getName(), $merit->getDescription()))
            ->addInterestingness(PetActivityLogInterestingness::LevelUp)
        ;

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }

    #[Route("/{pet}/pickExpertise", requirements: ["pet" => "\d+"], methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function pickExpertise(
        Pet $pet, Request $request, ResponseService $responseService, EntityManagerInterface $em, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        if($pet->getOwner()->getId() !== $userAccessor->getUserOrThrow()->getId())
            throw new PSPPetNotFoundException();

        if($pet->getCanPickTalent() !== 'expertise')
            throw new PSPInvalidOperationException('This pet is not ready to have a talent picked.');

        $expertise = $request->request->getString('expertise');

        if(!in_array($expertise, [ MeritEnum::FORCE_OF_WILL, MeritEnum::FORCE_OF_NATURE, MeritEnum::BALANCE ]))
            throw new PSPFormValidationException('You gotta\' choose one of the talents!');

        $merit = MeritRepository::findOneByName($em, $expertise);

        $pet->addMerit($merit);

        if($expertise === MeritEnum::FORCE_OF_WILL)
        {
            $pet->getSkills()
                ->increaseStat('intelligence')
                ->increaseStat('perception')
                ->increaseStat('dexterity')

                ->increaseStat($rng->rngNextFromArray([ 'intelligence', 'perception' ]))
                ->increaseStat($rng->rngNextFromArray([ 'intelligence', 'perception', 'dexterity' ]))
            ;
        }
        else if($expertise === MeritEnum::FORCE_OF_NATURE)
        {
            $pet->getSkills()
                ->increaseStat('strength')
                ->increaseStat('stamina')
                ->increaseStat('dexterity')

                ->increaseStat($rng->rngNextFromArray([ 'strength', 'stamina' ]))
                ->increaseStat($rng->rngNextFromArray([ 'strength', 'stamina', 'dexterity' ]))
            ;
        }
        else if($expertise === MeritEnum::BALANCE)
        {
            $pet->getSkills()
                ->increaseStat('strength')
                ->increaseStat('stamina')
                ->increaseStat('dexterity')
                ->increaseStat('intelligence')
                ->increaseStat('perception')
            ;
        }

        $pet->getSkills()->setExpertise();

        PetActivityLogFactory::createUnreadLog($em, $pet, str_replace('%pet.name%', $pet->getName(), $merit->getDescription()))
            ->addInterestingness(PetActivityLogInterestingness::LevelUp)
        ;

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }
}
