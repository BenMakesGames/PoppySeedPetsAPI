<?php
namespace App\Command;

use App\Repository\MeritRepository;
use Doctrine\ORM\EntityManagerInterface;

class GivePetsStartingMeritsCommand extends PoppySeedPetsCommand
{
    private $meritRepository;
    private $em;

    public function __construct(MeritRepository $meritRepository, EntityManagerInterface $em)
    {
        $this->meritRepository = $meritRepository;
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:give-pets-starting-merits')
            ->setDescription('Gives every pet an initial merit.')
        ;
    }

    protected function doCommand()
    {
        $pets = $this->em->createQuery('SELECT p FROM App\\Entity\\Pet p')->iterate();

        $i = 0;
        foreach($pets as $pet)
        {
            $pet[0]->addMerit($this->meritRepository->getRandomStartingMerit());

            $i++;

            if($i % 100 === 0)
            {
                echo $i . '... ';
                $this->em->flush();
                $this->em->clear();
            }
        }

        $this->em->flush();
        $this->em->clear();

        echo 'done!' . "\n";
    }
}
