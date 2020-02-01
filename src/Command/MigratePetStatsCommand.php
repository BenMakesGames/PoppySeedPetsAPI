<?php
namespace App\Command;

use App\Entity\Pet;
use App\Entity\PetSkills;
use App\Enum\LocationEnum;
use App\Enum\PetSkillEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Repository\PetRepository;
use App\Repository\PetSkillsRepository;
use App\Repository\UserRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigratePetStatsCommand extends Command
{
    private $em;
    private $petSkillsRepository;
    private $inventoryService;

    public function __construct(
        EntityManagerInterface $em, PetSkillsRepository $petSkillsRepository, InventoryService $inventoryService
    )
    {
        $this->em = $em;
        $this->petSkillsRepository = $petSkillsRepository;
        $this->inventoryService = $inventoryService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:migrate-pet-stats')
            ->setDescription('Migrates pet stats.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $increased = 0;
        $decreased = 0;
        $maxLevel = 0;

        /** @var PetSkills[] $pets */
        $skills = $this->petSkillsRepository->findAll();

        foreach($skills as $skill)
        {
            if($skill->getStamina() + $skill->getStrength() + $skill->getDexterity() + $skill->getIntelligence() + $skill->getPerception() < 5)
            {
                while($skill->getStamina() + $skill->getStrength() + $skill->getDexterity() + $skill->getIntelligence() + $skill->getPerception() < 5)
                {
                    $skill->increaseStat(ArrayFunctions::pick_one([
                        'strength', 'stamina', 'dexterity', 'intelligence', 'perception'
                    ]));
                }

                $increased++;
            }
            else if($skill->getStamina() + $skill->getStrength() + $skill->getDexterity() + $skill->getIntelligence() + $skill->getPerception() > 5)
            {
                $pointsAvailable = [];

                for($x = 0; $x < $skill->getStrength(); $x++) $pointsAvailable[] = 'strength';
                for($x = 0; $x < $skill->getStamina(); $x++) $pointsAvailable[] = 'stamina';
                for($x = 0; $x < $skill->getDexterity(); $x++) $pointsAvailable[] = 'dexterity';
                for($x = 0; $x < $skill->getIntelligence(); $x++) $pointsAvailable[] = 'intelligence';
                for($x = 0; $x < $skill->getPerception(); $x++) $pointsAvailable[] = 'perception';

                while(count($pointsAvailable) > 5)
                {
                    $i = array_rand($pointsAvailable);

                    switch($pointsAvailable[$i])
                    {
                        case 'strength':
                            $skill->setStrength($skill->getStrength() - 1);
                            $skill->increaseStat(PetSkillEnum::BRAWL);
                            break;

                        case 'stamina':
                            $skill->setStamina($skill->getStamina() - 1);
                            $skill->increaseStat(ArrayFunctions::pick_one([ PetSkillEnum::BRAWL, PetSkillEnum::BRAWL, PetSkillEnum::BRAWL, PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA ]));
                            break;

                        case 'dexterity':
                            $skill->setDexterity($skill->getDexterity() - 1);
                            $skill->increaseStat(ArrayFunctions::pick_one([ PetSkillEnum::CRAFTS, PetSkillEnum::NATURE, PetSkillEnum::BRAWL ]));
                            break;

                        case 'intelligence':
                            $skill->setIntelligence($skill->getIntelligence() - 1);
                            $skill->increaseStat(ArrayFunctions::pick_one([ PetSkillEnum::CRAFTS, PetSkillEnum::CRAFTS, PetSkillEnum::CRAFTS, PetSkillEnum::UMBRA, PetSkillEnum::SCIENCE ]));
                            break;

                        case 'perception':
                            $skill->setPerception($skill->getPerception() - 1);
                            $skill->increaseStat(ArrayFunctions::pick_one([ PetSkillEnum::NATURE, PetSkillEnum::NATURE, PetSkillEnum::STEALTH ]));
                            break;
                    }

                    unset($pointsAvailable[$i]);
                }

                $decreased++;
            }

            $level = $skill->getTotal();

            $skill->getPet()->setTimeSpent(max(0, $level * 2500 + mt_rand(-10, 15) * 100));

            $maxLevel = max($level, $maxLevel);
        }

        echo 'Increased the stats of ' . $increased . ' pets' . "\n";
        echo 'Decreased the stats of ' . $decreased . ' pets' . "\n";
        echo 'Max pet level is ' . $maxLevel . "\n";

        $this->em->flush();
    }
}
