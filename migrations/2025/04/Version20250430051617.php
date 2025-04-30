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

final class Version20250430051617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // hat data for Tea Leaves:
        $this->addSql(<<<EOSQL
        INSERT INTO `item_hat` (`id`, `head_x`, `head_y`, `head_angle`, `head_angle_fixed`, `head_scale`) VALUES (289, '0.57', '0.28', '72', '0', '0.25')
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // add hat id for Tea Leaves:
        $this->addSql('UPDATE `item` SET `hat_id` = 289 WHERE `item`.`id` = 21;');
    }

    public function down(Schema $schema): void
    {
    }
}
