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

final class Version20230726140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE `hollow_earth_tile_card` SET `image` = 'box-of-ores' WHERE `hollow_earth_tile_card`.`id` = 29;");
        $this->addSql("UPDATE `item` SET `image` = 'tile/box-of-ores' WHERE `item`.`id` = 1046;");

        $this->addSql("UPDATE `hollow_earth_tile_card` SET `image` = 'torch' WHERE `hollow_earth_tile_card`.`id` = 30;");
        $this->addSql("UPDATE `item` SET `image` = 'tile/stereotypical-torch' WHERE `item`.`id` = 1035;");

        $this->addSql("UPDATE `hollow_earth_tile_card` SET `image` = 'noisy-goblin' WHERE `hollow_earth_tile_card`.`id` = 14;");
        $this->addSql("UPDATE `item` SET `image` = 'tile/noisy-goblin' WHERE `item`.`id` = 1023;");
    }

    public function down(Schema $schema): void
    {
    }
}
