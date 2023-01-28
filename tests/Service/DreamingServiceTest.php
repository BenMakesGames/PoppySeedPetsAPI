<?php
namespace App\Tests\Service;

use App\Entity\Item;
use App\Entity\Pet;
use App\Entity\PetSpecies;
use App\Repository\DreamRepository;
use App\Service\PetActivity\DreamingService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DreamingServiceTest extends KernelTestCase
{
    public function testDreamDescriptions()
    {
        self::bootKernel();

        $container = self::$container;

        /** @var DreamingService $dreamingService */
        $dreamingService = $container->get(DreamingService::class);

        /** @var DreamRepository $dreamRepository */
        $dreamRepository = $container->get(DreamRepository::class);

        $dreams = $dreamRepository->findAll();

        $dummyItem = new Item();
        $dummyItem->setName('Dummy Item');

        $dummyDreamer = new Pet();
        $dummyDreamer->setName('Dreamer');

        $dummySpecies = new PetSpecies();
        $dummySpecies->setName('Dummy Species');

        $replacements = $dreamingService->generateReplacementsDictionary($dummyItem, $dummyDreamer, $dummySpecies);

        foreach($dreams as $dream)
        {
            $descriptionResult = DreamingService::applyMadlib($dream->getDescription(), $replacements);
            $itemCommentTextResult = DreamingService::applyMadlib($dream->getItemDescription(), $replacements);

            $this->assertEquals(false, strpos($descriptionResult, '%'), 'After applying madlibs, there should be no remaining % signs... BUT THERE WERE, in Dream #' . $dream->getId() . '\'s activity log description: "' . $descriptionResult . '"');
            $this->assertEquals(false, strpos($itemCommentTextResult, '%'), 'After applying madlibs, there should be no remaining % signs... BUT THERE WERE, in Dream #' . $dream->getId() . '\'s item comment text: "' . $itemCommentTextResult . '"');
        }
    }
}
