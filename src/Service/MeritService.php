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

                case MeritEnum::PROTOCOL_7:
                    $available = $pet->getSkills()->getComputer() > 0 || $pet->getLevel() >= 10;
                    break;

                case MeritEnum::NATURAL_CHANNEL:
                    $available = $pet->getSkills()->getUmbra() > 0 || $pet->getLevel() >= 10;
                    break;

                case MeritEnum::SOOTHING_VOICE:
                    $available = $pet->getSkills()->getMusic() > 0 || count($pet->getMerits()) >= 3;
                    break;

                case MeritEnum::BLACK_HOLE_TUM:
                    $available = $pet->getSkills()->getStamina() >= 4;
                    break;

                case MeritEnum::EIDETIC_MEMORY:
                    $available = $pet->getSkills()->getIntelligence() >= 4;
                    break;

                // these Merits may NEVER be chosen; they are gained in other ways:
                case MeritEnum::MODERATION:
                case MeritEnum::MIND_OVER_MATTER:
                case MeritEnum::MATTER_OVER_MIND:
                case MeritEnum::BALANCE:
                case MeritEnum::FORCE_OF_WILL:
                case MeritEnum::FORCE_OF_NATURE:
                case MeritEnum::BEHATTED:
                    $available = false;
                    break;

                // all other Merits can ALWAYS be chosen:
                default:
                    // moon-bound, spirit companion, no shadow or reflection
                    $available = true;
            }

            if($available)
                $availableMerits[] = $this->meritRepository->findOneByName($merit);
        }

        return $availableMerits;
    }
}