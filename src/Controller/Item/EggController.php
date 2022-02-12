<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetLocationEnum;
use App\Repository\MeritRepository;
use App\Repository\PetRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserQuestRepository;
use App\Service\PetColorService;
use App\Service\PetFactory;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/egg")
 */
class EggController extends PoppySeedPetsItemController
{
    /**
     * @Route("/polyp/{inventory}/hatch", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function hatchPolyp(
        Inventory $inventory, ResponseService $responseService, Squirrel3 $squirrel3,
        EntityManagerInterface $em, PetRepository $petRepository, PetSpeciesRepository $petSpeciesRepository,
        MeritRepository $meritRepository, PetFactory $petFactory, PetColorService $petColorService
    )
    {
        $this->validateInventory($inventory, 'egg/polyp/#/hatch');

        $jelling = $petSpeciesRepository->findOneBy([ 'name' => 'Sága Jelling' ]);

        if(!$jelling)
            throw new HttpException(500, 'The species "Sága Jelling" does not exist! :| Make Ben fix this!');

        $user = $this->getUser();
        $location = $inventory->getLocation();

        if($location !== LocationEnum::HOME)
            return $responseService->itemActionSuccess('You can\'t hatch it here! Take it to your house, quick!');

        $message = "A jellyfish detached";

        $em->remove($inventory);

        $message .= "\n\nAnyway, it's super cute, and... really seems to like you! In fact, it's already named itself after you??";

        $jellingName = $squirrel3->rngNextFromArray([
            'Epistêmê',
            'Gyaan',
            'Wissen',
            'Hæfni',
            'Visku',
            'Mahara',
            'Hikma',
            'Dovednost',
            'Sabedoria',
        ]);

        $newPet = $petFactory->createPet(
            $user, $jellingName, $jelling, '', '', FlavorEnum::getRandomValue($squirrel3), $meritRepository->findOneByName(MeritEnum::SAGA_SAGA)
        );

        $newPet
            ->increaseLove(10)
            ->increaseSafety(10)
            ->increaseEsteem(10)
            ->increaseFood(-8)
            ->setScale($squirrel3->rngNextInt(80, 120))
        ;

        $numberOfPetsAtHome = $petRepository->getNumberAtHome($user);

        if($numberOfPetsAtHome >= $user->getMaxPets())
        {
            $newPet->setLocation(PetLocationEnum::DAYCARE);
            $message .= "\n\nBut, you know, your house is full, so into the daycare it goes, I guess!";
        }

        $petColorService->recolorPet($newPet);

        $em->flush();

        $responseService->setReloadPets(true);

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/weird-blue/{inventory}/hatch", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function hatchWeirdBlueEgg(
        Inventory $inventory, ResponseService $responseService, UserQuestRepository $userQuestRepository,
        EntityManagerInterface $em, PetRepository $petRepository, PetSpeciesRepository $petSpeciesRepository,
        MeritRepository $meritRepository, PetFactory $petFactory, PetColorService $petColorService,
        Squirrel3 $squirrel3
    )
    {
        $this->validateInventory($inventory, 'egg/weird-blue/#/hatch');

        $starMonkey = $petSpeciesRepository->findOneBy([ 'name' => 'Star Monkey' ]);

        if(!$starMonkey)
            throw new HttpException(500, 'The species "Star Monkey" does not exist! :| Make Ben fix this!');

        $user = $this->getUser();
        $location = $inventory->getLocation();

        if($location !== LocationEnum::HOME)
            return $responseService->itemActionSuccess('You can\'t hatch it here! Take it to your house, quick!');

        $increasedPetLimitWithEgg = $userQuestRepository->findOrCreate($user, 'Increased Pet Limit with Weird, Blue Egg', false);
        $increasedPetLimitWithMetalBox = $userQuestRepository->findOrCreate($user, 'Increased Pet Limit with Metal Box', false);

        $message = "Whoa! A weird creature popped out! It kind of looks like a monkey, but without arms. Also: a glowing tail. (Also: I feel like monkeys don't hatch from eggs?)";

        $em->remove($inventory);

        if(!$increasedPetLimitWithEgg->getValue() && !$increasedPetLimitWithMetalBox->getValue())
        {
            $user->increaseMaxPets(1);
            $increasedPetLimitWithEgg->setValue(true);

            $message .= "\n\nAlso, your maximum pet limit at home has been increased by one!? Sure, why not! (But just this once!)";
        }

        $message .= "\n\nAnyway, it's super cute, and... really seems to like you! In fact, it's already named itself after you??";

        $monkeyName = $squirrel3->rngNextFromArray([
            'Climbing',
            'Fuzzy',
            'Howling',
            'Monkey',
            'Naner',
            'Poppy',
            'Stinky',
            'Tree',
        ]) . ' ' . $user->getName();

        $newPet = $petFactory->createPet(
            $user, $monkeyName, $starMonkey, '', '', FlavorEnum::getRandomValue($squirrel3), $meritRepository->getRandomStartingMerit()
        );

        $newPet
            ->increaseLove(10)
            ->increaseSafety(10)
            ->increaseEsteem(10)
            ->increaseFood(-8)
            ->setScale($squirrel3->rngNextInt(80, 120))
        ;

        $numberOfPetsAtHome = $petRepository->getNumberAtHome($user);

        if($numberOfPetsAtHome >= $user->getMaxPets())
        {
            $newPet->setLocation(PetLocationEnum::DAYCARE);
            $message .= "\n\nBut, you know, your house is full, so into the daycare it goes, I guess!";
        }

        $petColorService->recolorPet($newPet);

        $em->flush();

        $responseService->setReloadPets(true);

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/metalBox/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openMetalBox(
        Inventory $inventory, ResponseService $responseService, UserQuestRepository $userQuestRepository,
        EntityManagerInterface $em, PetRepository $petRepository, PetSpeciesRepository $petSpeciesRepository,
        MeritRepository $meritRepository, PetFactory $petFactory, PetColorService $petColorService, Squirrel3 $squirrel3
    )
    {
        $this->validateInventory($inventory, 'egg/metalBox/#/open');

        $grabber = $petSpeciesRepository->findOneBy([ 'name' => 'Grabber' ]);

        if(!$grabber)
            throw new HttpException(500, 'The species "Grabber" does not exist! :| Make Ben fix this!');

        $user = $this->getUser();
        $location = $inventory->getLocation();

        if($location !== LocationEnum::HOME)
            return $responseService->itemActionSuccess('You can\'t open it here! Take it to your house, quick!');

        $increasedPetLimitWithEgg = $userQuestRepository->findOrCreate($user, 'Increased Pet Limit with Weird, Blue Egg', false);
        $increasedPetLimitWithMetalBox = $userQuestRepository->findOrCreate($user, 'Increased Pet Limit with Metal Box', false);

        $message = "Whoa! A weird creature popped out! It's some kinda' robot! But without arms?";

        $em->remove($inventory);

        if(!$increasedPetLimitWithEgg->getValue() && !$increasedPetLimitWithMetalBox->getValue())
        {
            $user->increaseMaxPets(1);
            $increasedPetLimitWithMetalBox->setValue(true);

            $message .= "\n\n(Also, your maximum pet limit at home has been increased by one! But just this once!)";
        }

        $message .= "\n\nAnyway, it's dashing around like it's excited to be here; it really seems to like you! In fact, it's already named itself after you??";

        $newPet = $petFactory->createPet(
            $user, '', $grabber, '', '', FlavorEnum::getRandomValue($squirrel3), $meritRepository->getRandomStartingMerit()
        );

        $petColorService->recolorPet($newPet, 0.2);

        $robotName = 'Metal ' . $user->getName() . ' ' . $squirrel3->rngNextFromArray([
            '2.0',
            'Beta',
            'Mk 2',
            '#' . $newPet->getColorA(),
            'X',
            '',
            'RC1',
            'SP2'
        ]);

        $newPet->setName(trim($robotName));

        $newPet
            ->increaseLove(10)
            ->increaseSafety(10)
            ->increaseEsteem(10)
            ->increaseFood(-8)
            ->setScale($squirrel3->rngNextInt(80, 120))
        ;

        $numberOfPetsAtHome = $petRepository->getNumberAtHome($user);

        if($numberOfPetsAtHome >= $user->getMaxPets())
        {
            $newPet->setLocation(PetLocationEnum::DAYCARE);
            $message .= "\n\nBut, you know, your house is full, so into the daycare it goes, I guess!";
        }

        $em->flush();

        $responseService->setReloadPets(true);

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
