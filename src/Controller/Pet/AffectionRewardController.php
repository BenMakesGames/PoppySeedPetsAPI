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

use App\Entity\Merit;
use App\Entity\Pet;
use App\Entity\SpiritCompanion;
use App\Enum\MeritEnum;
use App\Enum\PetSkillEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\ArrayFunctions;
use App\Functions\MeritFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\UserUnlockedFeatureHelpers;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/pet")]
class AffectionRewardController
{
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/{pet}/availableMerits", methods: ["GET"], requirements: ["pet" => "\d+"])]
    public function getAvailableMerits(
        Pet $pet, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        $merits = $em->getRepository(Merit::class)->findBy([ 'name' => MeritFunctions::getAvailableMerits($pet) ]);

        return $responseService->success($merits, [ SerializationGroupEnum::AVAILABLE_MERITS ]);
    }

    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/{pet}/chooseAffectionReward/merit", methods: ["POST"], requirements: ["pet" => "\d+"])]
    public function chooseAffectionRewardMerit(
        Pet $pet, Request $request, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if($pet->hasMerit(MeritEnum::AFFECTIONLESS))
            throw new PSPInvalidOperationException($pet->getName() . ' is Affectionless. It cannot gain Merits from affection.');

        if($pet->getAffectionRewardsClaimed() >= $pet->getAffectionLevel())
            throw new PSPInvalidOperationException('You\'ll have to raise ' . $pet->getName() . '\'s affection, first.');

        $meritName = $request->request->getString('merit');

        $availableMerits = $em->getRepository(Merit::class)->findBy([ 'name' => MeritFunctions::getAvailableMerits($pet) ]);

        /** @var Merit|null $merit */
        $merit = ArrayFunctions::find_one($availableMerits, fn(Merit $m) => $m->getName() === $meritName);

        if(!$merit)
            throw new PSPNotFoundException('That merit is not available.');

        $pet
            ->addMerit($merit)
            ->increaseAffectionRewardsClaimed()
        ;

        if($merit->getName() === MeritEnum::SPIRIT_COMPANION)
        {
            $spiritCompanion = new SpiritCompanion();

            $pet->setSpiritCompanion($spiritCompanion);

            $em->persist($spiritCompanion);
        }
        else if($merit->getName() === MeritEnum::VOLAGAMY)
        {
            $pet->setIsFertile(true);
        }

        PetActivityLogFactory::createUnreadLog($em, $pet, '%pet:' . $pet->getId() . '.name% took the "' . $merit->getName() . '" Merit at %user:' . $user->getId() . '.name\'s% suggestion.')
            ->setIcon('ui/merit-icon');

        // you should already unlock the merit when the pet increases in affection, but someone reported that
        // NOT happening, so just in case...
        if(!$pet->getOwner()->hasUnlockedFeature(UnlockableFeatureEnum::Park))
            UserUnlockedFeatureHelpers::create($em, $pet->getOwner(), UnlockableFeatureEnum::Park);

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }

    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/{pet}/chooseAffectionReward/skill", methods: ["POST"], requirements: ["pet" => "\d+"])]
    public function chooseAffectionRewardSkill(
        Pet $pet, Request $request, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor, PetExperienceService $petExperienceService
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if($pet->hasMerit(MeritEnum::AFFECTIONLESS))
            throw new PSPInvalidOperationException($pet->getName() . ' is Affectionless. It cannot gain Merits from affection.');

        if($pet->getAffectionRewardsClaimed() >= $pet->getAffectionLevel())
            throw new PSPInvalidOperationException('You\'ll have to raise ' . $pet->getName() . '\'s affection, first.');

        $skillName = $request->request->get('skill');

        if(!PetSkillEnum::isAValue($skillName))
            throw new PSPFormValidationException('"' . $skillName . '" is not a skill!');

        if($pet->getSkills()->getStat($skillName) >= 20)
            throw new PSPInvalidOperationException($pet->getName() . '\'s ' . $skillName . ' is already max!');

        $activityLog = PetActivityLogFactory::createUnreadLog($em, $pet, '%pet:' . $pet->getId() . '.name% trained hard in ' . $skillName . ' at %user:' . $user->getId() . '.name\'s% suggestion.')
            ->setIcon('ui/merit-icon');

        $petExperienceService->forceIncreaseSkill($pet, $skillName, 1, $activityLog);
        $pet->increaseAffectionRewardsClaimed();

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }
}
