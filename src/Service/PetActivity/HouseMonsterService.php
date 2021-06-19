<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Functions\GrammarFunctions;
use App\Model\PetChanges;
use App\Model\SummoningScrollMonster;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;

class HouseMonsterService
{
    private IRandom $squirrel3;
    private UserStatsRepository $userStatsRepository;
    private InventoryService $inventoryService;
    private EntityManagerInterface $em;
    private PetExperienceService $petExperienceService;

    public function __construct(
        Squirrel3 $squirrel3, UserStatsRepository $userStatsRepository, InventoryService $inventoryService,
        EntityManagerInterface $em, PetExperienceService $petExperienceService
    )
    {
        $this->squirrel3 = $squirrel3;
        $this->userStatsRepository = $userStatsRepository;
        $this->inventoryService = $inventoryService;
        $this->em = $em;
        $this->petExperienceService = $petExperienceService;
    }

    /**
     * @param Pet[] $petsAtHome
     */
    public function doFight(string $userSummonedDescription, array $petsAtHome, SummoningScrollMonster $monster)
    {
        $user = $petsAtHome[0]->getOwner();

        $totalSkill = 0;
        /** @var string[] $petNames */ $petNames = [];
        /** @var Pet[] $unprotectedPets */ $unprotectedPets = [];
        /** @var string[] $unprotectedPetNames */ $unprotectedPetNames = [];
        /** @var PetChanges[] $petChanges */ $petChanges = [];

        foreach($petsAtHome as $pet)
        {
            $petWithSkills = $pet->getComputedSkills();
            $totalSkill += $petWithSkills->getBrawl()->getTotal() + max($petWithSkills->getStrength()->getTotal(), $petWithSkills->getStamina()->getTotal()) + $petWithSkills->getDexterity()->getTotal();

            if($monster->element === 'fire')
            {
                if($petWithSkills->getHasProtectionFromHeat()->getTotal() > 0)
                    $totalSkill += 2;
                else
                {
                    $unprotectedPets[] = $pet;
                    $unprotectedPetNames[] = $pet->getName();
                }
            }
            else if($monster->element === 'electricity')
            {
                $unprotectedPets[] = $pet;
                $unprotectedPetNames[] = $pet->getName();
            }


            $petNames[] = $pet->getName();
            $petChanges[$pet->getId()] = new PetChanges($pet);
        }

        $roll = $this->squirrel3->rngNextInt(max(20, 1 + ($totalSkill >> 1)), 20 + $totalSkill);

        $result = $userSummonedDescription . ', causing ' . GrammarFunctions::indefiniteArticle($monster->name) . ' ' . $monster->name . ' to be summoned! ';

        $loot = $monster->minorRewards;

        $grab = $this->squirrel3->rngNextFromArray([
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
            $loot[] = $this->squirrel3->rngNextFromArray($monster->minorRewards);

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
            $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::BRAWL ]);

            if($won)
            {
                $pet
                    ->increaseSafety($this->squirrel3->rngNextInt(4, 8))
                    ->increaseEsteem($this->squirrel3->rngNextInt(6, 10))
                ;
            }
            else
            {
                $pet->increaseSafety(-$this->squirrel3->rngNextInt(4, 8));

                // you can't feel bad about yourself if you didn't even have a chance... right??
                if($totalSkill >= 40)
                    $pet->increaseEsteem(-$this->squirrel3->rngNextInt(2, 4));
                else
                    $pet->increaseLove(-$this->squirrel3->rngNextInt(2, 4)); // not very cool of you to summon the thing, then, though, I guess :P
            }
        }

        if(count($unprotectedPets) > 0)
        {
            if($monster->element === 'fire')
                $result .= "\n\n" . ArrayFunctions::list_nice($unprotectedPetNames) . ' ' . (count($unprotectedPetNames) === 1 ? 'was' : 'were') . ' unprotected from the ' . $monster->name . '\'s flames, and got singed!';
            else if($monster->element === 'electricity')
                $result .= "\n\n" . ArrayFunctions::list_nice($unprotectedPetNames) . ' ' . (count($unprotectedPetNames) === 1 ? 'was' : 'were') . ' unprotected from the ' . $monster->name . '\'s sparks, and got zapped!';

            foreach($unprotectedPets as $pet)
                $pet->increaseSafety(-$this->squirrel3->rngNextInt(4, 12));
        }

        if($won)
        {
            $message = ArrayFunctions::list_nice($petNames) . ' got this by defeating ' . GrammarFunctions::indefiniteArticle($monster->name) . ' ' . $monster->name . '.';
            $this->userStatsRepository->incrementStat($user, 'Won Against Something... Unfriendly');
        }
        else
        {
            $message = ArrayFunctions::list_nice($petNames) . ' ' . (count($petsAtHome) === 1 ? 'was' : 'were') . ' defeated by ' . GrammarFunctions::indefiniteArticle($monster->name) . ' ' . $monster->name . ', but managed to ' . $grab . ' this during the fight.';
            $this->userStatsRepository->incrementStat($user, 'Lost Against Something... Unfriendly');
        }

        foreach($loot as $item)
            $this->inventoryService->receiveItem($item, $user, $user, $message, LocationEnum::HOME);

        foreach($petsAtHome as $pet)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(5, 15), PetActivityStatEnum::HUNT, $won);

            $activityLog = (new PetActivityLog())
                ->setPet($pet)
                ->setEntry($result)
                ->setChanges($petChanges[$pet->getId()]->compare($pet))
                ->setViewed()
            ;

            $this->em->persist($activityLog);
        }

        return $result;
    }
}