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

final class Version20250823163310 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Sandwill Scroch';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'EOSQL'
        -- food effect
        INSERT INTO item_food (`id`, `food`, `love`, `junk`, `alcohol`, `earthy`, `fruity`, `tannic`, `spicy`, `creamy`, `meaty`, `planty`, `fishy`, `floral`, `fatty`, `oniony`, `chemically`, `caffeine`, `psychedelic`, `granted_skill`, `chance_for_bonus_item`, `random_flavor`, `contains_tentacles`, `granted_status_effect`, `granted_status_effect_duration`, `is_candy`, `leftovers_id`, `bonus_item_group_id`) VALUES (534,30,7,0,0,2,0,0,0,0,1,0,2,0,1,0,1,0,8,NULL,NULL,0,0,NULL,NULL,0,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1482,"Sandwill Scroch","Making your own Sandwich Scr-- er, I mean, Sandwill Scroch is easy!
        
        1. Get a nice piece of paper, and cut it two
        2. Butter each half on one side
        3. Nut-butter each half on the side you didn\'t regular-type butter
        4. Get a fish (dead is easier, but you do you)
        5. Put the nut-buttered side of each paper half against the fish
        6. Fry with a bit of Quintessence, flipping occasionally, until golden-brown and delicious
        
        Your Sandwill Scroch is now reedy to red-- er, I mean, reddy to reed!","scroll/sandwich","[[\"Read\",\"scroll\\/sandwich\\/#\\/read\"]]",NULL,534,37,NULL,NULL,420,0,NULL,NULL,NULL,0,NULL,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1566,1482,"a") ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- item groups
        INSERT IGNORE INTO item_group_item (item_group_id, item_id) VALUES (14, 1482), (44, 1482), (46, 1482);
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
