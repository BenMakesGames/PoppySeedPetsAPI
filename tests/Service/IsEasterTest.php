<?php

namespace Service;

use App\Functions\CalendarFunctions;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IsEasterTest extends KernelTestCase
{
    public function testIsEaster()
    {
        self::assertFalse(CalendarFunctions::isEaster(new \DateTime('2024-03-28')), '2024-03-28 MUST NOT BE Easter');
        self::assertTrue(CalendarFunctions::isEaster(new \DateTime('2024-03-29')), '2024-03-29 MUST BE Easter');
        self::assertTrue(CalendarFunctions::isEaster(new \DateTime('2024-03-30')), '2024-03-30 MUST BE Easter');
        self::assertTrue(CalendarFunctions::isEaster(new \DateTime('2024-03-31')), '2024-03-31 MUST BE Easter');
        self::assertFalse(CalendarFunctions::isEaster(new \DateTime('2024-04-01')), '2024-04-01 MUST NOT BE Easter');

        self::assertFalse(CalendarFunctions::isEaster(new \DateTime('2025-04-17')), '2024-04-17 MUST NOT BE Easter');
        self::assertTrue(CalendarFunctions::isEaster(new \DateTime('2025-04-18')), '2024-04-18 MUST BE Easter');
        self::assertTrue(CalendarFunctions::isEaster(new \DateTime('2025-04-19')), '2024-04-19 MUST BE Easter');
        self::assertTrue(CalendarFunctions::isEaster(new \DateTime('2025-04-20')), '2024-04-20 MUST BE Easter');
        self::assertFalse(CalendarFunctions::isEaster(new \DateTime('2025-04-21')), '2024-04-21 MUST NOT BE Easter');
    }
}