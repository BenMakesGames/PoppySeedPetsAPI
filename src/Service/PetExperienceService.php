<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\StatusEffect;
use App\Enum\EnumInvalidValueException;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetSkillEnum;
use App\Enum\StatusEffectEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Repository\ItemRepository;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Repository\UserUnlockedFeatureRepository;
use Doctrine\ORM\EntityManagerInterface;

class PetExperienceService
{
    public const SOCIAL_ENERGY_PER_HANG_OUT = 576; // 2.5 hangouts per day (for average pets)

    private PetActivityStatsService $petActivityStatsService;
    private IRandom $squirrel3;
    private InventoryService $inventoryService;
    private UserStatsRepository $userStatsRepository;
    private CalendarService $calendarService;
    private UserQuestRepository $userQuestRepository;
    private ResponseService $responseService;
    private PetActivityLogTagRepository $petActivityLogTagRepository;
    private HattierService $hattierService;
    private UserUnlockedFeatureRepository $userUnlockedFeatureRepository;

    public function __construct(
        PetActivityStatsService $petActivityStatsService, Squirrel3 $squirrel3, CalendarService $calendarService,
        InventoryService $inventoryService, UserStatsRepository $userStatsRepository, ResponseService $responseService,
        UserQuestRepository $userQuestRepository, PetActivityLogTagRepository $petActivityLogTagRepository,
        HattierService $hattierService, UserUnlockedFeatureRepository $userUnlockedFeatureRepository
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
        $this->hattierService = $hattierService;
        $this->userUnlockedFeatureRepository = $userUnlockedFeatureRepository;
    }

    /**
     * @param string[] $stats
     */
    public function gainExp(Pet $pet, int $exp, array $stats, ?PetActivityLog $activityLog = null): bool
    {
        if(count($stats) == 0) return false;

        if($pet->hasStatusEffect(StatusEffectEnum::INSPIRED))
            $exp++;

        if($exp < 0) return false;

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

        if($exp == 0) return false;

        $pet->increaseExperience($exp);

        $levelUp = $pet->getExperience() >= $pet->getExperienceToLevel();

        $focusStatusEffect = $levelUp ? PetExperienceService::getPetFocusingStatusEffect($pet) : null;

        while($pet->getExperience() >= $pet->getExperienceToLevel())
        {
            $pet->decreaseExperience($pet->getExperienceToLevel());

            if($focusStatusEffect && $pet->getSkills()->getStat($focusStatusEffect->skill) >= 10)
                $statToLevel = $focusStatusEffect->skill;
            else
                $statToLevel = $this->squirrel3->rngNextFromArray($stats);

            // only remove a Focused status effect if the focused stat was leveled-up
            if($focusStatusEffect && $focusStatusEffect->skill == $statToLevel)
            {
                $pet->removeStatusEffect($pet->getStatusEffect($focusStatusEffect->statusEffect));
                $focusStatusEffect = null;
            }

            if($pet->getSkills()->getStat($statToLevel) >= 20)
            {
                $pet->getSkills()->increaseScrollLevels();
                $this->userStatsRepository->incrementStat($pet->getOwner(), 'Skill Scrolls Made by Pets');

                $newItem = $this->inventoryService->petCollectsItem('Skill Scroll: ' . $statToLevel, $pet, $pet->getName() . ', a ' . $statToLevel . '-master, produced this scroll.', null);
                $newItem->setLockedToOwner(true);
            }
            else
            {
                $pet->getSkills()->increaseStat($statToLevel);

                if($activityLog)
                {
                    $activityLog->setEntry($activityLog->getEntry() . ' %pet:' . $pet->getId() . '.name% leveled up! +1 ' . ucfirst($statToLevel) . '!')
                        ->addInterestingness(PetActivityLogInterestingnessEnum::LEVEL_UP)
                    ;
                }
            }

            if($pet->getLevel() == 50)
                $this->unlockLevel50Style($pet);
        }

        if($activityLog && $levelUp)
            $activityLog->addTag($this->petActivityLogTagRepository->findOneBy([ 'title' => 'Level-up' ]));

        return $levelUp;
    }

    private static function getPetFocusingStatusEffect(Pet $pet): ?FocusingStatusEffect
    {
        $possibleEffects = [
            [ PetSkillEnum::BRAWL, StatusEffectEnum::FOCUSED_BRAWL ],
            [ PetSkillEnum::NATURE, StatusEffectEnum::FOCUSED_NATURE ],
            [ PetSkillEnum::CRAFTS, StatusEffectEnum::FOCUSED_CRAFTS ],
            [ PetSkillEnum::STEALTH, StatusEffectEnum::FOCUSED_STEALTH ],
            [ PetSkillEnum::SCIENCE, StatusEffectEnum::FOCUSED_SCIENCE ],
            [ PetSkillEnum::MUSIC, StatusEffectEnum::FOCUSED_MUSIC ],
            [ PetSkillEnum::UMBRA, StatusEffectEnum::FOCUSED_UMBRA ],
        ];

        foreach($possibleEffects as $effect)
        {
            $statusEffect = $pet->getStatusEffect($effect[1]);

            if($statusEffect)
                return new FocusingStatusEffect($effect[0], $effect[1]);
        }

        return null;
    }

    private function unlockLevel50Style(Pet $pet)
    {
        $this->hattierService->petMaybeUnlockAura(
            $pet,
            'Impactful',
            '%pet:' . $pet->getId() . '.name% has reached level 50?! Incredible. Truly _Wow!_',
            '%pet:' . $pet->getId() . '.name% has reached level 50?! Incredible. Truly _Wow!_',
            ActivityHelpers::PetName($pet) . ' has reached level 50! Incredible. Truly _Wow!_ (And I\'m sure the Hattier would agree!)'
        );
    }

    public function spendSocialEnergy(Pet $pet, int $energy)
    {
        if($pet->hasStatusEffect(StatusEffectEnum::EXTRA_EXTROVERTED) || $pet->hasStatusEffect(StatusEffectEnum::MOONSTRUCK))
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

        $this->spendTimeOnStatusEffects($pet, $time);
    }

    public function spendTimeOnStatusEffects(Pet $pet, int $time)
    {
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
        if($points === 0 || $pet->hasMerit(MeritEnum::AFFECTIONLESS))
            return;

        $divideBy = 1;

        if($pet->getFood() + $pet->getAlcohol() < 0) $divideBy++;
        if($pet->getSafety() + $pet->getAlcohol() < 0) $divideBy++;

        $points = ceil($points / $divideBy);

        if($points == 0) return;

        $previousAffectionLevel = $pet->getAffectionLevel();

        $pet->increaseAffectionPoints($points);

        // if a pet's affection level increased, and you haven't unlocked the park, now you get the park!
        if($pet->getAffectionLevel() > $previousAffectionLevel && !$pet->getOwner()->hasUnlockedFeature(UnlockableFeatureEnum::Park))
            $this->userUnlockedFeatureRepository->create($pet->getOwner(), UnlockableFeatureEnum::Park);

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

class FocusingStatusEffect
{
    function __construct(string $skill, string $statusEffect)
    {
        $this->skill = $skill;
        $this->statusEffect = $statusEffect;
    }

    public string $skill;
    public string $statusEffect;
}