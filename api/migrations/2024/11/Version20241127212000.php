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

final class Version20241127212000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Horns of Plenty';
    }

    public function up(Schema $schema): void
    {
        // horn of plenty
        $this->addSql(<<<EOSQL
            -- hat
            INSERT INTO item_hat (`id`, `head_x`, `head_y`, `head_angle`, `head_scale`, `head_angle_fixed`) VALUES (269,0.75,0.685,41,0.55,0) ON DUPLICATE KEY UPDATE `id` = `id`;
            
            -- the item itself!
            INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1413,"Horn of Plenty",NULL,"box/horn-of-plenty","[[\"Raid\",\"hornOfPlenty\\/#\\/use\"]]",NULL,NULL,0,NULL,269,0,0,NULL,NULL,NULL,0,NULL,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
            
            -- grammar
            INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1504,1413,"a") ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // empty horn of plenty
        $this->addSql(<<<EOSQL
            -- hat
            INSERT INTO item_hat (`id`, `head_x`, `head_y`, `head_angle`, `head_scale`, `head_angle_fixed`) VALUES (270,0.75,0.685,41,0.55,0) ON DUPLICATE KEY UPDATE `id` = `id`;
            
            -- the item itself!
            INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1414,"Empty Horn of Plenty",NULL,"hat/empty-horn-of-plenty","[]",NULL,NULL,0,NULL,270,60,1,NULL,NULL,NULL,0,NULL,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
            
            -- grammar
            INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1505,1414,"an") ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
