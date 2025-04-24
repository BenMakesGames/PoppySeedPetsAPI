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

final class Version20250424043857 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates a new item_hat entry and links it to an item.';
    }

    public function up(Schema $schema): void
    {
        // Create a new hat and set values from the Make a Hat tool
        $this->addSql(<<<'EOSQL'
INSERT INTO `item_hat` (`head_x`, `head_y`, `head_angle`, `head_angle_fixed`, `head_scale`) VALUES ('0.525', '0.93', '-14', '0', '0.49');
EOSQL);

        // Link the new hat to the desired item (update the item ID as needed)
        $this->addSql(<<<'EOSQL'
UPDATE `item`
SET `hat_id` = (SELECT MAX(`id`) FROM `item_hat`)
WHERE `item`.`id` = 1173;
EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
