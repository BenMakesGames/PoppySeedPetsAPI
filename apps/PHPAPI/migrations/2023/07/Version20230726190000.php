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

final class Version20230726190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE `hollow_earth_tile_card` SET `image` = 'hidden-alcove' WHERE `hollow_earth_tile_card`.`id` = 40;");
        $this->addSql("UPDATE `item` SET `image` = 'tile/hidden-alcove' WHERE `item`.`id` = 1047;");

        $this->addSql("UPDATE `hollow_earth_tile_card` SET `image` = 'sand-worm' WHERE `hollow_earth_tile_card`.`id` = 69;");
        $this->addSql("UPDATE `item` SET `image` = 'tile/sand-worm' WHERE `item`.`id` = 1196;");

        $this->addSql("UPDATE `hollow_earth_tile_card` SET `image` = 'shimmering-waterfall' WHERE `hollow_earth_tile_card`.`id` = 45;");
        $this->addSql("UPDATE `item` SET `image` = 'tile/shimmering-waterfall' WHERE `item`.`id` = 1034;");

        $this->addSql("UPDATE `hollow_earth_tile_card` SET `image` = 'orchard' WHERE `hollow_earth_tile_card`.`id` = 19;");
        $this->addSql("UPDATE `item` SET `image` = 'tile/orchard' WHERE `item`.`id` = 1031;");

        $this->addSql("UPDATE `hollow_earth_tile_card` SET `image` = 'sandstorm' WHERE `hollow_earth_tile_card`.`id` = 48;");
    }

    public function down(Schema $schema): void
    {
    }
}
