<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Functions\GrammarFunctions;
use App\Model\PetChanges;
use App\Repository\EnchantmentRepository;
use App\Repository\InventoryRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\ResponseService;
use App\Service\ToolBonusService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/item/dragonVase")
 */
class DragonVaseController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/dip", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        UserQuestRepository $userQuestRepository, Request $request, InventoryRepository $inventoryRepository,
        EnchantmentRepository $enchantmentRepository, ToolBonusService $toolBonusService,
        UserStatsRepository $userStatsRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'dragonVase');

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

        $newEnchantment = $enchantmentRepository->findOneByName(ArrayFunctions::pick_one([
            'Magpie\'s', 'Medium-hot', 'Piercing',
            'of Swords', 'of Mangoes', 'Climbing',
            'Blackened', 'Archaeopteryx'
        ]));

        $userStatsRepository->incrementStat($user, 'Tools Dipped in a Dragon Vase');

        $hadAnEnchantment = $dippedItem->getEnchantment() !== null;
        $oldName = $toolBonusService->enchantedName($dippedItem);

        $dippedItem
            ->setEnchantment($newEnchantment)
            ->addComment('This item was enchanted in the waters of a Dragon Vase.')
        ;

        $newName = $toolBonusService->enchantedName($dippedItem);

        $em->flush();

        if($hadAnEnchantment)
            $responseService->addFlashMessageString('The ' . $oldName . '\'s bonus was replaced! It is now ' . GrammarFunctions::indefiniteArticle($newName) . ' ' . $newName . '!');
        else
            $responseService->addFlashMessageString('The ' . $oldName . ' has been enchanted! It is now ' . GrammarFunctions::indefiniteArticle($newName) . ' ' . $newName . '!');

        return $responseService->success();
    }
}
