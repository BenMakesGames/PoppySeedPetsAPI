<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\StatusEffect;
use App\Enum\EnumInvalidValueException;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Repository\ItemRepository;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use Doctrine\ORM\EntityManagerInterface;

class PetExperienceService
{
    public const SOCIAL_ENERGY_PER_HANG_OUT = 576; // 2.5 hangouts per day (for average pets)

    private $petActivityStatsService;
    private $squirrel3;
    private $inventoryService;
    private $userStatsRepository;
    private CalendarService $calendarService;
    private UserQuestRepository $userQuestRepository;
    private ResponseService $responseService;
    private PetActivityLogTagRepository $petActivityLogTagRepository;

    public function __construct(
        PetActivityStatsService $petActivityStatsService, Squirrel3 $squirrel3, CalendarService $calendarService,
        InventoryService $inventoryService, UserStatsRepository $userStatsRepository, ResponseService $responseService,
        UserQuestRepository $userQuestRepository, PetActivityLogTagRepository $petActivityLogTagRepository
    )
    {
        $this->petActivityStatsService = $petActivityStatsService;
        $this->squirrel3 = $squirrel3;
        $this->inventoryService = $inventoryService;
        $this->userStatsRepository = $userStatsRepository;
        $this->calendarService = $calendarService;
        $this->userQuestRepository = $userQuestRepository;
        $this->responseService = $responseService;
        $this->petActivityLogTagRepository = $petActivityLogTagRepository;
    }

    /**
     * @param string[] $stats
     */
    public function gainExp(Pet $pet, int $exp, array $stats, ?PetActivityLog $activityLog = null)
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

        $levelUp = $pet->getExperience() >= $pet->getExperienceToLevel();

        while($pet->getExperience() >= $pet->getExperienceToLevel())
        {
            $pet->decreaseExperience($pet->getExperienceToLevel());

            $statToLevel = $this->squirrel3->rngNextFromArray($stats);

            if($pet->getSkills()->getStat($statToLevel) >= 20)
            {
                $pet->getSkills()->increaseScrollLevels();
                $this->userStatsRepository->incrementStat($pet->getOwner(), 'Skill Scrolls Made by Pets');

                $newItem = $this->inventoryService->petCollectsItem('Skill Scroll: ' . $statToLevel, $pet, $pet->getName() . ', a ' . $statToLevel . '-master, produced this scroll.', null);
                $newItem->setLockedToOwner(true);
            }
            else
                $pet->getSkills()->increaseStat($statToLevel);
        }

        if($activityLog && $levelUp)
            $activityLog->addTag($this->petActivityLogTagRepository->findOneBy([ 'title' => 'Level-up' ]));
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

    /**
     * @param Pet $pet
     * @param int $points
     */
    public function gainAffection(Pet $pet, int $points)
    {
        if($points === 0) return;

        $divideBy = 1;

        if($pet->getFood() + $pet->getAlcohol() < 0) $divideBy++;
        if($pet->getSafety() + $pet->getAlcohol() < 0) $divideBy++;

        $points = ceil($points / $divideBy);

        if($points === 0) return;

        $previousAffectionLevel = $pet->getAffectionLevel();

        $pet->increaseAffectionPoints($points);

        // if a pet's affection level increased, and you haven't unlocked the park, now you get the park!
        if($pet->getAffectionLevel() > $previousAffectionLevel && $pet->getOwner()->getUnlockedPark() === null)
            $pet->getOwner()->setUnlockedPark();

        if($this->calendarService->isValentinesOrAdjacent())
            $this->maybeGivePlayerTwuWuv($pet);
    }

    private function maybeGivePlayerTwuWuv(Pet $pet): bool
    {
        $alreadyReceived = $this->userQuestRepository->findOrCreate($pet->getOwner(), 'Valentines ' . date('Y-m-d'), false);

        if($alreadyReceived->getValue())
            return false;

        $this->inventoryService->receiveItem('Twu Wuv', $pet->getOwner(), $pet->getOwner(), $pet->getOwner()->getName() . ' received this from ' . $pet->getName() . ' for Valentine\'s Day!', LocationEnum::HOME, true);
        $this->inventoryService->receiveItem('Twu Wuv', $pet->getOwner(), $pet->getOwner(), $pet->getOwner()->getName() . ' received this from ' . $pet->getName() . ' for Valentine\'s Day!', LocationEnum::HOME, true);

        $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% gave Twu Wuv to %user:' . $pet->getOwner()->getId() . '.Name% for Valentine\'s Day! (Two of them! Two Twu Wuvs!)', 'items/resource/twu-wuv')
            ->addInterestingness(PetActivityLogInterestingnessEnum::HOLIDAY_OR_SPECIAL_EVENT)
            ->addTags($this->petActivityLogTagRepository->findByNames([ 'Special Event', 'Valentine\'s' ]))
        ;

        $alreadyReceived->setValue(true);

        return true;
    }
}
