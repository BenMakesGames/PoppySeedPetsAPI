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

final class Version20250424000625 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
        INSERT INTO `item_group` (`id`, `name`, `is_craving`, `is_gift_shop`) VALUES ('48', 'Robot', '0', '0')
        ON DUPLICATE KEY UPDATE `id` = `id`
        EOSQL);

        $this->addSql(<<<EOSQL
        INSERT INTO item_group_item (item_group_id, item_id)
        SELECT 48, id
        FROM item WHERE
            image LIKE 'robot/%'
            OR name = 'Beta Bug'
        EOSQL);

        $this->addSql(<<<EOSQL
        -- hat
        INSERT INTO item_hat (`id`, `head_x`, `head_y`, `head_angle`, `head_scale`, `head_angle_fixed`) VALUES (283,0.49,0.765,0,0.56,0) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1445,"Bag of Feathers",NULL,"bag/feather","[]",NULL,NULL,0,NULL,283,0,0,NULL,NULL,NULL,0,NULL,0,5) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1530,1445,"a") ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- item groups
        INSERT IGNORE INTO item_group_item (item_group_id, item_id) VALUES (45, 1445);
        EOSQL);

        $this->addSql(<<<EOSQL
        UPDATE monster_of_the_week
        SET medium_prize_id=1445
        WHERE id=(SELECT MAX(monster_of_the_week_id) FROM monster_of_the_week_contribution) AND monster='Cardea'
        LIMIT 1
        EOSQL);

        $this->addSql(<<<EOSQL
        -- hat
        INSERT INTO item_hat (`id`, `head_x`, `head_y`, `head_angle`, `head_scale`, `head_angle_fixed`) VALUES (284,0.335,0.99,-18,0.84,0) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1446,"Eiri Persona Persona",NULL,"hat/eiri-persona-persona","[]",NULL,NULL,0,NULL,284,0,0,NULL,NULL,NULL,0,NULL,0,5) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1531,1446,"an") ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
