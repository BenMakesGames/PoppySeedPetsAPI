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

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250704220047 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'EOSQL'
        INSERT INTO `monthly_story_adventure` (`id`, `title`, `summary`, `release_number`, `release_year`, `release_month`, `is_dark`) VALUES
        (5, 'REMIX', 'You\'ve run enough prefab scenarios for your pets... time to mix it up a bit!', 0, 0, 0, 0)
        ON DUPLICATE KEY UPDATE id = id;

        INSERT INTO `monthly_story_adventure_step` (`id`, `adventure_id`, `aura_id`, `title`, `type`, `step`, `previous_step`, `x`, `y`, `min_pets`, `max_pets`, `narrative`, `treasure`, `pin_override`) VALUES
        (101, 5, NULL, 'Shipwreck', 'RemixShipwreck', 1, NULL, 28, 30.75, 3, 3, NULL, NULL, NULL),
        (102, 5, NULL, 'Beach', 'RemixBeach', 2, 1, 15.5, 41.8, 3, 3, NULL, NULL, NULL),
        (103, 5, NULL, 'A Small Cave', 'RemixTreasureRoom', 3, 2, 20.05, 49.25, 3, 3, NULL, NULL, NULL),
        (104, 5, NULL, 'Beach', 'RemixBeach', 4, 1, 45.5, 17.4, 3, 3, NULL, NULL, NULL),
        (105, 5, NULL, 'Island Forest', 'RemixForest', 5, 4, 29.05, 10.55, 3, 3, NULL, NULL, NULL),
        (106, 5, NULL, 'Cave Entrance', 'RemixCave', 6, 4, 53, 19.5, 3, 3, NULL, NULL, 'Left'),
        (107, 5, NULL, 'Fork in the Cave', 'RemixCave', 7, 6, 60.5, 37.75, 3, 3, NULL, NULL, NULL),
        (108, 5, NULL, 'Underground Lake', 'RemixUndergroundLake', 8, 7, 56.84, 43.95, 3, 3, NULL, NULL, 'Right'),
        (109, 5, NULL, 'An Opening', 'RemixCave', 9, 7, 67.39, 60.89, 3, 3, NULL, NULL, NULL),
        (110, 5, NULL, 'Endless Stone', 'RemixCave', 10, 9, 53.34, 72.26, 3, 3, NULL, NULL, NULL),
        (111, 5, NULL, 'A Clearly-magic Tower', 'RemixMagicTower', 11, 10, 33.17, 84.85, 3, 3, NULL, NULL, 'Right'),
        (112, 5, NULL, 'Strange Plants', 'RemixUmbralPlants', 12, 11, 43.36, 95.24, 3, 3, NULL, NULL, NULL),
        (113, 5, NULL, 'Underground Village', 'RemixDarkVillage', 13, 10, 66.46, 81.4, 3, 3, NULL, NULL, NULL),
        (114, 5, NULL, 'Underground Foothills', 'RemixCave', 14, 13, 53.26, 85.24, 3, 3, NULL, NULL, NULL),
        (115, 5, NULL, 'Graveyard', 'RemixGraveyard', 15, 13, 67.37, 96.1, 3, 3, NULL, NULL, NULL),
        (116, 5, NULL, 'Precipice', 'RemixCave', 16, 13, 82.57, 77.71, 3, 3, NULL, NULL, NULL),
        (117, 5, NULL, 'The Deep', 'RemixTheDeep', 17, 16, 85.67, 88.1, 3, 3, NULL, NULL, 'Bottom')
        ON DUPLICATE KEY UPDATE id = id;
        EOSQL);

    }

    public function down(Schema $schema): void
    {
    }
}
