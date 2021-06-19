<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetSkills;
use App\Entity\PetSpecies;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Functions\GrammarFunctions;
use App\Model\PetChanges;
use App\Model\SummoningScrollMonster;
use App\Repository\PetRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\PetActivity\HouseMonsterService;
use App\Service\PetExperienceService;
use App\Service\PetFactory;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/summoningScroll")
 */
class SummoningScrollController extends PoppySeedPetsItemController
{
    private const SENTINEL_NAMES = [
        'Sentinel',
        'Homunculus',
        'Golem',
        'Puppet',
        'Guardian',
        'Marionette',
        'Familiar',
        'Summon',
        'Shield',
        'Sentry',
        'Substitute',
        'Ersatz',
        'Proxy',
        'Placeholder',
        'Surrogate',
    ];

    /**
     * @Route("/{inventory}/unfriendly", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function summonSomethingUnfriendly(
        Inventory $inventory, ResponseService $responseService, PetRepository $petRepository,
        EntityManagerInterface $em, HouseMonsterService $houseMonsterService, Squirrel3 $squirrel3
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'summoningScroll/#/unfriendly');

        $em->remove($inventory);

        $petsAtHome = $petRepository->findBy([
            'owner' => $user,
            'inDaycare' => false
        ]);

        if(count($petsAtHome) === 0)
        {
            return $responseService->itemActionSuccess('');
        }

        /** @var SummoningScrollMonster $monster */
        $monster = $squirrel3->rngNextFromArray([
            SummoningScrollMonster::CreateDragon(),
            SummoningScrollMonster::CreateBalrog(),
            SummoningScrollMonster::CreateBasabasa(),
            SummoningScrollMonster::CreateIfrit(),
            SummoningScrollMonster::CreateCherufe(),
        ]);

        $result = $houseMonsterService->doFight('You read the scroll', $petsAtHome, $monster);

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

        $this->validateInventory($inventory, 'summoningScroll/#/unfriendly2');

        $em->remove($inventory);

        $petsAtHome = $petRepository->findBy([
            'owner' => $user,
            'inDaycare' => false
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
        $user = $this->getUser();

        $this->validateInventory($inventory, 'summoningScroll/#/friendly');

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

            $pet->setName($squirrel3->rngNextFromArray(self::SENTINEL_NAMES));

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

            $pet->setScale($squirrel3->rngNextInt(80, 120));

            if($pet->getSpecies()->getName() === 'Sentinel')
            {
                $pet->setName($squirrel3->rngNextFromArray(self::SENTINEL_NAMES));

                $gotASentinel = true;
            }
        }

        $pet->setOwner($user);

        $numberOfPetsAtHome = $petRepository->getNumberAtHome($user);

        if($numberOfPetsAtHome >= $user->getMaxPets())
        {
            $pet->setInDaycare(true);

            if($gotAReusedSentinel)
                $message = 'You read the scroll... not ' . $squirrel3->rngNextInt(3, 6) . ' seconds later, a Sentinel appears! (That\'s not a pet! But it looks like someone took care of it... has it done this before?) You put it in the Pet Shelter daycare...';
            else if($gotASentinel)
                $message = 'You read the scroll... not ' . $squirrel3->rngNextInt(3, 6) . ' seconds later, a Sentinel appears! (That\'s not a pet!) You put it in the Pet Shelter daycare...';
            else
                $message = 'You read the scroll... not ' . $squirrel3->rngNextInt(3, 6) . ' seconds later, ' . GrammarFunctions::indefiniteArticle($pet->getSpecies()->getName()) . ' ' . $pet->getSpecies()->getName() . ' named ' . $pet->getName() . ' opens the door, waves "hello", then closes it again before heading to the Pet Shelter!';
        }
        else
        {
            $pet->setInDaycare(false);

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
