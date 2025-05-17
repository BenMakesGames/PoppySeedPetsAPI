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


namespace App\Controller\Item\Blueprint;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\PetActivityLogInterestingness;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Model\PetChanges;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Xoshiro;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class BlueprintHelpers
{
    public static function getPet(EntityManagerInterface $em, User $user, Request $request): Pet
    {
        $petId = $request->request->getInt('pet', 0);
        $pet = $em->getRepository(Pet::class)->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        return $pet;
    }

    public static function rewardHelper(
        PetExperienceService $petExperienceService, ResponseService $responseService,
        EntityManagerInterface $em, Pet $pet, ?string $skill, string $flashMessage, string $logMessage
    ): void
    {
        $rng = new Xoshiro();
        $changes = new PetChanges($pet);

        if($skill && $pet->getSkills()->getStat($skill) >= 20)
            $skill = null;

        $pet
            ->increaseLove($rng->rngNextInt(3, 6))
            ->increaseEsteem($rng->rngNextInt(2, 4))
        ;

        $petExperienceService->gainAffection($pet, 10);

        $responseService->addFlashMessage($flashMessage);

        $activityLog = PetActivityLogFactory::createUnreadLog($em, $pet, $logMessage)
            ->setIcon('ui/affection')
            ->setChanges($changes->compare($pet))
            ->addInterestingness(PetActivityLogInterestingness::RareActivity)
        ;

        if($skill)
        {
            $pet->getSkills()->increaseStat($skill);

            $activityLog
                ->addInterestingness(PetActivityLogInterestingness::LevelUp)
                ->setEntry($activityLog->getEntry() . ' +1 ' . ucfirst($skill) . '!')
                ->addTags(PetActivityLogTagHelpers::findByNames($em, [ 'Level-up' ]));
        }
    }
}
