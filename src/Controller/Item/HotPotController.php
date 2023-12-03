<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\GrammarFunctions;
use App\Functions\InventoryModifierFunctions;
use App\Repository\InventoryRepository;
use App\Repository\SpiceRepository;
use App\Repository\UserQuestRepository;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/hotPot")
 */
class HotPotController extends AbstractController
{
    #[Route("/{inventory}/dip", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, IRandom $squirrel3,
        UserQuestRepository $userQuestRepository, Request $request, InventoryRepository $inventoryRepository,
        UserStatsService $userStatsRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'hotPot');

        $itemId = $request->request->getInt('food', 0);

        if($itemId <= 0)
            throw new PSPFormValidationException('You forgot to select a food!');

        $dippedItem = $inventoryRepository->findOneBy([
            'id' => $itemId,
            'owner' => $user,
            'location' => LocationEnum::HOME
        ]);

        if(!$dippedItem)
            throw new PSPNotFoundException('Could not find that item!? Reload, and try again...');

        if(!$dippedItem->getItem()->getFood())
            throw new PSPInvalidOperationException('That item is not a food! Dipping it into the Hot Pot would accomplish NOTHING.');

        $today = (new \DateTimeImmutable())->format('Y-m-d');
        $usedHotPot = $userQuestRepository->findOrCreate($user, 'Used Hot Pot', (new \DateTimeImmutable())->modify('-1 day')->format('Y-m-d'));

        if($today === $usedHotPot->getValue())
            throw new PSPInvalidOperationException('You already dipped something into a Hot Pot today. You\'ll just have to wait for tomorrow!');

        $usedHotPot->setValue($today);

        $dippingStat = $userStatsRepository->incrementStat($user, UserStatEnum::FOODS_DIPPED_IN_A_HOT_POT);

        // Hot Pot-only spices
        $possibleSpices = [
            'Sichuan', 'Salty', 'Meaty',
            '5-Spice\'d', 'with Sesame Seeds'
        ];

        if($dippingStat->getValue() > 1)
        {
            // other spices:
            $possibleSpices[] = 'Spicy';
            $possibleSpices[] = 'Onion\'d';
            $possibleSpices[] = 'Fishy';
        }

        if($dippedItem->getSpice())
        {
            $possibleSpices = array_filter($possibleSpices, fn(string $bonus) =>
                $bonus !== $dippedItem->getSpice()->getName()
            );
        }

        $newSpice = SpiceRepository::findOneByName($em, $squirrel3->rngNextFromArray($possibleSpices));

        $hadASpice = $dippedItem->getSpice() !== null;
        $oldName = InventoryModifierFunctions::getNameWithModifiers($dippedItem);

        $dippedItem
            ->setSpice($newSpice)
            ->addComment('This item gained "' . $newSpice->getName() . '" from a Hot Pot.')
        ;

        $newName = InventoryModifierFunctions::getNameWithModifiers($dippedItem);

        $em->flush();

        if($hadASpice)
            $responseService->addFlashMessage('The ' . $oldName . '\'s spice was replaced! It is now ' . GrammarFunctions::indefiniteArticle($newName) . ' ' . $newName . '!');
        else
            $responseService->addFlashMessage('The ' . $oldName . ' has been spiced! It is now ' . GrammarFunctions::indefiniteArticle($newName) . ' ' . $newName . '!');

        return $responseService->success();
    }
}
