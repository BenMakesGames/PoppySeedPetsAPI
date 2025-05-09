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

final class Version20231222161000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'takoyaki + two item tweaks';
    }

    public function up(Schema $schema): void
    {
        // compass (the math kind): description tweak
        $this->addSql("UPDATE `item` SET `description` = 'Who needs the North Star when you\\'ve got geometry? Sure, it won\\'t help you navigate the wild unknown, or point you home when you\\'re lost at sea, but when you find yourself marooned on a desert island with nothing but that urgent, _primal_ need to draw a perfect circle - ooh: or _bisect an angle_ - well! There\\'s only one compass you\\'d be happy to have: the _math kind!_' WHERE `item`.`id` = 365;");

        // cheesy flakes: become a fertilizer
        $this->addSql("UPDATE `item` SET `fertilizer` = '1' WHERE `item`.`id` = 851;");

        // takoyaki
        $this->addSql(<<<EOSQL
        -- food effect
        INSERT IGNORE INTO item_food (`id`, `food`, `love`, `junk`, `alcohol`, `earthy`, `fruity`, `tannic`, `spicy`, `creamy`, `meaty`, `planty`, `fishy`, `floral`, `fatty`, `oniony`, `chemically`, `caffeine`, `psychedelic`, `granted_skill`, `chance_for_bonus_item`, `random_flavor`, `contains_tentacles`, `granted_status_effect`, `granted_status_effect_duration`, `is_candy`, `leftovers_id`, `bonus_item_group_id`) VALUES (500,10,0,0,0,1,1,0,0,0,2,1,3,1,1,1,0,0,0,NULL,100,0,1,NULL,NULL,0,42,1);
        
        -- the item itself!
        INSERT IGNORE INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1356,"Takoyaki","Does this mean the Tentacles in PSP are canonically octopus?
        
        I\'m afraid not.
        
        If anything, it means this item is curiously misnamed...","meal/takoyaki",NULL,NULL,500,14,NULL,NULL,0,0,NULL,NULL,NULL,0,NULL,0,1);
        
        -- grammar
        INSERT IGNORE INTO item_grammar (`id`, `item_id`, `article`) VALUES (1466,1356,NULL);
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
