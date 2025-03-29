<?php
namespace Service;

use App\Entity\Dream;
use App\Entity\Item;
use App\Entity\Pet;
use App\Entity\PetSpecies;
use App\Service\PetActivity\DreamingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use function PHPUnit\Framework\assertFalse;

/**
 * JUSTIFICATION: It's easy to accidentally write a dream madlib with incorrect %placeholder%s.
 * This test makes sure you don't do that.
 */
class DreamingServiceTest extends KernelTestCase
{
    /**
     * @group requiresDatabase
     */
    public function testDreamDescriptions()
    {
        self::bootKernel();

        $container = self::getContainer();

        /** @var DreamingService $dreamingService */
        $dreamingService = $container->get(DreamingService::class);

        $dreams = $container
            ->get(EntityManagerInterface::class)
            ->getRepository(Dream::class)
            ->findAll()
        ;

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

            assertFalse(mb_strpos($descriptionResult, '%'), 'After applying madlibs, there should be no remaining % signs... BUT THERE WERE, in Dream #' . $dream->getId() . '\'s activity log description: "' . $descriptionResult . '"');
            assertFalse(mb_strpos($itemCommentTextResult, '%'), 'After applying madlibs, there should be no remaining % signs... BUT THERE WERE, in Dream #' . $dream->getId() . '\'s item comment text: "' . $itemCommentTextResult . '"');
        }
    }
}
