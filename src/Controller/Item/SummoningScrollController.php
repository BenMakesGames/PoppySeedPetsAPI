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
use App\Functions\ColorFunctions;
use App\Functions\GrammarFunctions;
use App\Model\PetChanges;
use App\Model\PetShelterPet;
use App\Model\SummoningScrollMonster;
use App\Repository\MeritRepository;
use App\Repository\PetRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\PetFactory;
use App\Service\ResponseService;
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
        EntityManagerInterface $em, UserStatsRepository $userStatsRepository, InventoryService $inventoryService,
        PetExperienceService $petExperienceService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'summoningScroll/#/unfriendly');

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $petsAtHome = $petRepository->findBy([
            'owner' => $user,
            'inDaycare' => false
        ]);

        if(count($petsAtHome) === 0)
        {
            return $responseService->itemActionSuccess('');
        }

        /** @var SummoningScrollMonster $monster */
        $monster = ArrayFunctions::pick_one([
            SummoningScrollMonster::CreateDragon(),
            SummoningScrollMonster::CreateBalrog(),
            SummoningScrollMonster::CreateBasabasa(),
            SummoningScrollMonster::CreateIfrit(),
            SummoningScrollMonster::CreateCherufe(),
        ]);

        $totalSkill = 0;
        /** @var string[] $petNames */ $petNames = [];
        /** @var Pet[] $unprotectedPets */ $unprotectedPets = [];
        /** @var string[] $unprotectedPetNames */ $unprotectedPetNames = [];
        /** @var PetChanges[] $petChanges */ $petChanges = [];

        foreach($petsAtHome as $pet)
        {
            $totalSkill += $pet->getBrawl() + max($pet->getStrength(), $pet->getStamina()) + $pet->getDexterity();

            if($pet->hasProtectionFromHeat())
                $totalSkill += 2;
            else
            {
                $unprotectedPets[] = $pet;
                $unprotectedPetNames[] = $pet->getName();
            }

            $petNames[] = $pet->getName();
            $petChanges[$pet->getId()] = new PetChanges($pet);
        }

        $roll = mt_rand(max(20, 1 + floor($totalSkill / 2)), 20 + $totalSkill);

        $result = 'You read the scroll, causing ' . GrammarFunctions::indefiniteArticle($monster->name) . ' ' . $monster->name . ' to be summoned! ';

        $loot = $monster->minorRewards;

        $grab = ArrayFunctions::pick_one([
            'grab', 'snag', 'take'
        ]);

        if($roll >= 70)
        {
            $loot[] = $monster->majorReward;

            foreach($monster->minorRewards as $r)
                $loot[] = $r;

            $result .= ArrayFunctions::list_nice($petNames) . ' easily ' . (count($petsAtHome) === 1 ? 'dispatches' : 'dispatch') . ' the monster, taking its ' . ArrayFunctions::list_nice($loot) . '.';

            $exp = 5;
            $won = true;
        }
        else if($roll >= 50)
        {
            $loot[] = $monster->majorReward;
            $loot[] = ArrayFunctions::pick_one($monster->minorRewards);

            $result .= ArrayFunctions::list_nice($petNames) . ' ' . (count($petsAtHome) === 1 ? 'beats' : 'beat') . ' the monster back, and were rewarded with ' . ArrayFunctions::list_nice($loot) . '!';

            $exp = 5;
            $won = true;
        }
        else if($totalSkill < 30)
        {
            $result .= ArrayFunctions::list_nice($petNames) . ' ' . (count($petsAtHome) === 1 ? 'was' : 'were') . ' completely outmatched! At least they managed to ' . $grab. ' ' . ArrayFunctions::list_nice($loot) . '...';

            $exp = 2;
            $won = false;
        }
        else
        {
            $result .= ArrayFunctions::list_nice($petNames) . ' ' . (count($petsAtHome) === 1 ? 'fights' : 'fight') . ' their hardest, but ' . (count($petsAtHome) === 1 ? 'is' : 'are') . ' unable to defeat it! They were able to ' . $grab. ' ' . ArrayFunctions::list_nice($loot) . ', at least!';

            $exp = 3;
            $won = false;
        }

        foreach($petsAtHome as $pet)
        {
            $petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::BRAWL ]);

            if($won)
            {
                $pet
                    ->increaseSafety(mt_rand(4, 8))
                    ->increaseEsteem(mt_rand(6, 10))
                ;
            }
            else
            {
                $pet->increaseSafety(-mt_rand(4, 8));

                // you can't feel bad about yourself if you didn't even have a chance... right??
                if($totalSkill >= 40)
                    $pet->increaseEsteem(-mt_rand(2, 4));
                else
                    $pet->increaseLove(-mt_rand(2, 4)); // not very cool of you to summon the thing, then, though, I guess :P
            }
        }

        if(count($unprotectedPets) > 0)
        {
            $result .= "\n\n" . ArrayFunctions::list_nice($unprotectedPetNames) . ' ' . (count($unprotectedPetNames) === 1 ? 'was' : 'were') . ' unprotected from the ' . $monster->name . '\'s flames, and got singed!';

            foreach($unprotectedPets as $pet)
                $pet->increaseSafety(-mt_rand(4, 12));
        }

        if($won)
        {
            $message = ArrayFunctions::list_nice($petNames) . ' got this by defeating ' . GrammarFunctions::indefiniteArticle($monster->name) . ' ' . $monster->name . '.';
            $userStatsRepository->incrementStat($user, 'Won Against Something... Unfriendly');
        }
        else
        {
            $message = ArrayFunctions::list_nice($petNames) . ' ' . (count($petsAtHome) === 1 ? 'was' : 'were') . ' defeated by ' . GrammarFunctions::indefiniteArticle($monster->name) . ' ' . $monster->name . ', but managed to ' . $grab . ' this during the fight.';
            $userStatsRepository->incrementStat($user, 'Lost Against Something... Unfriendly');
        }

        foreach($loot as $item)
            $inventoryService->receiveItem($item, $user, $user, $message, LocationEnum::HOME);

        foreach($petsAtHome as $pet)
        {
            $petExperienceService->spendTime($pet, mt_rand(5, 15), PetActivityStatEnum::HUNT, $won);

            $activityLog = (new PetActivityLog())
                ->setPet($pet)
                ->setEntry($result)
                ->setChanges($petChanges[$pet->getId()]->compare($pet))
                ->setViewed()
            ;

            $em->persist($activityLog);
        }

        $em->flush();

        return $responseService->itemActionSuccess($result, [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/friendly", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function summonSomethingFriendly(
        Inventory $inventory, ResponseService $responseService, PetRepository $petRepository,
        UserRepository $userRepository, UserStatsRepository $userStatsRepository, EntityManagerInterface $em,
        PetSpeciesRepository $petSpeciesRepository, PetFactory $petFactory
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'summoningScroll/#/friendly');

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $pet = null;
        $gotASentinel = false;
        $gotAReusedSentinel = false;

        if(mt_rand(1, 19) === 1)
        {
            $pet = $petFactory->createRandomPetOfSpecies(
                $user,
                $petSpeciesRepository->findOneBy([ 'name' => 'Sentinel' ])
            );

            $pet->setName(ArrayFunctions::pick_one(self::SENTINEL_NAMES));

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

            $pet = $petFactory->createRandomPetOfSpecies($user, ArrayFunctions::pick_one($allSpecies));

            $pet->setScale(mt_rand(80, 120));

            if($pet->getSpecies()->getName() === 'Sentinel')
            {
                $pet->setName(ArrayFunctions::pick_one(self::SENTINEL_NAMES));

                $gotASentinel = true;
            }
        }

        $pet->setOwner($user);

        $numberOfPetsAtHome = $petRepository->getNumberAtHome($user);

        if($numberOfPetsAtHome >= $user->getMaxPets())
        {
            $pet->setInDaycare(true);

            if($gotAReusedSentinel)
                $message = 'You read the scroll... not ' . mt_rand(3, 6) . ' seconds later, a Sentinel appears! (That\'s not a pet! But it looks like someone took care of it... has it done this before?) You put it in the Pet Shelter daycare...';
            else if($gotASentinel)
                $message = 'You read the scroll... not ' . mt_rand(3, 6) . ' seconds later, a Sentinel appears! (That\'s not a pet!) You put it in the Pet Shelter daycare...';
            else
                $message = 'You read the scroll... not ' . mt_rand(3, 6) . ' seconds later, ' . GrammarFunctions::indefiniteArticle($pet->getSpecies()->getName()) . ' ' . $pet->getSpecies()->getName() . ' named ' . $pet->getName() . ' opens the door, waves "hello", then closes it again before heading to the Pet Shelter!';
        }
        else
        {
            $pet->setInDaycare(false);

            if($gotAReusedSentinel)
                $message = 'You read the scroll... not ' . mt_rand(3, 6) . ' seconds later, a Sentinel appears! (That\'s not a pet! But it looks like someone took care of it... has it done this before?) Well... it\'s here now, I guess...';
            else if($gotASentinel)
                $message = 'You read the scroll... not ' . mt_rand(3, 6) . ' seconds later, a Sentinel appears! (That\'s not a pet!) Well... it\'s here now, I guess...';
            else
                $message = 'You read the scroll... not ' . mt_rand(3, 6) . ' seconds later, ' . GrammarFunctions::indefiniteArticle($pet->getSpecies()->getName()) . ' ' . $pet->getSpecies()->getName() . ' named ' . $pet->getName() . ' opens the door, and walks inside!';
        }

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'reloadInventory' => true, 'itemDeleted' => true, 'reloadPets' => $numberOfPetsAtHome < $user->getMaxPets() ]);
    }
}
