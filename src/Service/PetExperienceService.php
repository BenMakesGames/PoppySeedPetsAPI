<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\StatusEffect;
use App\Enum\EnumInvalidValueException;
use App\Enum\StatusEffectEnum;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;

class PetExperienceService
{
    public const SOCIAL_ENERGY_PER_HANG_OUT = 576; // 2.5 hangouts per day (for average pets)

    private $petActivityStatsService;
    private $em;
    private $responseService;
    private $itemRepository;
    private $squirrel3;
    private $inventoryService;

    public function __construct(
        PetActivityStatsService $petActivityStatsService, EntityManagerInterface $em,
        ResponseService $responseService, ItemRepository $itemRepository, Squirrel3 $squirrel3,
        InventoryService $inventoryService
    )
    {
        $this->petActivityStatsService = $petActivityStatsService;
        $this->em = $em;
        $this->responseService = $responseService;
        $this->itemRepository = $itemRepository;
        $this->squirrel3 = $squirrel3;
        $this->inventoryService = $inventoryService;
    }

    /**
     * @param string[] $stats
     */
    public function gainExp(Pet $pet, int $exp, array $stats)
    {
        if($pet->hasStatusEffect(StatusEffectEnum::INSPIRED))
            $exp++;

        if($exp < 0) return;

        if($pet->getTool() && ArrayFunctions::any($stats, fn(string $stat) => $pet->getTool()->focusesSkill($stat)))
        {
            $exp++;
        }

        $divideBy = 1;

        if($pet->getFood() + $pet->getAlcohol() < 0) $divideBy++;
        if($pet->getSafety() + $pet->getAlcohol() < 0) $divideBy++;
        if($pet->getLove() + $pet->getAlcohol() < 0) $divideBy++;
        if($pet->getEsteem() + $pet->getAlcohol() < 0) $divideBy++;

        $divideBy += 2 * $pet->getAlcohol() / $pet->getStomachSize();

        $exp = round($exp / $divideBy);

        if($exp === 0) return;

        $pet->increaseExperience($exp);

        while($pet->getExperience() >= $pet->getExperienceToLevel())
        {
            $pet->decreaseExperience($pet->getExperienceToLevel());

            $statToLevel = $this->squirrel3->rngNextFromArray($stats);

            if($pet->getSkills()->getStat($statToLevel) >= 20)
            {
                $newItem = $this->inventoryService->petCollectsItem('Skill Scroll: ' . $statToLevel, $pet, ActivityHelpers::PetName($pet) . ', a ' . $statToLevel . '-master, produced this scroll.', null);
                $newItem->setLockedToOwner(true);
            }
            else
                $pet->getSkills()->increaseStat($statToLevel);
        }
    }

    public function spendSocialEnergy(Pet $pet, int $energy)
    {
        if($pet->hasStatusEffect(StatusEffectEnum::EXTRA_EXTROVERTED))
            $energy = ceil($energy / 2);

        if($this->squirrel3->rngNextInt(1, 10) === 1)
        {
            // smallish chance to consume WAY less energy. this was added to help jiggle pets out of a situation where
            // two pets owned by the same account are always offset in social energy such that they're never able to hang
            // out with each other.
            $energy = $this->squirrel3->rngNextInt(ceil($energy / 4), ceil($energy * 3 / 4));
        }
        else
        {
            // always add a LITTLE random jiggle, though:
            $energy = $this->squirrel3->rngNextInt(ceil($energy * 8 / 10), ceil($energy * 12 / 10));
        }

        // tool modifiers (if any)
        $socialEnergyModifier = $pet->getTool() ? $pet->getTool()->socialEnergyModifier() : 0;

        // a small drift based on number of extra friends a pet can have; more friends = less energy cost
        $socialEnergyModifier -= $pet->getBonusMaximumFriends() * 5;

        // introverted pets spend more social energy; extroverted pets spend less
        if($pet->getExtroverted() < 0)
            $socialEnergyModifier += 20;
        else if($pet->getExtroverted() > 0)
            $socialEnergyModifier -= 20;

        $energy = round($energy * (100 + $socialEnergyModifier) / 100);

        if($energy < 0)
            throw new \Exception('Somehow, the game tried to spend negative social energy. This is bad, and Ben should fix it.');

        $pet->getHouseTime()->spendSocialEnergy($energy);
    }

    /**
     * spendTime should be called AFTER gainExp
     * @throws EnumInvalidValueException
     */
    public function spendTime(Pet $pet, int $time, string $activityStat, ?bool $success)
    {
        $pet->getHouseTime()->spendActivityTime($time);
        $this->petActivityStatsService->logStat($pet, $activityStat, $success, $time);

        if($pet->getPregnancy())
            $pet->getPregnancy()->increaseGrowth($time);

        /** @var StatusEffect[] $statusEffects */
        $statusEffects = array_values($pet->getStatusEffects()->toArray());

        for($i = count($statusEffects) - 1; $i >= 0; $i--)
        {
            $statusEffects[$i]->spendTime($time);

            // some status effects TRANSFORM when they run out (like caffeinated -> tired)
            if($statusEffects[$i]->getTimeRemaining() <= 0)
            {
                if($statusEffects[$i]->getStatus() === StatusEffectEnum::CAFFEINATED)
                {
                    $newTotal = ceil($statusEffects[$i]->getTotalDuration() / 2);
                    $statusEffects[$i]
                        ->setStatus(StatusEffectEnum::TIRED)
                        ->setTimeRemaining($statusEffects[$i]->getTimeRemaining() + $newTotal)
                        ->setTotalDuration($newTotal)
                    ;
                }
            }

            // if the status effect didn't transform (or was still out of time AFTER transforming), then delete it
            if($statusEffects[$i]->getTimeRemaining() <= 0)
                $pet->removeStatusEffect($statusEffects[$i]);
        }
    }
}
