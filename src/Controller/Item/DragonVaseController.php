<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetLocationEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Functions\GrammarFunctions;
use App\Functions\InventoryModifierFunctions;
use App\Model\PetChanges;
use App\Repository\EnchantmentRepository;
use App\Repository\InventoryRepository;
use App\Repository\PetRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/dragonVase")
 */
class DragonVaseController extends AbstractController
{
    /**
     * @Route("/{inventory}/smash", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function smash(
        Inventory $inventory, ResponseService $responseService, Squirrel3 $rng, PetRepository $petRepository,
        PetExperienceService $petExperienceService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'dragonVase/#/smash');

        $petsAtHome = $petRepository->findBy([
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

            $skill = $rng->rngNextFromArray([ PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);

            $description = $skill == PetSkillEnum::BRAWL ? 'pounced on' : 'bound';

            $petItem = $rng->rngNextFromArray([ 'Quintessence', 'Wings', 'Feathers' ]);

            if($petItem == 'Feathers')
                $activityLog = $responseService->createActivityLog($pet, ActivityHelpers::PetName($pet) . ' ' . $description . ' some Wings some ' . $petItem . ' that flew out of a Dragon Vase ' . $user->getName() . ' smashed, but accidentally reduced it to mere Feathers.', '');
            else
                $activityLog = $responseService->createActivityLog($pet, ActivityHelpers::PetName($pet) . ' ' . $description . ' some ' . $petItem . ' that flew out of a Dragon Vase ' . $user->getName() . ' smashed.', '');

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

    /**
     * @Route("/{inventory}/dip", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Squirrel3 $squirrel3,
        UserQuestRepository $userQuestRepository, Request $request, InventoryRepository $inventoryRepository,
        EnchantmentRepository $enchantmentRepository, UserStatsRepository $userStatsRepository
    )
    {
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'dragonVase');

        $itemId = $request->request->getInt('tool');

        $dippedItem = $inventoryRepository->findOneBy([
            'id' => $itemId,
            'owner' => $user,
            'location' => LocationEnum::HOME
        ]);

        if(!$dippedItem)
            throw new NotFoundHttpException('Could not find that item!? Reload, and try again...');

        if(!$dippedItem->getItem()->getTool())
            throw new UnprocessableEntityHttpException('That item is not a tool! Dipping it into the vase would accomplish NOTHING.');

        $today = (new \DateTimeImmutable())->format('Y-m-d');
        $usedDragonVase = $userQuestRepository->findOrCreate($user, 'Used Dragon Vase', (new \DateTimeImmutable())->modify('-1 day')->format('Y-m-d'));

        if($today === $usedDragonVase->getValue())
            throw new UnprocessableEntityHttpException('You already dipped something into a Dragon Vase today. You\'ll just have to wait for tomorrow!');

        $usedDragonVase->setValue($today);

        $dippingStat = $userStatsRepository->incrementStat($user, 'Tools Dipped in a Dragon Vase');

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

        $newBonus = $enchantmentRepository->findOneByName($squirrel3->rngNextFromArray($possibleBonuses));

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
