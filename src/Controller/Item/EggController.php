<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\PetSkills;
use App\Entity\User;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Functions\ArrayFunctions;
use App\Functions\ColorFunctions;
use App\Repository\PetRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/egg")
 */
class EggController extends PsyPetsItemController
{
    /**
     * @Route("/weird-blue/{inventory}/hatch", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function hatchWeirdBlueEgg(
        Inventory $inventory, ResponseService $responseService, UserQuestRepository $userQuestRepository,
        EntityManagerInterface $em, PetRepository $petRepository, PetSpeciesRepository $petSpeciesRepository
    )
    {
        $this->validateInventory($inventory, 'egg/weird-blue/#/hatch');

        $starMonkey = $petSpeciesRepository->findOneBy([ 'name' => 'Star Monkey' ]);

        if(!$starMonkey)
            throw new \Exception('The species "Star Monkey" does not exist! :| Make Ben fix this!');

        $user = $this->getUser();
        $location = $inventory->getLocation();

        if($location !== LocationEnum::HOME)
            return $responseService->itemActionSuccess('You can\'t hatch it here! Take it to your house, quick!');

        $increasedPetLimitWithEgg = $userQuestRepository->findOrCreate($user, 'Increased Pet Limit with Weird, Blue Egg', false);


        $message = "Whoa! A weird creature popped out! It kind of looks like a monkey, but without arms. Also: a glowing tail. (Also: I feel like monkeys don't hatch from eggs?)";

        $em->remove($inventory);

        if(!$increasedPetLimitWithEgg->getValue())
        {
            $user->increaseMaxPets(1);
            $increasedPetLimitWithEgg->setValue(true);

            $message .= "\n\nAlso, your maximum pet limit at home has been increased by one!? Sure, why not! (But just this once!)";
        }

        $message .= "\n\nAnyway, it's super cute, and... really seems to like you! In fact, it's already named itself after you??";

        $petSkills = new PetSkills();

        $em->persist($petSkills);

        $newPet = (new Pet())
            ->setSpecies($starMonkey)
            ->setFavoriteFlavor(FlavorEnum::getRandomValue())
            ->setOwner($user)
            ->setName($user->getName())
            ->increaseLove(10)
            ->increaseSafety(10)
            ->increaseEsteem(10)
            ->increaseFood(-8)
            ->setSkills($petSkills)
        ;

        $em->persist($newPet);

        $numberOfPetsAtHome = $petRepository->getNumberAtHome($user);

        if($numberOfPetsAtHome >= $user->getMaxPets())
        {
            $newPet->setInDaycare(true);
            $message .= "\n\nBut, you know, into the daycare it goes, I guess!";
        }

        $this->recolorPet($newPet);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'reloadInventory' => true, 'itemDeleted' => true, 'reloadPets' => true ]);
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
