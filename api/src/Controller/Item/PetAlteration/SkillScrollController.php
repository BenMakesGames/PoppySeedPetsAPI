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
use App\Enum\PetSkillEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\PetActivityLogFactory;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item")]
class SkillScrollController
{
    #[Route("/brawlSkillScroll/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function increaseBrawl(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        UserAccessor $userAccessor, PetExperienceService $petExperienceService
    ): JsonResponse
    {
        return $this->doSkillScroll($inventory, $request, $em, $responseService, PetSkillEnum::Brawl, $userAccessor, $petExperienceService);
    }

    #[Route("/craftsSkillScroll/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function increaseCrafts(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        UserAccessor $userAccessor, PetExperienceService $petExperienceService
    ): JsonResponse
    {
        return $this->doSkillScroll($inventory, $request, $em, $responseService, PetSkillEnum::Crafts, $userAccessor, $petExperienceService);
    }

    #[Route("/musicSkillScroll/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function increaseMusic(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        UserAccessor $userAccessor, PetExperienceService $petExperienceService
    ): JsonResponse
    {
        return $this->doSkillScroll($inventory, $request, $em, $responseService, PetSkillEnum::Music, $userAccessor, $petExperienceService);
    }

    #[Route("/natureSkillScroll/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function increaseNature(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        UserAccessor $userAccessor, PetExperienceService $petExperienceService
    ): JsonResponse
    {
        return $this->doSkillScroll($inventory, $request, $em, $responseService, PetSkillEnum::Nature, $userAccessor, $petExperienceService);
    }

    #[Route("/scienceSkillScroll/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function increaseScience(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        UserAccessor $userAccessor, PetExperienceService $petExperienceService
    ): JsonResponse
    {
        return $this->doSkillScroll($inventory, $request, $em, $responseService, PetSkillEnum::Science, $userAccessor, $petExperienceService);
    }

    #[Route("/stealthSkillScroll/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function increaseStealth(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        UserAccessor $userAccessor, PetExperienceService $petExperienceService
    ): JsonResponse
    {
        return $this->doSkillScroll($inventory, $request, $em, $responseService, PetSkillEnum::Stealth, $userAccessor, $petExperienceService);
    }

    #[Route("/arcanaSkillScroll/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function increaseArcana(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        UserAccessor $userAccessor, PetExperienceService $petExperienceService
    ): JsonResponse
    {
        return $this->doSkillScroll($inventory, $request, $em, $responseService, PetSkillEnum::Arcana, $userAccessor, $petExperienceService);
    }

    private function doSkillScroll(
        Inventory $inventory, Request $request, EntityManagerInterface $em, ResponseService $responseService, string $skill,
        UserAccessor $userAccessor, PetExperienceService $petExperienceService
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, $skill . 'SkillScroll');

        if(!PetSkillEnum::isAValue($skill))
            throw new PSPFormValidationException('Not a valid skill.');

        $petId = $request->request->getInt('pet', 0);
        $pet = $em->getRepository(Pet::class)->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if($pet->getSkills()->getStat($skill) >= 20)
            throw new PSPInvalidOperationException($pet->getName() . ' already has 20 points of ' . $skill . '! It doesn\'t get higher than that!');

        $em->remove($inventory);

        $activityLog = PetActivityLogFactory::createUnreadLog($em, $pet, '%pet:' . $pet->getId() . '.name% was read ' . $inventory->getItem()->getNameWithArticle() . '...')
            ->setIcon('items/scroll/skill/' . $skill);

        $petExperienceService->forceIncreaseSkill($pet, $skill, 1, $activityLog);

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
