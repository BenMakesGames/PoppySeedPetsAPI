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


namespace MonsterOfTheWeek;

use App\Controller\MonsterOfTheWeek\MonsterOfTheWeekHelpers;
use App\Entity\Item;
use App\Enum\MonsterOfTheWeekEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * JUSTIFICATION: The Monster of the Week prizes are hand-typed; it'd be easy to typo one.
 */
class ValidateMonsterOfTheWeekPrizesTest extends KernelTestCase
{
    /**
     * @group requiresDatabase
     */
    public function testMonsterOfTheWeekPrizesAreValid()
    {
        self::bootKernel();

        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $monsterTypes = MonsterOfTheWeekEnum::getValues();

        foreach($monsterTypes as $monsterType)
        {
            self::validateItemNames($em, MonsterOfTheWeekHelpers::getConsolationPrize($monsterType));
            self::validateItemNames($em, MonsterOfTheWeekHelpers::getEasyPrizes($monsterType));
            self::validateItemNames($em, MonsterOfTheWeekHelpers::getMediumPrizes($monsterType));
            self::validateItemNames($em, MonsterOfTheWeekHelpers::getHardPrizes($monsterType));
        }
    }

    private static function validateItemNames(EntityManagerInterface $em, array|string $itemNames)
    {
        if(!is_array($itemNames))
            $itemNames = [ $itemNames ];

        foreach($itemNames as $itemName)
        {
            $item = $em->getRepository(Item::class)->findOneBy([ 'name' => $itemName ]);

            self::assertNotNull($item, "The item \"{$itemName}\" does not exist.");
        }
    }
}