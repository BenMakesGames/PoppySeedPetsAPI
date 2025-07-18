<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\EnumInvalidValueException;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Enum\StatusEffectEnum;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ActivityHelpers;
use App\Functions\EnchantmentRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\SpiceRepository;
use App\Functions\StatusEffectHelpers;
use App\Model\ComputedPetSkills;
use App\Service\FieldGuideService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class StrangeUmbralEncounters
{
    public function __construct(
        private readonly PetExperienceService $petExperienceService,
        private readonly InventoryService $inventoryService,
        private readonly EntityManagerInterface $em,
        private readonly IRandom $rng,
        private readonly FieldGuideService $fieldGuideService
    )
    {
    }

    /**
     * @throws PSPNotFoundException
     * @throws EnumInvalidValueException
     */
    public function adventure(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $maxEncoutner = $pet->getLevel() >= 10 ? 3 : 2;

        return match ($this->rng->rngNextInt(1, $maxEncoutner))
        {
            1 => $this->encounterWildlife($pet),
            2 => $this->encounterCosmicGoat($pet),
            3 => $this->encounterAgares($pet),
            default => throw new \Exception('Ben messed up strange umbral encounters. That\'s bad, but he\'s been emailed, and should fix it soon. Sorry :|'),
        };
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function encounterWildlife(Pet $pet): PetActivityLog
    {
        $encounter = $this->getRandomWildlifeEncounter($pet);

        $tags = [ 'The Umbra' ];

        if(in_array(PetSkillEnum::Stealth, $encounter['skills']))
            $tags[] = 'Stealth';

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, 'While exploring the umbra, ' . ActivityHelpers::PetName($pet) . ' ' . $encounter['description'])
            ->addInterestingness(PetActivityLogInterestingness::UncommonActivity)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, $tags))
        ;

        $this->petExperienceService->gainExp($pet, 1, $encounter['skills'], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, false);

        return $activityLog;
    }

    private function getRandomWildlifeEncounter(Pet $pet): array
    {
        return $this->rng->rngNextFromArray([
            [
                'description' => 'saw a family of deer spirits running across the umbral sands. ' . ActivityHelpers::PetName($pet) . ' watched for a while before returning home.',
                'skills' => [ PetSkillEnum::Arcana ]
            ],
            [
                'description' => 'saw the Raccoon King and his entourage. ' . ActivityHelpers::PetName($pet) . ' hid behind some rocks and waited for them to pass...',
                'skills' => [ PetSkillEnum::Arcana, PetSkillEnum::Stealth ]
            ],
            [
                'description' => 'encountered a friendly wizard. They traveled for a while, the wizard making conversation, until they parted ways.',
                'skills' => [ PetSkillEnum::Arcana ]
            ],
            [
                'description' => 'found a large field of wild, oversized plants. ' . ActivityHelpers::PetName($pet) . ' explored the field for a while, avoiding the more aggressive-looking plants...',
                'skills' => [ PetSkillEnum::Arcana ]
            ],
            [
                'description' => 'felt the ground tremble. They looked around, but didn\'t see an obvious source of the shaking...',
                'skills' => [ PetSkillEnum::Arcana ]
            ],
            [
                'description' => 'felt a strange breeze. Looking up, they saw a flock (or maybe "school"?) of translucent creatures gliding overhead.',
                'skills' => [ PetSkillEnum::Arcana ]
            ]
        ]);
    }

    // Agares is a spirit-duke. now you know.

    /**
     * @throws PSPNotFoundException
     * @throws EnumInvalidValueException
     */
    private function encounterAgares(Pet $pet): PetActivityLog
    {
        $fate = $this->rng->rngNextFromArray([
            StatusEffectEnum::FatedDeliciously,
            StatusEffectEnum::FatedSoakedly,
            StatusEffectEnum::FatedElectrically,
            StatusEffectEnum::FatedFerally,
            StatusEffectEnum::FatedLunarly,
        ]);

        if($pet->getTool() && !$pet->getTool()->getEnchantment())
        {
            $enchantment = EnchantmentRepository::findOneByName($this->em, 'of Agares');

            $pet->getTool()
                ->setEnchantment($enchantment)
                ->addComment('This item was enchanted by an old man riding an alligator and holding a goshawk!')
            ;

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, 'While exploring some ruins in the Umbra, ' . '%pet:' . $pet->getId() . '.name% was approached by an old man riding an alligator and holding a goshawk. He said something, but it was in a language %pet:' . $pet->getId() . '.name% didn\'t know, but it was clearly a prediction of the future. %pet:' . $pet->getId() . '.name%\'s ' . $pet->getTool()->getItem()->getName() . ' began to glow, and the old man left...')
                ->addInterestingness(PetActivityLogInterestingness::UncommonActivity)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra' ]))
            ;
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, 'While exploring some ruins in the Umbra, ' . '%pet:' . $pet->getId() . '.name% was approached by an old man riding an alligator and holding a goshawk. He said something, but it was in a language %pet:' . $pet->getId() . '.name% didn\'t know, but it was clearly a prediction of the future.')
                ->addInterestingness(PetActivityLogInterestingness::UncommonActivity)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra' ]))
            ;
        }

        StatusEffectHelpers::applyStatusEffect($this->em, $pet, $fate, 1);

        $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, false);

        return $activityLog;
    }

    /**
     * @throws PSPNotFoundException
     * @throws EnumInvalidValueException
     */
    private function encounterCosmicGoat(Pet $pet): PetActivityLog
    {
        $discoveryMessage = 'While exploring the Umbra, some white rain started to fall. ' . '%pet:' . $pet->getId() . '.name% looked up, and saw the Cosmic Goat flying overhead, milk flowing from its udder.';

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $discoveryMessage . ' They gathered up as much of the "rain" as they could.')
            ->addInterestingness(PetActivityLogInterestingness::UncommonActivity)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra' ]))
        ;

        $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, false);

        $cosmic = SpiceRepository::findOneByName($this->em, 'Cosmic');

        $this->inventoryService->petCollectsEnhancedItem('Creamy Milk', null, $cosmic, $pet, $pet->getName() . ' collected this from the Cosmic Goat, who happened to fly overhead while ' . $pet->getName() . ' was exploring the Umbra.', $activityLog);

        $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Cosmic Goat', $discoveryMessage);

        return $activityLog;
    }
}
