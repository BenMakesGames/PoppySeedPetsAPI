<?php
namespace App\Controller\Item;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\PetLocationEnum;
use App\Enum\UserStatEnum;
use App\Functions\GrammarFunctions;
use App\Model\SummoningScrollMonster;
use App\Repository\PetRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserRepository;
use App\Repository\UserStatsRepository;
use App\Service\PetActivity\HouseMonsterService;
use App\Service\PetFactory;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/evilBeetle")
 */
class EvilSentientBeetleController extends AbstractController
{
    /**
     * @Route("/{inventory}/defeat", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function summonSomethingUnfriendly(
        Inventory $inventory, ResponseService $responseService, PetRepository $petRepository,
        EntityManagerInterface $em, HouseMonsterService $houseMonsterService, Squirrel3 $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'evilBeetle/#/defeat');

        $em->remove($inventory);

        $petsAtHome = $petRepository->findBy([
            'owner' => $user,
            'location' => PetLocationEnum::HOME
        ]);

        if(count($petsAtHome) === 0)
        {
            return $responseService->itemActionSuccess('You have no pets at home! You can\'t defeat evil all on your own!');
        }

        /** @var SummoningScrollMonster $monster */
        $monster = SummoningScrollMonster::CreateDiscipleOfHunCame();

        $result = $houseMonsterService->doFight('You challenge the beetle', $petsAtHome, $monster);

        $em->flush();

        return $responseService->itemActionSuccess($result, [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/unfriendly2", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function summonSomethingFromDeepSpace(
        Inventory $inventory, ResponseService $responseService, PetRepository $petRepository,
        EntityManagerInterface $em, HouseMonsterService $houseMonsterService, Squirrel3 $squirrel3
    )
    {
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'summoningScroll/#/unfriendly2');

        $em->remove($inventory);

        $petsAtHome = $petRepository->findBy([
            'owner' => $user,
            'location' => PetLocationEnum::HOME
        ]);

        if(count($petsAtHome) === 0)
        {
            return $responseService->itemActionSuccess('');
        }

        /** @var SummoningScrollMonster $monster */
        $monster = $squirrel3->rngNextFromArray([
            SummoningScrollMonster::CreateCrystallineEntity(),
            SummoningScrollMonster::CreateBivusRelease(),
            SummoningScrollMonster::CreateSpaceJelly(),
        ]);

        $result = $houseMonsterService->doFight('You cast your voice into the mirror', $petsAtHome, $monster);

        $em->flush();

        return $responseService->itemActionSuccess($result, [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/friendly", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function summonSomethingFriendly(
        Inventory $inventory, ResponseService $responseService, PetRepository $petRepository,
        UserRepository $userRepository, UserStatsRepository $userStatsRepository, EntityManagerInterface $em,
        PetSpeciesRepository $petSpeciesRepository, PetFactory $petFactory, Squirrel3 $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'summoningScroll/#/friendly');

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $pet = null;
        $gotASentinel = false;
        $gotAReusedSentinel = false;

        if($squirrel3->rngNextInt(1, 19) === 1)
        {
            $pet = $petFactory->createRandomPetOfSpecies(
                $user,
                $petSpeciesRepository->findOneBy([ 'name' => 'Sentinel' ])
            );

            $gotASentinel = true;
        }

        if($pet === null)
        {
            $pet = $petRepository->findOneBy(
                [
                    'owner' => $userRepository->findOneByEmail('the-wilds@poppyseedpets.com')
                ],
                [ 'lastInteracted' => 'ASC' ]
            );

            if($pet && $pet->getSpecies()->getName() === 'Sentinel')
            {
                $gotAReusedSentinel = true;
            }
        }

        if($pet === null)
        {
            $allSpecies = $petSpeciesRepository->findAll();

            $pet = $petFactory->createRandomPetOfSpecies($user, $squirrel3->rngNextFromArray($allSpecies));

            $gotASentinel = $pet->getSpecies()->getName() === 'Sentinel';
        }

        $pet->setOwner($user);

        $numberOfPetsAtHome = $petRepository->getNumberAtHome($user);

        if($numberOfPetsAtHome >= $user->getMaxPets())
        {
            $pet->setLocation(PetLocationEnum::DAYCARE);

            if($gotAReusedSentinel)
                $message = 'You read the scroll... not ' . $squirrel3->rngNextInt(3, 6) . ' seconds later, a Sentinel appears! (That\'s not a pet! But it looks like someone took care of it... has it done this before?) You put it in the Pet Shelter daycare...';
            else if($gotASentinel)
                $message = 'You read the scroll... not ' . $squirrel3->rngNextInt(3, 6) . ' seconds later, a Sentinel appears! (That\'s not a pet!) You put it in the Pet Shelter daycare...';
            else
                $message = 'You read the scroll... not ' . $squirrel3->rngNextInt(3, 6) . ' seconds later, ' . GrammarFunctions::indefiniteArticle($pet->getSpecies()->getName()) . ' ' . $pet->getSpecies()->getName() . ' named ' . $pet->getName() . ' opens the door, waves "hello", then closes it again before heading to the Pet Shelter!';
        }
        else
        {
            $pet->setLocation(PetLocationEnum::HOME);

            if($gotAReusedSentinel)
                $message = 'You read the scroll... not ' . $squirrel3->rngNextInt(3, 6) . ' seconds later, a Sentinel appears! (That\'s not a pet! But it looks like someone took care of it... has it done this before?) Well... it\'s here now, I guess...';
            else if($gotASentinel)
                $message = 'You read the scroll... not ' . $squirrel3->rngNextInt(3, 6) . ' seconds later, a Sentinel appears! (That\'s not a pet!) Well... it\'s here now, I guess...';
            else
                $message = 'You read the scroll... not ' . $squirrel3->rngNextInt(3, 6) . ' seconds later, ' . GrammarFunctions::indefiniteArticle($pet->getSpecies()->getName()) . ' ' . $pet->getSpecies()->getName() . ' named ' . $pet->getName() . ' opens the door, and walks inside!';
        }

        $em->flush();

        $responseService->setReloadPets($numberOfPetsAtHome < $user->getMaxPets());

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
