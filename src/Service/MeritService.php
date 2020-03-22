<?php
namespace App\Service;

use App\Entity\Merit;
use App\Entity\Pet;
use App\Enum\EnumInvalidValueException;
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
     * @return string[]
     */
    public function getUnlearnableMerits(Pet $pet): array
    {
        $petMerits = array_map(function(Merit $m) { return $m->getName(); }, $pet->getMerits()->toArray());
        $canUnlearn = array_values(array_intersect($petMerits, [
            MeritEnum::INTROSPECTIVE,
            MeritEnum::PROTOCOL_7,
            MeritEnum::NATURAL_CHANNEL,
            MeritEnum::SOOTHING_VOICE,
            MeritEnum::BLACK_HOLE_TUM,
            MeritEnum::EIDETIC_MEMORY,
            MeritEnum::MOON_BOUND,
            MeritEnum::NO_SHADOW_OR_REFLECTION,
        ]));

        if(!$pet->getPregnancy() && $pet->hasMerit(MeritEnum::VOLAGAMY))
            $canUnlearn[] = MeritEnum::VOLAGAMY;

        return $canUnlearn;
    }

    /**
     * @return Merit[]
     * @throws EnumInvalidValueException
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

                case MeritEnum::PROTOCOL_7:
                    $available = $pet->getSkills()->getScience() > 0 || $pet->getLevel() >= 10;
                    break;

                case MeritEnum::NATURAL_CHANNEL:
                    $available = $pet->getSkills()->getUmbra() > 0 || $pet->getLevel() >= 10;
                    break;

                case MeritEnum::SOOTHING_VOICE:
                    $available = $pet->getSkills()->getMusic() > 0 || count($pet->getMerits()) >= 3;
                    break;

                case MeritEnum::BLACK_HOLE_TUM:
                case MeritEnum::EIDETIC_MEMORY:
                case MeritEnum::NO_SHADOW_OR_REFLECTION:
                    $available = $pet->getSkills()->getTalent() !== null;
                    break;

                // these merits are ALWAYS available
                case MeritEnum::MOON_BOUND:
                case MeritEnum::SPIRIT_COMPANION:
                case MeritEnum::LUCKY:
                    $available = true;
                    break;

                // all other Merits can NEVER be chosen:
                default:
                    $available = false;
            }

            if($available)
                $availableMerits[] = $this->meritRepository->findOneByName($merit);
        }

        return $availableMerits;
    }
}
