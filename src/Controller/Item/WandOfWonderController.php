<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Functions\ArrayFunctions;
use App\Functions\ColorFunctions;
use App\Repository\PetRepository;
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
    public function pointWandOfWonder(
        Inventory $inventory, ResponseService $responseService, UserQuestRepository $userQuestRepository,
        EntityManagerInterface $em, InventoryService $inventoryService, PetRepository $petRepository
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
            'yellowDye',
            'recolorAPet',
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

            case 'yellowDye':
                $dye = mt_rand(4, mt_rand(6, 10));
                $itemActionDescription = "Is that-- oh god! The wand is peeing!?\n\nWait, no... it\'s... Yellow Dye??!\n\nYou find some small jars to catch the stuff; in the end, you get " . $dyes . " Yellow Dye.";

                for($x = 0; $x < $dye; $x++)
                    $inventoryService->receiveItem('Yellow Dye', $user, $user, 'A Wand of Wonder, uh, _summoned_ this Yellow Dye.', $location);

                break;

            case 'recolorAPet':
                $petToRecolor = $this->pickRandomPetAtLocation($petRepository, $location, $user);

                if($petToRecolor)
                {
                    $this->recolorPet($petToRecolor);

                    $itemActionDescription = "A rainbow of colors swirl out of the wand and around " . $petToRecolor->getName() . ", whose colors change before your eyes!";
                }
                else
                {
                    $itemActionDescription = "A rainbow of colors swirl out of the wand and around the room, as if looking for something.\n\nAfter a moment, the colors fade away. If they really were looking for something, it seems they didn't find it.";
                }

                break;
        }

        if(mt_rand(1, 10) === 1)
        {
            $em->remove($inventory);

            $itemActionDescription .= "\n\n";

            $remains = mt_rand(1, 4);

            if($remains === 1)
            {
                $itemActionDescription .= 'Then, the wand snaps in two and crumbles to dust. Well, actually, it crumbles to Silica Grounds.';
                $inventoryService->receiveItem('Silica Grounds', $user, $user, 'These Silica Grounds were once a Wand of Wonder. Now they\'re just Silica Grounds. (Sorry, I guess that was a little redundant...)', $location);
            }
            else if($remains === 2)
            {
                $itemActionDescription .= 'Then, the wand burst into flames, and is reduced to Charcoal.';
                $inventoryService->receiveItem('Charcoal', $user, $user, 'The charred remains of a Wand of Wonder :|', $location);
            }
            else // $remains 3 || 4
            {
                $itemActionDescription .= 'You feel the last bits of magic drain from the wand. It\'s now nothing more than a common, Crooked Stick.';
                $inventoryService->receiveItem('Crooked Stick', $user, $user, 'The mundane remains of a Wand of Wonder...', $location);
            }

            $itemActionEffects['itemDeleted'] = true;
            $itemActionEffects['reloadInventory'] = true;
        }

        $em->flush();

        return $responseService->itemActionSuccess($itemActionDescription, $itemActionEffects);
    }

    private function pickRandomPetAtLocation(PetRepository $petRepository, string $location, User $user): ?Pet
    {
        $pet = null;

        if($location === LocationEnum::HOME)
        {
            $pets = $petRepository->findBy([
                'owner' => $user->getId(),
                'inDaycare' => false
            ]);

            if(count($pets) > 0)
                $pet = ArrayFunctions::pick_one($pets);
        }

        return $pet;
    }

    private function recolorPet(Pet $pet)
    {
        $h = mt_rand(0, 1000) / 1000.0;
        $s = mt_rand(mt_rand(0, 500), 1000) / 1000.0;
        $l = mt_rand(mt_rand(0, 500), mt_rand(750, 1000)) / 1000.0;

        $strategy = mt_rand(1, 100);

        $h2 = $h;
        $s2 = $s;
        $l2 = $l;

        if($strategy <= 35)
        {
            // complementary color
            $h2 = $h2 + 0.5;
            if($h2 > 1) $h2 -= 1;

            if(mt_rand(1, 2) === 1)
            {
                if($s < 0.5)
                    $s2 = $s * 2;
                else
                    $s2 = $s / 2;
            }
        }
        else if($strategy <= 70)
        {
            // different luminosity
            if($l2 <= 0.5)
                $l2 += 0.5;
            else
                $l2 -= 0.5;
        }
        else if($strategy <= 90)
        {
            // black & white
            if($l < 0.3333)
                $l2 = mt_rand(850, 1000) / 1000.0;
            else if($l > 0.6666)
                $l2 = mt_rand(0, 150) / 1000.0;
            else if(mt_rand(1, 2) === 1)
                $l2 = mt_rand(850, 1000) / 1000.0;
            else
                $l2 = mt_rand(0, 150) / 1000.0;
        }
        else
        {
            // RANDOM!
            $h2 = mt_rand(0, 1000) / 1000.0;
            $s2 = mt_rand(mt_rand(0, 500), 1000) / 1000.0;
            $l2 = mt_rand(mt_rand(0, 500), mt_rand(750, 1000)) / 1000.0;
        }

        $pet
            ->setColorA(ColorFunctions::HSL2Hex($h, $s, $l))
            ->setColorB(ColorFunctions::HSL2Hex($h2, $s2, $l2))
        ;
    }
}
