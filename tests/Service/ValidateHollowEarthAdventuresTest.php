<?php

namespace Service;

use App\Entity\HollowEarthTileCard;
use App\Entity\Item;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use function PHPUnit\Framework\assertTrue;

/**
 * JUSTIFICATION: The JSON structure for adventures is easy to mess up, especially item names,
 * which are typed by hand. It's easy to make a typo. This test double-checks that adventure JSON
 * is valid.
 */
class ValidateHollowEarthAdventuresTest extends KernelTestCase
{
    /**
     * @group requiresDatabase
     */
    public function testHollowEarthAdventuresAreValid()
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
            self::validatePetChallengesAreLegit($em, $adventure->getName(), $event);
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

            self::assertNotNull($item, "Adventure \"{$adventureName}\" references item \"{$itemName}\" which does not exist.");
        }
    }

    private static function validatePetChallengesAreLegit(EntityManagerInterface $em, string $adventureName, array $event)
    {
        foreach($event as $key => $value)
        {
            if($key === 'petChallenge')
                self::validatePetChallenge($em, $adventureName, $value);
            else if(is_array($value))
                self::validatePetChallengesAreLegit($em, $adventureName, $value);
        }
    }

    private static function validatePetChallenge(EntityManagerInterface $em, string $adventureName, array $petChallenge)
    {
        assertTrue(array_key_exists('ifSuccess', $petChallenge), "Adventure \"{$adventureName}\" has a pet challenge without an \"ifSuccess\" key.");
    }
}