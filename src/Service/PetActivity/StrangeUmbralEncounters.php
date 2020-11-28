<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Model\ComputedPetSkills;
use App\Repository\EnchantmentRepository;
use App\Repository\SpiceRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;

class StrangeUmbralEncounters
{

    private $responseService;
    private $petExperienceService;
    private $inventoryService;
    private $enchantmentRepository;
    private $spiceRepository;

    public function __construct(
        ResponseService $responseService, PetExperienceService $petExperienceService, InventoryService $inventoryService,
        EnchantmentRepository $enchantmentRepository, SpiceRepository $spiceRepository
    )
    {
        $this->responseService = $responseService;
        $this->petExperienceService = $petExperienceService;
        $this->inventoryService = $inventoryService;
        $this->enchantmentRepository = $enchantmentRepository;
        $this->spiceRepository = $spiceRepository;
    }

    public function adventure(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        switch(mt_rand(1, 2))
        {
            case 1:
                return $this->encounterAgares($pet);
            case 2:
                return $this->encounterCosmicGoat($pet);
        }

        throw new \Exception('Ben messed up strange umbral encounters. That\'s bad, but he\'s been emailed, and will fix it soon. Sorry :|');
    }

    // Agares is a spirit-duke. now you know.
    private function encounterAgares(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);

        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::UMBRA, false);

        if($pet->getTool() && !$pet->getTool()->getEnchantment())
        {
            $enchantment = $this->enchantmentRepository->findOneByName('of Agares');

            $pet->getTool()
                ->setEnchantment($enchantment)
                ->addComment('This item was enchanted by an old man riding an alligator and holding a goshawk!')
            ;

            return $this->responseService->createActivityLog($pet, 'While exploring some ruins in the Umbra, ' . $pet->getName() . ' was approached by an old man riding an alligator and holding a goshawk. He said something, but it was in a language ' . $pet->getName() . ' didn\'t know. ' . $pet->getName() . '\'s ' . $pet->getTool()->getItem()->getName() . ' began to glow, and the old man left...', '');
        }
        else
        {
            return $this->responseService->createActivityLog($pet, 'While exploring some ruins in the Umbra, ' . $pet->getName() . ' was approached by an old man riding an alligator and holding a goshawk. He said something, but it was in a language ' . $pet->getName() . ' didn\'t know. Frustrated, the old man left.', '');
        }
    }

    private function encounterCosmicGoat(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);

        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::UMBRA, false);

        $activityLog = $this->responseService->createActivityLog($pet, 'While exploring the Umbra, some white rain started to fall. ' . $pet->getName() . ' looked up, and saw the Cosmic Goat flying overhead, milk flowing from its udder. They gathered up as much of the "rain" as they could.', '');

        $cosmic = $this->spiceRepository->findOneByName('Cosmic');

        $this->inventoryService->petCollectsEnhancedItem('Creamy Milk', null, $cosmic, $pet, $pet->getName() . ' collected this from the Cosmic Goat, who happened to fly overhead while ' . $pet->getName() . ' was exploring the Umbra.', $activityLog);

        return $activityLog;
    }
}
