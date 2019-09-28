<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Functions\ArrayFunctions;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/wandOfWonder")
 */
class WandOfWonderController extends PsyPetsItemController
{
    /**
     * @Route("/{inventory}/point", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(
        Inventory $inventory, ResponseService $responseService, UserQuestRepository $userQuestRepository,
        EntityManagerInterface $em, InventoryService $inventoryService
    )
    {
        $this->validateInventory($inventory, 'wandOfWonder/#/point');

        $user = $this->getUser();
        $location = $inventory->getLocation();

        $expandedGreenhouseWithWand = $userQuestRepository->findOrCreate($user, 'Expanded Greenhouse With Wand of Wonder', false);

        $itemActionDescription = null;
        $itemActionEffects = [];

        $possibleEffects = [
            'song',
            'featherStorm',
            'butterflies',
            'oneMoney',
        ];

        if($user->getUnlockedGreenhouse() && !$expandedGreenhouseWithWand->getValue())
            $possibleEffects[] = 'expandGreenhouse';

        $effect = ArrayFunctions::pick_one($possibleEffects);

        switch($effect)
        {
            case 'song':
                $notes = mt_rand(6, 10);
                $itemActionDescription = "The wand begins to sing.\n\nThen, it keeps singing.\n\nS-- still singi-- oh, wait, no, it's stoppe-- ah, never mind, just a pause.\n\nStiiiiiiiill going...\n\nOh, okay, it's stopped again. Is it for real this time?\n\nIt seems to be for real.\n\nYeah, okay, it's done.\n\nYou shake your head; " . $notes . " Music Notes fall out of your ears and clatter on the ground!\n\nFrickin' wand!";

                for($x = 0; $x < $notes; $x++)
                    $inventoryService->receiveItem('Music Note', $user, $user, 'These Music Notes fell out of your ears after a Wand of Wonder sang for a while.', $location);

                $itemActionEffects['reloadInventory'] = true;

                break;

            case 'featherStorm':
                $feathers = mt_rand(8, 12);
                $itemActionDescription = 'Hundreds of Feathers stream from the wand, filling the room. You never knew Feathers could be so loud! Moments later they begin to escape through crevices in the wall, but not before you grab a few!';

                for($x = 0; $x < $feathers; $x++)
                    $inventoryService->receiveItem('Feathers', $user, $user, 'A Wand of Wonder summoned these Feathers.', $location);

                $itemActionEffects['reloadInventory'] = true;

                break;

            case 'butterflies':
                $itemActionDescription = 'Hundreds of butterflies stream from the wand, filling the room. You never knew butterflies could be so loud! Moments later they escape through crevices in the wall, leaving no trace.';
                break;

            case 'expandGreenhouse':
                $itemActionDescription = 'You hear the earth shift in your Greenhouse! WHAT COULD IT MEAN!?!?';
                $user->increaseMaxPlants(1);
                $expandedGreenhouseWithWand->setValue(true);
                break;

            case 'oneMoney':
                $itemActionDescription = 'The wand begins to glow and shake violently. You hold on with all your might until, at last, it coughs up a single ~~m~~. (Lame!)';
                $user->increaseMoneys(1);
                break;
        }

        if(mt_rand(1, 10) === 1)
        {
            $em->remove($inventory);

            $itemActionDescription .= "\n\n";

            if(mt_rand(1, 2) === 1)
            {
                $itemActionDescription .= 'Afterwards, the wand snapped in two and crumbled to dust. Well, actually, it crumbled to Silica Grounds.';
                $inventoryService->receiveItem('Silica Grounds', $user, $user, 'These Silica Grounds were once a Wand of Wonder. Now they\'re just Silica Grounds. (Sorry, I guess that was a little redundant...)', $location);
            }
            else
            {
                $itemActionDescription .= 'Afterwards, the wand burst into flames, as was reduced to Charcoal.';
                $inventoryService->receiveItem('Charcoal', $user, $user, 'The charred remains of a Wand of Wonder :|', $location);
            }

            $itemActionEffects['itemDeleted'] = true;
            $itemActionEffects['reloadInventory'] = true;
        }

        $em->flush();

        return $responseService->itemActionSuccess($itemActionDescription, $itemActionEffects);
    }
}