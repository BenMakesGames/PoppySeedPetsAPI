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
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetLocationEnum;
use App\Enum\PetSkillEnum;
use App\Enum\UserStatEnum;
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
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/dragonVase")]
class DragonVaseController extends AbstractController
{
    #[Route("/{inventory}/smash", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function smash(
        Inventory $inventory, ResponseService $responseService, IRandom $rng,
        PetExperienceService $petExperienceService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

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
            LocationEnum::HOME,
            false
        );

        $petNames = [];

        foreach($petsAtHome as $pet)
        {
            $changes = new PetChanges($pet);

            $skill = $rng->rngNextFromArray([ PetSkillEnum::BRAWL, PetSkillEnum::ARCANA ]);

            $description = $skill == PetSkillEnum::BRAWL ? 'pounced on' : 'bound';

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
                ->addInterestingness(PetActivityLogInterestingnessEnum::PLAYER_ACTION_RESPONSE)
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
    public function read(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, IRandom $rng,
        Request $request, UserStatsService $userStatsRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'dragonVase');

        $itemId = $request->request->getInt('tool', 0);

        if($itemId <= 0)
            throw new PSPFormValidationException('You forgot to select a tool!');

        $dippedItem = $em->getRepository(Inventory::class)->findOneBy([
            'id' => $itemId,
            'owner' => $user,
            'location' => LocationEnum::HOME
        ]);

        if(!$dippedItem)
            throw new PSPNotFoundException('Could not find that item!? Reload, and try again...');

        if(!$dippedItem->getItem()->getTool())
            throw new PSPInvalidOperationException('That item is not a tool! Dipping it into the vase would accomplish NOTHING.');

        $today = (new \DateTimeImmutable())->format('Y-m-d');
        $usedDragonVase = UserQuestRepository::findOrCreate($em, $user, 'Used Dragon Vase', (new \DateTimeImmutable())->modify('-1 day')->format('Y-m-d'));

        if($today === $usedDragonVase->getValue())
            throw new PSPInvalidOperationException('You already dipped something into a Dragon Vase today. You\'ll just have to wait for tomorrow!');

        $usedDragonVase->setValue($today);

        $dippingStat = $userStatsRepository->incrementStat($user, UserStatEnum::TOOLS_DIPPED_IN_A_DRAGON_VASE);

        // Dragon Vase-only bonuses
        $possibleBonuses = [
            'of Swords', 'of Mangoes', 'Climbing',
            'Blackened', 'Archaeopteryx'
        ];

        if($dippingStat->getValue() > 1)
        {
            // other bonuses:
            $possibleBonuses[] = 'Magpie\'s';
            $possibleBonuses[] = 'Medium-hot';
            $possibleBonuses[] = 'Piercing';
        }

        if($dippedItem->getEnchantment())
        {
            $possibleBonuses = array_filter($possibleBonuses, fn(string $bonus) =>
                $bonus !== $dippedItem->getEnchantment()->getName()
            );
        }

        $newBonus = EnchantmentRepository::findOneByName($em, $rng->rngNextFromArray($possibleBonuses));

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
