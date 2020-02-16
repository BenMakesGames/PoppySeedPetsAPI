<?php
namespace App\Command;

use App\Entity\Pet;
use App\Entity\PetSkills;
use App\Enum\LocationEnum;
use App\Enum\LoveLanguageEnum;
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

class AssignLoveLanguagesCommand extends Command
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
            ->setName('app:assign-love-languages')
            ->setDescription('Assigns love languages to pets without one.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $languages = LoveLanguageEnum::getValues();

        $i = 0;
        foreach($languages as $language)
        {
            $this->em->getConnection()->executeQuery('
                UPDATE pet
                SET love_language="' . $language . '"
                WHERE love_language="" AND id % ' . count($languages) . ' = ' . $i . '
            ');
            $i++;
        }

        $this->em->flush();
    }
}
