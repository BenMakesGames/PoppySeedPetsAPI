<?php
namespace Service;

use App\Entity\Item;
use App\Entity\Pet;
use App\Entity\PetSpecies;
use App\Functions\RecipeRepository;
use App\Repository\DreamRepository;
use App\Service\PetActivity\DreamingService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DuplicateRecipeTest extends KernelTestCase
{
    public function testDreamDescriptions()
    {
        $seenIngredients = [];

        foreach(RecipeRepository::RECIPES as $recipe)
        {
            if(array_key_exists($recipe['ingredients'], $seenIngredients))
                $this->fail("Duplicate recipes found: \"{$recipe['name']}\" and \"{$seenIngredients[$recipe['ingredients']]}\".");
            else
                $seenIngredients[$recipe['ingredients']] = $recipe['name'];
        }
    }
}
