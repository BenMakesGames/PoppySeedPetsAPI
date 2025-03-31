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

use App\Functions\CalendarFunctions;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * JUSTIFICATION: Easter math is historically complicated. PHP actually added built-in methods for
 * computing Easter, which PSP now uses, so deleting this test would not be unreasonable.
 */
class IsEasterTest extends KernelTestCase
{
    public function testIsEaster()
    {
        self::assertFalse(CalendarFunctions::isEaster(new \DateTime('2024-03-28')), '2024-03-28 MUST NOT BE Easter');
        self::assertTrue(CalendarFunctions::isEaster(new \DateTime('2024-03-29')), '2024-03-29 MUST BE Easter');
        self::assertTrue(CalendarFunctions::isEaster(new \DateTime('2024-03-30')), '2024-03-30 MUST BE Easter');
        self::assertTrue(CalendarFunctions::isEaster(new \DateTime('2024-03-31')), '2024-03-31 MUST BE Easter');
        self::assertTrue(CalendarFunctions::isEaster(new \DateTime('2024-03-31 12:00:00')), '2024-03-31 @Noon MUST BE Easter');
        self::assertTrue(CalendarFunctions::isEaster(new \DateTime('2024-03-31 23:59:59')), '2024-03-31 @23:59:59 MUST BE Easter');
        self::assertFalse(CalendarFunctions::isEaster(new \DateTime('2024-04-01')), '2024-04-01 MUST NOT BE Easter');
    }
}