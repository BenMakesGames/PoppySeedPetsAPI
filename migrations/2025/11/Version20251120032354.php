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

final class Version20251120032354 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Toadstool Tea
        $this->addSql(<<<EOSQL
        -- hat
        INSERT INTO item_hat (`id`, `head_x`, `head_y`, `head_angle`, `head_scale`, `head_angle_fixed`) VALUES (306,0.49,0.73,0,0.45,0) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- food effect
        INSERT INTO item_food (`id`, `food`, `love`, `junk`, `alcohol`, `earthy`, `fruity`, `tannic`, `spicy`, `creamy`, `meaty`, `planty`, `fishy`, `floral`, `fatty`, `oniony`, `chemically`, `caffeine`, `psychedelic`, `granted_skill`, `chance_for_bonus_item`, `random_flavor`, `contains_tentacles`, `granted_status_effect`, `granted_status_effect_duration`, `is_candy`, `leftovers_id`, `bonus_item_group_id`) VALUES (535,2,0,0,0,1,0,1,0,0,0,1,0,0,0,0,0,4,8,NULL,NULL,0,0,NULL,NULL,0,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1496,"Toadstool Tea",NULL,"tea/toadstool",NULL,NULL,535,4,NULL,306,0,0,NULL,NULL,NULL,0,NULL,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1580,1496,"a") ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- item groups
        INSERT IGNORE INTO item_group_item (item_group_id, item_id) VALUES (46, 1496), (57, 1496);
        EOSQL);

        // Juramaia
        $this->addSql(<<<EOSQL
        INSERT INTO `pet_species` (`id`, `name`, `image`, `description`, `hand_x`, `hand_y`, `hand_angle`, `flip_x`, `hand_behind`, `available_from_pet_shelter`, `pregnancy_style`, `egg_image`, `hat_x`, `hat_y`, `hat_angle`, `available_from_breeding`, `sheds_id`, `family`, `name_sort`, `physical_description`) VALUES
        (113, 'Juramaia', 'mammal/juramaia', 'Cute, clawed, and purportedly extinct.\n\nJust what the heck is going on on this island?', '0.85', '0.88', '41', '0', '0', '1', '1', NULL, '0.82', '0.645', '16', '1', '34', 'mammal', 'Juramaia', 'A small, shrew-like mammal with a pink snout, short ears, and striped fur.')
        ON DUPLICATE KEY UPDATE id = id;
        EOSQL);

        // the very first Vaf & Nir were way too OP
        $this->addSql(<<<EOSQL
        UPDATE monster_of_the_week SET level = level / 6 WHERE id = 72
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
