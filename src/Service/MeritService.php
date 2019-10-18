<?php
namespace App\Service;

use App\Entity\Merit;
use App\Entity\Pet;
use App\Enum\MeritEnum;
use App\Repository\MeritRepository;

class MeritService
{
    private $meritRepository;

    public function __construct(MeritRepository $meritRepository)
    {
        $this->meritRepository = $meritRepository;
    }

    /**
     * @return Merit[]
     */
    public function getAvailableMerits(Pet $pet): array
    {
        /** @var Merit[] $availableMerits */
        $availableMerits = [];

        foreach(MeritEnum::getValues() as $merit)
        {
            if($pet->hasMerit($merit))
                continue;

            switch($merit)
            {
                case MeritEnum::VOLAGAMY:
                    $available = (new \DateTimeImmutable())->diff($pet->getBirthDate())->days >= 14;
                    break;

                case MeritEnum::INTROSPECTIVE:
                    $available = $pet->getRelationshipCount() >= 3;
                    break;

                case MeritEnum::NO_SHADOW_OR_REFLECTION:
                    $available = $pet->hasMerit(MeritEnum::MOON_BOUND);
                    break;

                case MeritEnum::PROTOCOL_7:
                    $available = $pet->getComputer() > 0 || $pet->getLevel() >= 10;
                    break;

                default:
                    $available = true;
            }

            if($available)
                $availableMerits[] = $this->meritRepository->findOneByName($merit);
        }

        return $availableMerits;
    }
}