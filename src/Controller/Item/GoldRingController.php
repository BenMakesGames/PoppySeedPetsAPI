<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\PetSpecies;
use App\Entity\User;
use App\Enum\FlavorEnum;
use App\Enum\PetLocationEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Functions\EnchantmentRepository;
use App\Functions\ItemRepository;
use App\Functions\MeritRepository;
use App\Functions\PetColorFunctions;
use App\Repository\PetRepository;
use App\Service\HattierService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetFactory;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/goldRing")]
class GoldRingController extends AbstractController
{
    #[Route("/{inventory}/smash", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function smash(
        Inventory $inventory, ResponseService $responseService, IRandom $squirrel3,
        EntityManagerInterface $em
    )
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'goldRing/#/smash');

        $inventory->changeItem(ItemRepository::findOneByName($em, 'Gold Bar'));

        $message = $squirrel3->rngNextFromArray([
            'Easy as 1, 2, 3.',
            'Easy as pie.',
            'Easy as falling off a log.',
            'Simple as A, B, C.',
            'No sweat.',
            'Like shooting fish in a barrel.',
            'Child\'s play.',
            'You could do this with one hand behind your back. So you do. OH GOD, IT\'S REALLY HAR--nah, it\'s still easy.',
            'Piece of cake.',
            'A task as pleasurable as it is simple.',
            'A breeze.',
            'Easy-peasy.',
            'Elementary.',
            'It\'s a cakewalk. (You know: when you take your cake out for a walk? I guess? Maybe? Okay, smarty pants, _you_ tell _me_ what a cakewalk is, then!)',
            'It\' a cinch!',
            'No problem.',
            'You\'ve scarcely done anything simpler!',
            'A walk in the park.',
        ]);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/collect100", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function collect100(
        Inventory $inventory, EntityManagerInterface $em, InventoryService $inventoryService,
        ResponseService $responseService, PetFactory $petFactory, IRandom $rng, HattierService $hattierService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'goldRing/#/collect100');

        $goldRingItem = $inventory->getItem();

        $count = InventoryService::countInventory($em, $user->getId(), $goldRingItem->getId(), $inventory->getLocation());

        if($count < 20)
        {
            return $responseService->itemActionSuccess('I\'m only counting ' . $count . ', so...');
        }
        else if($count < 45)
        {
            return $responseService->itemActionSuccess('Up to ' . $count . '! Not bad! Still a ways to go, though!');
        }
        else if($count < 60)
        {
            return $responseService->itemActionSuccess($count . '! About half-way there!');
        }
        else if($count < 80)
        {
            return $responseService->itemActionSuccess('Dang: ' . $count . '! You\'re really serious about this!');
        }
        else if($count < 95)
        {
            return $responseService->itemActionSuccess('omg! ' . $count . '!?');
        }
        else if($count == 95)
            return $responseService->itemActionSuccess($count . '!!');
        else if($count == 96)
            return $responseService->itemActionSuccess($count . '!!!!!');
        else if($count == 97)
            return $responseService->itemActionSuccess($count . '!! Just 3 more!');
        else if($count == 98)
            return $responseService->itemActionSuccess($count . '!! So close!');
        else if($count == 99)
            return $responseService->itemActionSuccess($count . '!! AAAAAAAAAAAAAA!!!');
        else
        {
            $inventoryService->loseItem($user, $goldRingItem->getId(), $inventory->getLocation(), 100);

            $hedgehog = $em->getRepository(PetSpecies::class)->findOneBy([ 'name' => 'Hedgehog' ]);

            $hedgehogName = $rng->rngNextFromArray([
                'Speedy', 'Dash', 'Blur', 'Quickly', 'Quills', 'Boots', 'Nitro', 'Boom', 'Runner', 'Jumper',
                'Sir Spinsalot', 'Miles', 'Blue'
            ]);

            $petColors = PetColorFunctions::generateRandomPetColors($rng);

            $newPet = $petFactory->createPet(
                $user, $hedgehogName, $hedgehog,
                $petColors[0], $petColors[1],
                FlavorEnum::getRandomValue($rng),
                MeritRepository::getRandomStartingMerit($em, $rng)
            );

            $newPet
                ->increaseLove(10)
                ->increaseSafety(10)
                ->increaseEsteem(10)
                ->increaseFood(-8)
                ->setScale($rng->rngNextInt(80, 120))
            ;

            $message = '100 Gold Rings!!! That\'s one extra Hedgehog!';

            $gottaGoFast = EnchantmentRepository::findOneByName($em, 'Super-sonic');

            if(!$hattierService->userHasUnlocked($user, $gottaGoFast))
            {
                $hattierService->playerUnlockAura($user, $gottaGoFast, 'You unlocked this by collecting 100 Gold Rings!');

                if($user->hasUnlockedFeature(UnlockableFeatureEnum::Hattier))
                    $message .= ' (And a new aura for the Hattier: Gotta\' Go Fast!)';
                else
                    $message .= ' (And something tells you you got something else, too, but you\'ll have to unlock the Hattier to find out what!)';
            }

            $numberOfPetsAtHome = PetRepository::getNumberAtHome($em, $user);

            $petJoinsHouse = $numberOfPetsAtHome < $user->getMaxPets();

            if(!$petJoinsHouse)
            {
                $newPet->setLocation(PetLocationEnum::DAYCARE);
                $message .= "\n\nYour house is full, so it dashes off to the daycare.";
            }

            $em->flush();

            $responseService->setReloadPets($petJoinsHouse);

            return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
        }
    }
}
