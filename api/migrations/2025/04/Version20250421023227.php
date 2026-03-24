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

final class Version20250421023227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix typos in Community Hollow Earth tiles.';
    }

    public function up(Schema $schema): void
    {
        // Edit text in Flying Keys tile
        $this->addSql(<<<'SQL'
            UPDATE hollow_earth_tile_card
            SET event = REPLACE(event, 'which it let\'s you keep!', 'which it lets you keep!')
            WHERE id = 77;
        SQL);

        // Edit text in Like Moths tile
        $this->addSql(<<<'SQL'
            UPDATE hollow_earth_tile_card
            SET event = REPLACE(event, 'mistaking it\'s warm glow', 'mistaking its warm glow')
            WHERE id = 83;
        SQL);

        // Edit text in Worm "Merchant" tile
        $this->addSql(<<<'SQL'
            UPDATE hollow_earth_tile_card
            SET event = REPLACE(event, 'with it\'s basket', 'with its basket')
            WHERE id = 84;
        SQL);
    }

    public function down(Schema $schema): void
    {
    }
}
