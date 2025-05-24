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


namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Enum\MeritEnum;
use App\Enum\PetSkillEnum;
use App\Enum\UserStat;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\EquipmentFunctions;
use App\Functions\MeritFunctions;
use App\Functions\MeritRepository;
use App\Functions\PetActivityLogFactory;
use App\Model\MeritInfo;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/forgettingScroll")]
class ForgettingScrollController
{
    #[Route("/{inventory}/forgettableThings", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getForgettableThings(
        Inventory $inventory, ResponseService $responseService, Request $request, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'forgettingScroll');

        $petId = $request->query->getInt('pet', 0);
        $pet = $em->getRepository(Pet::class)->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        $unlearnableSkills = array_values(array_filter(PetSkillEnum::getValues(), fn(string $skill) =>
            $pet->getSkills()->getStat($skill) > 0
        ));

        $unlearnableMerits = MeritFunctions::getUnlearnableMerits($pet);

        if(count($unlearnableSkills) === 0 && count($unlearnableMerits) === 0)
            throw new PSPInvalidOperationException('There are no skills or merits that ' . $pet->getName() . ' can forget!');

        $data = [
            'merits' => $unlearnableMerits,
            'skills' => $unlearnableSkills,
        ];

        return $responseService->success($data);
    }

    #[Route("/{inventory}/forgetMerit", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function forgetMerit(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        UserStatsService $userStatsRepository, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'forgettingScroll');

        $petId = $request->request->getInt('pet', 0);
        $pet = $em->getRepository(Pet::class)->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        $meritName = $request->request->getString('merit');
        $merit = MeritRepository::findOneByName($em, $meritName);

        if(!$pet->hasMerit($merit->getName()))
            throw new PSPNotFoundException($pet->getName() . ' doesn\'t have that Merit.');

        if(!in_array($merit->getName(), MeritFunctions::getUnlearnableMerits($pet)))
        {
            if($merit->getName() === MeritEnum::VOLAGAMY)
                throw new PSPInvalidOperationException('That merit cannot be unlearned while ' . $pet->getName() . ' ' . ($pet->getSpecies()->getEggImage() ? 'has an egg' : 'is pregnant') . '.');
            else
                throw new PSPInvalidOperationException('That merit cannot be unlearned.');
        }

        $userStatsRepository->incrementStat($user, UserStat::ReadAScroll);

        $em->remove($inventory);

        $pet->removeMerit($merit);

        PetActivityLogFactory::createUnreadLog($em, $pet, '%pet:' . $pet->getId() . '.name% has forgotten the "' . $merit->getName() . '" Merit!')
            ->setIcon('items/scroll/unlearning');

        if(in_array($merit->getName(), MeritInfo::AFFECTION_REWARDS))
            $pet->decreaseAffectionRewardsClaimed();

        if($merit->getName() === MeritEnum::BEHATTED)
        {
            if($pet->getHat())
            {
                EquipmentFunctions::unhatPet($pet);

                $responseService->addFlashMessage($pet->getName() . '\'s hat falls to the ground.');
            }
        }
        else if($merit->getName() === MeritEnum::SPIRIT_COMPANION)
        {
            if($pet->getSpiritCompanion())
            {
                $responseService->addFlashMessage($pet->getSpiritCompanion()->getName() . ' fades away...');

                if($pet->getSpiritCompanion()->getFatheredPets()->count() == 0)
                    $em->remove($pet->getSpiritCompanion());

                $pet->setSpiritCompanion(null);
            }
        }
        else if($merit->getName() === MeritEnum::VOLAGAMY)
        {
            $pet->setIsFertile(false);
        }
        else if($merit->getName() === MeritEnum::AFFECTIONLESS)
        {
            $pet->getHouseTime()->setSocialEnergy(0);
        }

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/forgetSkill", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function forgetSkill(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        UserStatsService $userStatsRepository, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'forgettingScroll');

        $petId = $request->request->getInt('pet', 0);
        $pet = $em->getRepository(Pet::class)->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        $skill = $request->request->getString('skill');

        if(!PetSkillEnum::isAValue($skill))
            throw new PSPFormValidationException('You gotta\' select a skill to forget!');

        if($pet->getSkills()->getStat($skill) < 1)
            throw new PSPInvalidOperationException($pet->getName() . ' does not have any points of ' . $skill . ' to unlearn.');

        $userStatsRepository->incrementStat($user, UserStat::ReadAScroll);

        $em->remove($inventory);

        $pet->getSkills()->decreaseStat($skill);

        PetActivityLogFactory::createUnreadLog($em, $pet, '%pet:' . $pet->getId() . '.name% has forgotten some details about ' . ucfirst($skill) . '!')
            ->setIcon('items/scroll/unlearning');

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
