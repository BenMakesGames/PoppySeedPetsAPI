<?php
namespace App\Service;

use App\Entity\Item;
use App\Entity\ItemGroup;
use App\Entity\Pet;
use App\Entity\PetCraving;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogTagEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\StatusEffectHelpers;
use Doctrine\ORM\EntityManagerInterface;

class CravingService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly IRandom $rng,
        private readonly PetExperienceService $petExperienceService
    )
    {
    }

    public static function petHasCraving(Pet $pet): bool
    {
        if(!$pet->hasCraving())
            return false;

        if($pet->getCraving()->isSatisfied())
            return false;

        return true;
    }

    public function maybeRemoveCraving(Pet $pet)
    {
        if(
            CravingService::petHasCraving($pet) &&
            ($pet->getFood() < 0 || $pet->getSafety() < 0)
        )
        {
            $craving = $pet->getCraving();
            $this->em->remove($craving);
            $pet->setCraving(null);
        }
    }

    public function maybeAddCraving(Pet $pet)
    {
        if(
            (!$pet->hasMerit(MeritEnum::AFFECTIONLESS) || $pet->hasStatusEffect(StatusEffectEnum::BITTEN_BY_A_VAMPIRE)) &&
            !CravingService::petHasCraving($pet) &&
            $pet->getFullnessPercent() >= 0.5 &&
            $pet->getSafety() >= 8
        )
        {
            $craving = $pet->getCraving();
            $fiveDaysAgo = (new \DateTimeImmutable())->modify('-5 days');

            if($craving === null)
            {
                $craving = (new PetCraving())
                    ->setFoodGroup($this->getRandomCravingItemGroup($pet))
                    ->setCreatedOn(new \DateTimeImmutable())
                ;
                $this->em->persist($craving);
                $pet->setCraving($craving);
            }
            else if($craving->getSatisfiedOn() && $craving->getSatisfiedOn() <= $fiveDaysAgo)
            {
                $craving
                    ->setFoodGroup($this->getRandomCravingItemGroup($pet))
                    ->setCreatedOn(new \DateTimeImmutable())
                    ->setSatisfiedOn(null)
                ;
            }
        }
    }

    private function getRandomCravingItemGroup(Pet $pet): ItemGroup
    {
        if($pet->hasStatusEffect(StatusEffectEnum::BITTEN_BY_A_VAMPIRE))
            return $this->em->getRepository(ItemGroup::class)->findOneBy([ 'name' => 'Bloody' ]);

        $cravingGroups = $this->em->getRepository(ItemGroup::class)->findBy([ 'isCraving' => 1 ]);

        return $this->rng->rngNextFromArray($cravingGroups);
    }

    public static function foodMeetsCraving(Pet $pet, Item $food): bool
    {
        if(!$pet->getCraving() || $pet->getCraving()->isSatisfied())
            return false;

        return ArrayFunctions::any(
            $pet->getCraving()->getFoodGroup()->getItems(),
            fn(Item $i) => $i->getId() === $food->getId()
        );
    }

    public function satisfyCraving(Pet $pet, Item $food)
    {
        if(!$pet->getCraving() || $pet->getCraving()->isSatisfied())
            return;

        $pet->getCraving()->setSatisfiedOn(new \DateTimeImmutable());

        $this->petExperienceService->gainAffection($pet, 2);

        $statusEffect = $this->rng->rngNextFromArray([
            StatusEffectEnum::INSPIRED,
            StatusEffectEnum::ONEIRIC,
            StatusEffectEnum::VIVACIOUS,
        ]);

        StatusEffectHelpers::applyStatusEffect($this->em, $pet, $statusEffect, 8 * 60);

        PetActivityLogFactory::createUnreadLog($this->em, $pet, 'The ' . $food->getName() . ' that ' . ActivityHelpers::PetName($pet) . ' ate satisfied their craving! They\'re feeling ' . $statusEffect . '!')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Eating ]))
            ->setIcon('icons/status-effect/craving');
    }
}