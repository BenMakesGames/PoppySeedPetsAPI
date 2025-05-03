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

final class Version20250503203356 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Jelly-filled Donut';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
        -- food effect
        INSERT INTO item_food (`id`, `food`, `love`, `junk`, `alcohol`, `earthy`, `fruity`, `tannic`, `spicy`, `creamy`, `meaty`, `planty`, `fishy`, `floral`, `fatty`, `oniony`, `chemically`, `caffeine`, `psychedelic`, `granted_skill`, `chance_for_bonus_item`, `random_flavor`, `contains_tentacles`, `granted_status_effect`, `granted_status_effect_duration`, `is_candy`, `leftovers_id`, `bonus_item_group_id`) VALUES (522,10,7,4,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,NULL,15,2,0,NULL,NULL,0,NULL,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1461,"Jelly-filled Donut",NULL,"bread/donut-jelly-filled","[[\"Scoop Out Jelly\",\"jellyDonut\\/#\\/empty\"]]",NULL,522,25,NULL,NULL,0,0,NULL,NULL,NULL,0,NULL,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1546,1461,"a") ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- item groups
        INSERT IGNORE INTO item_group_item (item_group_id, item_id) VALUES (6, 1461), (28, 1461), (46, 1461);
        EOSQL);

    }

    public function down(Schema $schema): void
    {
    }
}
