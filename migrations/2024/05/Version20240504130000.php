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

final class Version20240504130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'lox';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
        -- hat
        INSERT INTO item_hat (`id`, `head_x`, `head_y`, `head_angle`, `head_scale`, `head_angle_fixed`) VALUES (256,0.49,0.57,0,0.47,0) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- food effect
        INSERT INTO item_food (`id`, `food`, `love`, `junk`, `alcohol`, `earthy`, `fruity`, `tannic`, `spicy`, `creamy`, `meaty`, `planty`, `fishy`, `floral`, `fatty`, `oniony`, `chemically`, `caffeine`, `psychedelic`, `granted_skill`, `chance_for_bonus_item`, `random_flavor`, `contains_tentacles`, `granted_status_effect`, `granted_status_effect_duration`, `is_candy`, `leftovers_id`, `bonus_item_group_id`) VALUES (507,8,0,0,0,0,0,0,0,0,2,0,4,0,0,0,0,0,0,NULL,NULL,0,0,NULL,NULL,0,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1383,"Lox","Gravlax is a kind of Lox, not a kind of Pokémon.
        
        Don\'t feel bad: it\'s a common misconception.","animal/meat/lox",NULL,NULL,507,8,NULL,256,0,0,NULL,NULL,NULL,0,NULL,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1474,1383,"some") ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
