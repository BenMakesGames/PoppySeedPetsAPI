<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\StatusEffect;
use App\Enum\StatusEffectEnum;
use App\Functions\ArrayFunctions;
use Doctrine\ORM\EntityManagerInterface;

class StatusEffectService
{
    private EntityManagerInterface $em;
    private ResponseService $responseService;
    private EquipmentService $equipmentService;

    public function __construct(
        EntityManagerInterface $em, ResponseService $responseService, EquipmentService $equipmentService
    )
    {
        $this->em = $em;
        $this->responseService = $responseService;
        $this->equipmentService = $equipmentService;
    }

    public function applyStatusEffect(Pet $pet, string $status, int $durationInMinutes)
    {
        $maxDuration = $this->getStatusEffectMaxDuration($status);

        $statusEffect = $pet->getStatusEffect($status);

        if(!$statusEffect)
        {
            $statusEffect = (new StatusEffect())
                ->setStatus($status);

            $pet->addStatusEffect($statusEffect);

            $this->em->persist($statusEffect);
        }

        $statusEffect
            ->setTotalDuration(min($maxDuration, $statusEffect->getTotalDuration() + $durationInMinutes))
            ->setTimeRemaining(min($statusEffect->getTotalDuration(), $statusEffect->getTimeRemaining() + $durationInMinutes))
        ;

        if($status === StatusEffectEnum::WEREFORM)
        {
            $itemsDropped = [];

            if(
                $pet->getTool() &&
                $pet->getTool()->getItem()->getTreasure() &&
                $pet->getTool()->getItem()->getTreasure()->getSilver() > 0
            )
            {
                $itemsDropped[] = $pet->getTool()->getFullItemName();
                $this->equipmentService->unequipPet($pet);
            }

            if(
                $pet->getHat() &&
                $pet->getHat()->getItem()->getTreasure() &&
                $pet->getHat()->getItem()->getTreasure()->getSilver() > 0
            )
            {
                $itemsDropped[] = $pet->getHat()->getFullItemName();
                $this->equipmentService->unhatPet($pet);
            }

            if(count($itemsDropped) > 0)
                $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% turned into a Werecreature, and immediately dropped their ' . ArrayFunctions::list_nice($itemsDropped) . '!', '');
            else
                $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% turned into a Werecreature!', '');
        }
    }

    public function getStatusEffectMaxDuration(string $status)
    {
        switch($status)
        {
            case StatusEffectEnum::CAFFEINATED:
                return 8 * 60;
            case StatusEffectEnum::EGGPLANT_CURSED:
                return 48 * 60;
            case StatusEffectEnum::HEX_HEXED:
                return 6 * 60;
            default:
                return 24 * 60;
        }
    }
}