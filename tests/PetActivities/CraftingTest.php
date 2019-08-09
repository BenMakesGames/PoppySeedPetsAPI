<?php
namespace App\Tests\PetActivities;

use App\Entity\Pet;
use App\Entity\PetSkills;
use App\Entity\User;
use App\Service\PetActivity\CraftingService;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class CraftingTest extends TestCase
{
    private $craftingService;

    public function __construct(CraftingService $craftingService)
    {
        $this->craftingService = $craftingService;
    }

    // TODO: how the fuck do we do this? >_>
    public function testExtractFromScales()
    {
        // run it 100 times, and check that the distribution is rightish
        // 1. we should get a few of each type of result
        // 2. the proper items should be removed and/or added in each case
        // 3. the pet should gain experience as a result, no matter the action
        // but how to check these things??
        for($i = 0; $i < 100; $i++)
        {
            $pet = (new Pet())
                ->setSkills(
                    (new PetSkills())
                        ->setIntelligence(10)
                )
                ->setOwner(
                    (new User())
                )
            ;

            $this->craftingService->extractFromScales($pet);
        }
    }
}