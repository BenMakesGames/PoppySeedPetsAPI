<?php
namespace App\Command;

use App\Entity\Enchantment;
use App\Entity\HollowEarthTileCard;
use App\Entity\Item;
use App\Entity\ItemFood;
use App\Entity\ItemGrammar;
use App\Entity\ItemHat;
use App\Entity\ItemTool;
use App\Entity\ItemTreasure;
use App\Entity\Plant;
use App\Entity\PlantYield;
use App\Entity\PlantYieldItem;
use App\Entity\Recipe;
use App\Entity\Spice;
use App\Enum\AffectionExpressionEnum;
use App\Repository\ItemRepository;
use App\Repository\PetRepository;
use App\Repository\RecipeRepository;
use App\Service\IRandom;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;

class AssignAffectionExpressionsCommand extends PoppySeedPetsCommand
{
    private EntityManagerInterface $em;
    private PetRepository $petRepository;
    private IRandom $rng;

    public function __construct(
        EntityManagerInterface $em, PetRepository $petRepository, Squirrel3 $rng
    )
    {
        $this->em = $em;
        $this->petRepository = $petRepository;
        $this->rng = $rng;

        parent::__construct();
    }

    protected function configure()
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
            $pets = $this->petRepository->findBy([ 'affectionExpressions' => '' ], null, 200);

            foreach ($pets as $pet) {
                $pet->setAffectionExpressions(join('', $this->rng->rngNextSubsetFromArray(AffectionExpressionEnum::getValues(), 3)));
            }

            $this->em->flush();
            $this->em->clear();
        } while(count($pets) > 0);

        return 0;
    }
}