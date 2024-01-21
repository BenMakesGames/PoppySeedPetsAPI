<?php

namespace App\Tests\Service;

use App\Entity\HollowEarthTileCard;
use App\Entity\Item;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ValidateHollowEarthAdventuresTest extends KernelTestCase
{
    public function testItemNamesAreValid()
    {
        self::bootKernel();

        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $adventures = $em->getRepository(HollowEarthTileCard::class)->findAll();

        foreach($adventures as $adventure)
        {
            $event = $adventure->getEvent();

            self::validateItemNamesAreLegit($em, $adventure->getName(), $event);
        }
    }

    private static function validateItemNamesAreLegit(EntityManagerInterface $em, string $adventureName, array $event)
    {
        foreach($event as $key => $value)
        {
            if($key === 'receiveItems')
                self::validateItemNames($em, $adventureName, $value);
            else if(is_array($value))
                self::validateItemNamesAreLegit($em, $adventureName, $value);
        }
    }

    private static function validateItemNames(EntityManagerInterface $em, string $adventureName, array|string $itemNames)
    {
        if(!is_array($itemNames))
            $itemNames = [ $itemNames ];

        foreach($itemNames as $itemName)
        {
            $item = $em->getRepository(Item::class)->findOneBy([ 'name' => $itemName ]);

            if(!$item)
                self::fail("Adventure \"{$adventureName}\" references item \"{$itemName}\" which does not exist.");
        }
    }
}