<?php
namespace App\Controller\Inventory;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ArrayFunctions;
use App\Functions\GrammarFunctions;
use App\Functions\InventoryModifierFunctions;
use App\Repository\InventoryRepository;
use App\Service\CookingService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/inventory")]
class CookAndCombineController extends AbstractController
{
    #[Route("/prepare", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function prepareRecipe(
        Request $request, ResponseService $responseService, InventoryRepository $inventoryRepository,
        EntityManagerInterface $em, CookingService $cookingService, IRandom $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $inventoryIds = $request->request->all('inventory');

        if(count($inventoryIds) > 100)
            throw new PSPFormValidationException('Oh, goodness, please don\'t try to Cook or Combine more than 100 items at a time! (Sorry for the inconvenience...)');

        $inventory = $inventoryRepository->findBy([
            'owner' => $user,
            'id' => $inventoryIds
        ]);

        if(count($inventory) !== count($inventoryIds))
            throw new PSPNotFoundException('Some of the items could not be found??');

        if(count($inventory) === 0)
            throw new PSPFormValidationException('You gotta\' select at least ONE item!');

        if(!InventoryService::inventoryInSameLocation($inventory))
            throw new PSPFormValidationException('All of the items must be in the same location.');

        if(count($inventory) === 2)
        {
            /** @var Inventory $baseItem */
            $baseItem = ArrayFunctions::find_one($inventory, fn(Inventory $i) => $i->getId() === $inventoryIds[0]);

            /** @var Inventory $addOn */
            $addOn = ArrayFunctions::find_one($inventory, fn(Inventory $i) => $i->getId() === $inventoryIds[1]);

            // try enchanting
            $enchanted = null;

            if($baseItem->getItem()->getTool() && $addOn->getItem()->getEnchants())
            {
                InventoryModifierFunctions::enchant($em, $baseItem, $addOn);
                $enchanted = $baseItem;
            }
            else if($addOn->getItem()->getTool() && $baseItem->getItem()->getEnchants())
            {
                InventoryModifierFunctions::enchant($em, $addOn, $baseItem);
                $enchanted = $addOn;
            }

            if($enchanted)
            {
                $newName = InventoryModifierFunctions::getNameWithModifiers($enchanted);

                $responseService->addFlashMessage('The ' . $enchanted->getItem()->getName() . ' is now ' . GrammarFunctions::indefiniteArticle($newName) . ' ' . $newName . '!');

                $em->flush();

                $responseService->setReloadInventory();

                return $responseService->success($enchanted, [ SerializationGroupEnum::MY_INVENTORY ]);
            }

            // try spicing up
            $spiced = null;

            if($baseItem->getItem()->getFood() && $addOn->getItem()->getSpice())
            {
                InventoryModifierFunctions::spiceUp($em, $baseItem, $addOn);
                $spiced = $baseItem;
            }
            else if($addOn->getItem()->getFood() && $baseItem->getItem()->getSpice())
            {
                InventoryModifierFunctions::spiceUp($em, $addOn, $baseItem);
                $spiced = $addOn;
            }

            if($spiced)
            {
                $newName = InventoryModifierFunctions::getNameWithModifiers($spiced);

                $responseService->addFlashMessage('The ' . $spiced->getItem()->getName() . ' is now ' . GrammarFunctions::indefiniteArticle($newName) . ' ' . $newName . '!');

                $em->flush();

                $responseService->setReloadInventory();

                return $responseService->success($spiced, [ SerializationGroupEnum::MY_INVENTORY ]);
            }
        }

        $results = $cookingService->prepareRecipe($user, $inventory, true);

        // do this before checking if anything was made
        // because if NOTHING was made, a record in "RecipeAttempted" was made :P
        $em->flush();

        if($results === null)
            throw new PSPInvalidOperationException('You can\'t make anything with those ingredients.');

        $qList = [];
        $totalQuantity = 0;

        foreach($results->quantities as $q)
        {
            $qList[$q->item->getName()] = $q->quantity;
            $totalQuantity += $q->quantity;
        }

        if($totalQuantity < 4)
            $exclaim = '.';
        else if($totalQuantity < 10 || strpos($results->recipe['ingredients'], ',') === false)
            $exclaim = '!';
        else
            $exclaim = '! (' . $squirrel3->rngNextFromArray([ 'Dang!', 'Wow!', 'Incredible...', 'So cook! Very meal!', 'A veritable feast!', 'Such skill!' ]) . ')';

        $responseService->addFlashMessage('You prepared ' . ArrayFunctions::list_nice_quantities($qList) . $exclaim);

        return $responseService->success($results->inventory, [ SerializationGroupEnum::MY_INVENTORY ]);
    }
}
