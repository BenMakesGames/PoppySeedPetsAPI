<?php

namespace MonsterOfTheWeek;

use App\Controller\MonsterOfTheWeek\MonsterOfTheWeekHelpers;
use App\Entity\Item;
use App\Enum\MonsterOfTheWeekEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ValidateMonsterOfTheWeekPrizesTest extends KernelTestCase
{
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