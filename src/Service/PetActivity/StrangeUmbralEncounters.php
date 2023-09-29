<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ActivityHelpers;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\StatusEffectHelpers;
use App\Model\ComputedPetSkills;
use App\Repository\EnchantmentRepository;
use App\Repository\SpiceRepository;
use App\Service\FieldGuideService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class StrangeUmbralEncounters
{

    private PetExperienceService $petExperienceService;
    private InventoryService $inventoryService;
    private EntityManagerInterface $em;
    private IRandom $squirrel3;
    private FieldGuideService $fieldGuideService;

    public function __construct(
        PetExperienceService $petExperienceService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $squirrel3, FieldGuideService $fieldGuideService
    )
    {
        $this->petExperienceService = $petExperienceService;
        $this->inventoryService = $inventoryService;
        $this->em = $em;
        $this->squirrel3 = $squirrel3;
        $this->fieldGuideService = $fieldGuideService;
    }

    public function adventure(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $maxEncoutner = $pet->getLevel() >= 10 ? 3 : 2;

        switch($this->squirrel3->rngNextInt(1, $maxEncoutner))
        {
            case 1:
                return $this->encounterWildlife($pet);
            case 2:
                return $this->encounterCosmicGoat($pet);
            case 3:
                return $this->encounterAgares($pet);
        }

        throw new \Exception('Ben messed up strange umbral encounters. That\'s bad, but he\'s been emailed, and should fix it soon. Sorry :|');
    }

    private function encounterWildlife(Pet $pet): PetActivityLog
    {
        $encounter = $this->getRandomWildlifeEncounter($pet);

        $tags = [ 'The Umbra' ];

        if(in_array(PetSkillEnum::STEALTH, $encounter['skills']))
            $tags[] = 'Stealth';

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, 'While exploring the umbra, ' . ActivityHelpers::PetName($pet) . ' ' . $encounter['description'])
            ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, $tags))
        ;

        $this->petExperienceService->gainExp($pet, 1, $encounter['skills'], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, false);

        return $activityLog;
    }

    private function getRandomWildlifeEncounter(Pet $pet): array
    {
        return $this->squirrel3->rngNextFromArray([
            [
                'description' => 'saw a family of deer spirits running across the umbral sands. ' . ActivityHelpers::PetName($pet) . ' watched for a while before returning home.',
                'skills' => [ PetSkillEnum::ARCANA ]
            ],
            [
                'description' => 'saw the Raccoon King and his entourage. ' . ActivityHelpers::PetName($pet) . ' hid behind some rocks and waited for them to pass...',
                'skills' => [ PetSkillEnum::ARCANA, PetSkillEnum::STEALTH ]
            ],
            [
                'description' => 'encountered a friendly wizard. They traveled for a while, the wizard making conversation, until they parted ways.',
                'skills' => [ PetSkillEnum::ARCANA ]
            ],
            [
                'description' => 'found a large field of wild, oversized plants. ' . ActivityHelpers::PetName($pet) . ' explored the field for a while, avoiding the more aggressive-looking plants...',
                'skills' => [ PetSkillEnum::ARCANA ]
            ],
            [
                'description' => 'felt the ground tremble. They looked around, but didn\'t see an obvious source of the shaking...',
                'skills' => [ PetSkillEnum::ARCANA ]
            ],
            [
                'description' => 'felt a strange breeze. Looking up, they saw a flock (or maybe "school"?) of translucent creatures gliding overhead.',
                'skills' => [ PetSkillEnum::ARCANA ]
            ]
        ]);
    }

    // Agares is a spirit-duke. now you know.
    private function encounterAgares(Pet $pet): PetActivityLog
    {
        $fate = $this->squirrel3->rngNextFromArray([
            StatusEffectEnum::FATED_DELICIOUSNESS,
            StatusEffectEnum::FATED_SOAKEDLY,
            StatusEffectEnum::FATED_ELECTRICALLY,
            StatusEffectEnum::FATED_FERALLY,
            StatusEffectEnum::FATED_LUNARLY,
        ]);

        if($pet->getTool() && !$pet->getTool()->getEnchantment())
        {
            $enchantment = EnchantmentRepository::findOneByName($this->em, 'of Agares');

            $pet->getTool()
                ->setEnchantment($enchantment)
                ->addComment('This item was enchanted by an old man riding an alligator and holding a goshawk!')
            ;

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, 'While exploring some ruins in the Umbra, ' . '%pet:' . $pet->getId() . '.name% was approached by an old man riding an alligator and holding a goshawk. He said something, but it was in a language %pet:' . $pet->getId() . '.name% didn\'t know, but it was clearly a prediction of the future. %pet:' . $pet->getId() . '.name%\'s ' . $pet->getTool()->getItem()->getName() . ' began to glow, and the old man left...')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra' ]))
            ;
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, 'While exploring some ruins in the Umbra, ' . '%pet:' . $pet->getId() . '.name% was approached by an old man riding an alligator and holding a goshawk. He said something, but it was in a language %pet:' . $pet->getId() . '.name% didn\'t know, but it was clearly a prediction of the future.')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra' ]))
            ;
        }

        StatusEffectHelpers::applyStatusEffect($this->em, $pet, $fate, 1);

        $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::ARCANA ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, false);

        return $activityLog;
    }

    private function encounterCosmicGoat(Pet $pet): PetActivityLog
    {
        $discoveryMessage = 'While exploring the Umbra, some white rain started to fall. ' . '%pet:' . $pet->getId() . '.name% looked up, and saw the Cosmic Goat flying overhead, milk flowing from its udder.';

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $discoveryMessage . ' They gathered up as much of the "rain" as they could.')
            ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'The Umbra' ]))
        ;

        $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::ARCANA ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, false);

        $cosmic = SpiceRepository::findOneByName($this->em, 'Cosmic');

        $this->inventoryService->petCollectsEnhancedItem('Creamy Milk', null, $cosmic, $pet, $pet->getName() . ' collected this from the Cosmic Goat, who happened to fly overhead while ' . $pet->getName() . ' was exploring the Umbra.', $activityLog);

        $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Cosmic Goat', $discoveryMessage);

        return $activityLog;
    }
}
