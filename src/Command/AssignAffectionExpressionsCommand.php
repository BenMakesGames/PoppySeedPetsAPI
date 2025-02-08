<?php
declare(strict_types=1);

namespace App\Command;

use App\Entity\Pet;
use App\Service\IRandom;
use Doctrine\ORM\EntityManagerInterface;

class AssignAffectionExpressionsCommand extends PoppySeedPetsCommand
{
    private EntityManagerInterface $em;
    private IRandom $rng;

    public function __construct(
        EntityManagerInterface $em, IRandom $rng
    )
    {
        $this->em = $em;
        $this->rng = $rng;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:assign-affection-expressions')
            ->setDescription('Assign expressions to all pets that don\'t have them.')
        ;
    }

    protected function doCommand(): int
    {
        do
        {
            $pets = $this->em->getRepository(Pet::class)->findBy([ 'affectionExpressions' => '' ], null, 200);

            foreach ($pets as $pet) {
                $pet->assignAffectionExpressions($this->rng);
            }

            $this->em->flush();
            $this->em->clear();
        } while(count($pets) > 0);

        return 0;
    }
}