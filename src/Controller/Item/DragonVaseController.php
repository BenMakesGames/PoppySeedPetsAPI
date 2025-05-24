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


namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetLocationEnum;
use App\Enum\PetSkillEnum;
use App\Enum\UserStat;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Functions\EnchantmentRepository;
use App\Functions\GrammarFunctions;
use App\Functions\InventoryModifierFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\UserQuestRepository;
use App\Model\PetChanges;
use App\Service\Clock;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/dragonVase")]
class DragonVaseController
{
    #[Route("/{inventory}/smash", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function smash(
        Inventory $inventory, ResponseService $responseService, IRandom $rng,
        PetExperienceService $petExperienceService, InventoryService $inventoryService,
        EntityManagerInterface $em, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'dragonVase/#/smash');

        $petsAtHome = $em->getRepository(Pet::class)->findBy([
            'owner' => $user,
            'location' => PetLocationEnum::HOME
        ]);

        $yourItem = $rng->rngNextFromArray([ 'Quintessence', 'Wings' ]);

        $inventoryService->receiveItem(
            $yourItem,
            $user,
            $user,
            $user->getName() . ' caught this as it escaped a Dragon Vase they smashed.',
            LocationEnum::Home,
            false
        );

        $petNames = [];

        foreach($petsAtHome as $pet)
        {
            $changes = new PetChanges($pet);

            $skill = $rng->rngNextFromArray([ PetSkillEnum::Brawl, PetSkillEnum::Arcana ]);

            $description = $skill == PetSkillEnum::Brawl ? 'pounced on' : 'bound';

            $petItem = $rng->rngNextFromArray([ 'Quintessence', 'Wings', 'Feathers' ]);

            if($petItem == 'Feathers')
                $activityLog = PetActivityLogFactory::createUnreadLog($em, $pet, ActivityHelpers::PetName($pet) . ' ' . $description . ' some ' . $petItem . ' that flew out of a Dragon Vase ' . $user->getName() . ' smashed, but accidentally reduced it to mere Feathers.');
            else
                $activityLog = PetActivityLogFactory::createUnreadLog($em, $pet, ActivityHelpers::PetName($pet) . ' ' . $description . ' some ' . $petItem . ' that flew out of a Dragon Vase ' . $user->getName() . ' smashed.');

            $inventoryService->petCollectsItem(
                $petItem,
                $pet,
                $pet->getName() . ' ' . $description . ' this as it escaped a Dragon Vase ' . $user->getName() . ' smashed.',
                $activityLog
            );

            $petExperienceService->gainExp($pet, 1, [ $skill ], $activityLog);

            $activityLog
                ->setChanges($changes->compare($pet))
                ->addInterestingness(PetActivityLogInterestingness::PlayerActionResponse)
            ;

            $petNames[] = $pet->getName();
        }

        $em->remove($inventory);

        $em->flush();

        if(count($petNames) > 0)
            return $responseService->itemActionSuccess('You smashed the Dragon Vase, and caught some ' . $yourItem . ' before it escaped; ' . ArrayFunctions::list_nice($petNames) . ' helped catch some, too!', [ 'itemDeleted' => true ]);
        else
            return $responseService->itemActionSuccess('You smashed the Dragon Vase, and caught some ' . $yourItem . ' before it escaped.', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/dip", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function dipATool(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        UserStatsService $userStatsRepository, UserAccessor $userAccessor, Clock $clock
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'dragonVase');

        $itemId = $request->request->getInt('tool', 0);

        if($itemId <= 0)
            throw new PSPFormValidationException('You forgot to select a tool!');

        $dippedItem = $em->getRepository(Inventory::class)->findOneBy([
            'id' => $itemId,
            'owner' => $user,
            'location' => LocationEnum::Home
        ]);

        if(!$dippedItem)
            throw new PSPNotFoundException('Could not find that item!? Reload, and try again...');

        if(!$dippedItem->getItem()->getTool())
            throw new PSPInvalidOperationException('That item is not a tool! Dipping it into the vase would accomplish NOTHING.');

        $today = (new \DateTimeImmutable())->format('Y-m-d');
        $usedDragonVase = UserQuestRepository::findOrCreate($em, $user, 'Used Dragon Vase', (new \DateTimeImmutable())->modify('-1 day')->format('Y-m-d'));

        if($today === $usedDragonVase->getValue())
            throw new PSPInvalidOperationException('You already dipped something into a Dragon Vase today. You\'ll just have to wait for tomorrow!');

        // most of these bonuses are unique to the Dragon Vase - "Magpie's" being the exception
        $bonus = match($clock->now->format('N')) {
            '1' => 'Blackened', // Monday - Selene, the goddess of the moon
            '2' => 'of Swords', // Tuesday - Ares, the god of war
            '3' => 'Climbing', // Wednesday - Hermes, the messenger god
            '4' => 'Magpie\'s', // Thursday - Zeus, the king of the gods
            '5' => 'Glycyrrhiza', // Friday - Aphrodite, the goddess of love
            '6' => 'Archaeopteryx', // Saturday - Cronus, the god of time
            '7' => 'of Mangoes', // Sunday - Helios, the sun god
        };

        if($dippedItem->getEnchantment() && $dippedItem->getEnchantment()->getName() === $bonus)
        {
            $responseService->addFlashMessage('The ' . InventoryModifierFunctions::getNameWithModifiers($dippedItem) . ' already has the ' . $bonus . ' bonus!');

            return $responseService->success();
        }

        $usedDragonVase->setValue($today);

        $userStatsRepository->incrementStat($user, UserStat::ToolsDippedInADragonVase);

        $newBonus = EnchantmentRepository::findOneByName($em, $bonus);

        $hadAnEnchantment = $dippedItem->getEnchantment() !== null;
        $oldName = InventoryModifierFunctions::getNameWithModifiers($dippedItem);

        $dippedItem
            ->setEnchantment($newBonus)
            ->addComment('This item gained "' . $newBonus->getName() . '" from a Dragon Vase.')
        ;

        $newName = InventoryModifierFunctions::getNameWithModifiers($dippedItem);

        $em->flush();

        if($hadAnEnchantment)
            $responseService->addFlashMessage('The ' . $oldName . '\'s bonus was replaced! It is now ' . GrammarFunctions::indefiniteArticle($newName) . ' ' . $newName . '!');
        else
            $responseService->addFlashMessage('The ' . $oldName . ' has been enchanted! It is now ' . GrammarFunctions::indefiniteArticle($newName) . ' ' . $newName . '!');

        return $responseService->success();
    }
}
