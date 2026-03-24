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

namespace Functions;

use App\Functions\PetColorFunctions;
use PHPUnit\Framework\TestCase;

class PetColorFunctionsTest extends TestCase
{
    public function testAdjustHue(): void
    {
        // Red is H=0
        $red = 'ff0000';
        
        // Adjust by 0.5 should give Cyan (H=0.5)
        $cyan = PetColorFunctions::adjustHue($red, 0.5);
        $this->assertEquals('00ffff', $cyan);

        // Adjust by 1.1 should be same as adjusting by 0.1
        $orange = PetColorFunctions::adjustHue($red, 1.1);
        // H=0.1 in HSL is roughly #ff9900 in some converters, but let's be more precise if we can.
        // Let's just check if it's consistent.
        $orange2 = PetColorFunctions::adjustHue($red, 0.1);
        $this->assertEquals($orange2, $orange);
        
        // Negative adjustment
        $magenta = PetColorFunctions::adjustHue($red, -1/6); // -0.1666...
        // H = 1 - 1/6 = 5/6 = 0.8333... which is Magenta
        $this->assertEquals('ff00ff', $magenta);
    }
}
