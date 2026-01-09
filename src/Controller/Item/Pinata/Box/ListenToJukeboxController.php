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

namespace App\Controller\Item\Pinata\Box;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Enum\LocationEnum;
use App\Enum\PetLocationEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\UserQuestRepository;
use App\Model\PetChanges;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ListenToJukeboxController
{
    #[Route("/item/box/jukebox/{inventory}/listen", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function listenToJukebox(
        Inventory $inventory, ResponseService $responseService, PetExperienceService $petExperienceService,
        EntityManagerInterface $em, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'box/jukebox/#/listen');

        $today = (new \DateTimeImmutable())->format('Y-m-d');
        $listenedToJukebox = UserQuestRepository::findOrCreate($em, $user, 'Listened to Jukebox', (new \DateTimeImmutable())->modify('-1 day')->format('Y-m-d'));

        if($today === $listenedToJukebox->getValue())
            return $responseService->itemActionSuccess('You already listened to the Jukebox today. (Everyone knows that Jukeboxes can only be listened to once per day! Everyone!)');

        if($inventory->getLocation() !== LocationEnum::Home && $inventory->getLocation() !== LocationEnum::Mantle)
            return $responseService->itemActionSuccess('For maximum effect, the Jukebox should be played somewhere your pets can hear it!');

        $listenedToJukebox->setValue($today);

        $pets = $em->getRepository(Pet::class)->findBy([
            'owner' => $user->getId(),
            'location' => PetLocationEnum::HOME
        ]);

        if(count($pets) === 0)
            return $responseService->itemActionSuccess('For maximum effect, the Jukebox should be played with pets around to hear it!');

        $petNames = [];

        foreach($pets as $pet)
        {
            $petNames[] = $pet->getName();
            $changes = new PetChanges($pet);

            $pet->increaseSafety(2);

            $activityLog = PetActivityLogFactory::createUnreadLog($em, $pet, $pet->getName() . ' listened to the Jukebox.');

            $petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Music ], $activityLog);

            $activityLog->setChanges($changes->compare($pet));
        }

        $em->flush();

        return $responseService->itemActionSuccess(ArrayFunctions::list_nice($petNames) . ' enjoyed listening to the Jukebox!');
    }
}
