<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Functions\ArrayFunctions;
use App\Functions\ColorFunctions;
use App\Repository\MeritRepository;
use App\Repository\PetRepository;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/wandOfWonder")
 */
class WandOfWonderController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/point", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function pointWandOfWonder(
        Inventory $inventory, ResponseService $responseService, UserQuestRepository $userQuestRepository,
        EntityManagerInterface $em, InventoryService $inventoryService, PetRepository $petRepository,
        TransactionService $transactionService, Request $request, MeritRepository $meritRepository
    )
    {
        $this->validateInventory($inventory, 'wandOfWonder/#/point');

        $user = $this->getUser();
        $location = $inventory->getLocation();

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('There is no such pet.');

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
            'toggleSpectral',
            'wine',
            'secretSeashell'
        ];

        if($user->getGreenhouse() && !$expandedGreenhouseWithWand->getValue())
            $possibleEffects[] = 'expandGreenhouse';

        $effect = ArrayFunctions::pick_one($possibleEffects);

        switch($effect)
        {
            case 'song':
                $notes = mt_rand(6, 10);
                $itemActionDescription = "The wand begins to sing.\n\nThen, it keeps singing.\n\nS-- still singi-- oh, wait, no, it's stoppe-- ah, never mind, just a pause.\n\nStiiiiiiiill going...\n\nOh, okay, it's stopped again. Is it for real this time?\n\nIt seems to be for real.\n\nYeah, okay, it's done.\n\nYou shake your head; " . $notes . " Music Notes fall out of your ears and clatter on the ground!\n\nFrickin' wand!";

                for($x = 0; $x < $notes; $x++)
                    $inventoryService->receiveItem('Music Note', $user, $user, 'These Music Notes fell out of ' . $user->getName() . '\'s ears after a Wand of Wonder sang for a while.', $location);

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
                $user->getGreenhouse()->increaseMaxPlants(1);
                $expandedGreenhouseWithWand->setValue(true);
                break;

            case 'oneMoney':
                $itemActionDescription = 'The wand begins to glow and shake violently. You hold on with all your might until, at last, it spits out a single ~~m~~. (Lame!)';
                $transactionService->getMoney($user, 1, 'Anticlimactically discharged by a Wand of Wonder.');
                break;

            case 'yellowDye':
                $dye = mt_rand(4, mt_rand(6, 10));
                $itemActionDescription = "Is that-- oh god! The wand is peeing!?\n\nWait, no... it's... Yellow Dye??!\n\nYou find some small jars to catch the stuff; in the end, you get " . $dye . " Yellow Dye.";

                for($x = 0; $x < $dye; $x++)
                    $inventoryService->receiveItem('Yellow Dye', $user, $user, 'A Wand of Wonder, uh, _summoned_ this Yellow Dye.', $location);

                break;

            case 'recolorAPet':
                if($pet)
                {
                    $this->recolorPet($pet);

                    $itemActionDescription = "A rainbow of colors swirl out of the wand and around " . $pet->getName() . ", whose colors change before your eyes!";
                }
                else
                {
                    $itemActionDescription = "A rainbow of colors swirl out of the wand and around the room, as if looking for something.\n\nAfter a moment, the colors fade away. If they really were looking for something, it seems they didn't find it.";
                }

                break;

            case 'toggleSpectral':
                if($pet->hasMerit(MeritEnum::SPECTRAL))
                {
                    $pet->removeMerit($meritRepository->findOneByName(MeritEnum::SPECTRAL));
                    $itemActionDescription = $pet->getName() . ' lands softly on the ground, and becomes completely opaque!';
                }
                else
                {
                    $pet->addMerit($meritRepository->findOneByName(MeritEnum::SPECTRAL));
                    $itemActionDescription = $pet->getName() . ' becomes slightly translucent, and begins to float!';
                }
                break;

            case 'wine':
                $wine = mt_rand(5, 10);
                $wines = [ 'Blackberry Wine', 'Blackberry Wine', 'Blueberry Wine', 'Blueberry Wine', 'Red Wine', 'Red Wine', 'Blood Wine' ];

                $itemActionDescription = "The wand shakes slightly, then begins pouring out wines of various colors! You grab some glasses, and catch as much as you can...";

                for($x = 0; $x < $wine; $x++)
                    $inventoryService->receiveItem(ArrayFunctions::pick_one($wines), $user, $user, $user->getName() . ' caught this wine pouring out of a Wand of Wonder.', $location);

                $itemActionEffects['reloadInventory'] = true;

                break;

            case 'secretSeashell':
                $itemActionDescription = 'For a moment, you hear the sound of the ocean. ' . $pet->getName() . ' leans in to listen, and a Secret Seashell drops off of their head!';
                $inventoryService->receiveItem('Secret Seashell', $user, $user, 'This fell off of ' . $pet->getName() . '\'s head after listening to a Wand of Wonder make ocean sounds.', $location);
                $itemActionEffects['reloadInventory'] = true;
                break;
        }

        if(mt_rand(1, 5) === 1)
        {
            $em->remove($inventory);

            $itemActionDescription .= "\n\n";

            $remains = mt_rand(1, 4);

            if($remains === 1)
            {
                $itemActionDescription .= 'Then, the wand snaps in two and crumbles to dust! (Well, actually, it crumbles to Silica Grounds.)';
                $inventoryService->receiveItem('Silica Grounds', $user, $user, 'These Silica Grounds were once a Wand of Wonder. Now they\'re just Silica Grounds. (Sorry, I guess that was a little redundant...)', $location);
            }
            else if($remains === 2)
            {
                $itemActionDescription .= 'Then, the wand burst into flames, and is reduced to Charcoal!';
                $inventoryService->receiveItem('Charcoal', $user, $user, 'The charred remains of a Wand of Wonder :|', $location);
            }
            else // $remains 3 || 4
            {
                $itemActionDescription .= 'You feel the last bits of magic drain from the wand. It\'s now nothing more than a common, Crooked Stick...';
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
        $colors = ColorFunctions::generateRandomPetColors();

        $pet
            ->setColorA($colors[0])
            ->setColorB($colors[1])
        ;
    }
}
