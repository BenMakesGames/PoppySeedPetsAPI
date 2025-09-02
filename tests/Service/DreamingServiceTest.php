<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace Service;

use App\Entity\Item;
use App\Entity\Pet;
use App\Entity\PetSkills;
use App\Entity\PetSpecies;
use App\Entity\User;
use App\Service\PetActivity\DreamingService;
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
    public function testDreamDescriptions(): void
    {
        self::bootKernel();

        $container = self::getContainer();

        /** @var DreamingService $dreamingService */
        $dreamingService = $container->get(DreamingService::class);

        $dreams = DreamingService::Dreams;

        $dummyItem = new Item();
        $dummyItem->setName('Dummy Item');

        $dummyDreamer = new Pet(new PetSpecies(), new User('any name', 'any@email.com'), new PetSkills());
        $dummyDreamer->setName('Dreamer');

        $dummySpecies = new PetSpecies();
        $dummySpecies->setName('Dummy Species');

        $replacements = $dreamingService->generateReplacementsDictionary($dummyItem, $dummyDreamer, $dummySpecies);

        foreach($dreams as $dream)
        {
            $descriptionResult = DreamingService::applyMadlib($dream['description'], $replacements);
            $itemCommentTextResult = DreamingService::applyMadlib($dream['itemDescription'], $replacements);

            assertFalse(mb_strpos($descriptionResult, '%'), 'After applying madlibs, there should be no remaining % signs... BUT THERE WERE, in activity log description: "' . $descriptionResult . '"');
            assertFalse(mb_strpos($itemCommentTextResult, '%'), 'After applying madlibs, there should be no remaining % signs... BUT THERE WERE, in item comment text: "' . $itemCommentTextResult . '"');
        }
    }
}
