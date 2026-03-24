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

namespace App\Functions;

use App\Entity\DesignGoal;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class DesignGoalRepository
{
    /**
     * @return DesignGoal[]
     */
    public static function findByIdsFromParameters(EntityManagerInterface $em, ParameterBag $params, string $fieldName): array
    {
        // *sigh* PHP...
        $designGoalIds =
            array_unique(       // unique values
                array_filter(   //   among values >= 0
                    array_map(  //     among the string values cast to integers
                        fn($id) => (int)$id,
                        $params->all($fieldName)
                    ),
                    fn(int $id) => $id > 0
                )
            )
        ;

        return $em->getRepository(DesignGoal::class)->findBy([ 'id' => $designGoalIds ]);
    }
}
