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

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250525163917 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Apricot Festival';
    }

    public function up(Schema $schema): void
    {
        // Apricrate
        $this->addSql(<<<EOSQL
        -- hat
        INSERT INTO item_hat (`id`, `head_x`, `head_y`, `head_angle`, `head_scale`, `head_angle_fixed`) VALUES (294,0.485,0.77,0,0.7,0) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1469,"Apricrate",NULL,"box/apricrate","[[\"Raid\",\"apricrate\\/#\\/open\"]]",NULL,NULL,0,NULL,294,0,0,NULL,NULL,NULL,0,NULL,0,5) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1554,1469,"an") ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // Empty Crate
        $this->addSql(<<<EOSQL
        -- hat
        INSERT INTO item_hat (`id`, `head_x`, `head_y`, `head_angle`, `head_scale`, `head_angle_fixed`) VALUES (295,0.495,0.435,0,1,0) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1470,"Empty Crate",NULL,"box/empty-crate",NULL,NULL,NULL,0,NULL,295,0,5,NULL,NULL,NULL,0,NULL,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1555,1470,"an") ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // Aprihat
        $this->addSql(<<<EOSQL
        -- hat
        INSERT INTO item_hat (`id`, `head_x`, `head_y`, `head_angle`, `head_scale`, `head_angle_fixed`) VALUES (296,0.48,0.605,0,0.9,0) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- food effect
        INSERT INTO item_food (`id`, `food`, `love`, `junk`, `alcohol`, `earthy`, `fruity`, `tannic`, `spicy`, `creamy`, `meaty`, `planty`, `fishy`, `floral`, `fatty`, `oniony`, `chemically`, `caffeine`, `psychedelic`, `granted_skill`, `chance_for_bonus_item`, `random_flavor`, `contains_tentacles`, `granted_status_effect`, `granted_status_effect_duration`, `is_candy`, `leftovers_id`, `bonus_item_group_id`) VALUES (525,4,2,0,0,0,2,0,0,0,0,0,0,1,0,0,0,0,0,NULL,NULL,0,0,NULL,NULL,0,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1471,"Aprihat","All natural! Made from real Apricots!","hat/aprihat",NULL,NULL,525,6,15,296,0,0,NULL,NULL,NULL,0,NULL,0,2) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1556,1471,"an") ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- item groups
        INSERT IGNORE INTO item_group_item (item_group_id, item_id) VALUES (7, 1471), (46, 1471);
        EOSQL);

        // Gold and Apricots
        $this->addSql(<<<EOSQL
        -- enchantment effect
        INSERT INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`, `physics`, `electronics`, `hacking`, `umbra`, `magic_binding`, `mining`) VALUES (516,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,NULL,0,0,0,0,92,130,0,0,0,0,0,0,0,0,0,0,0,NULL,NULL,NULL,0,0,0,0,1,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- enchantment
        INSERT INTO enchantment (`id`, `effects_id`, `name`, `is_suffix`, `aura_id`) VALUES (153,516,"of Gold and Apricots",1,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- tool effect
        INSERT INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`, `physics`, `electronics`, `hacking`, `umbra`, `magic_binding`, `mining`) VALUES (515,0,3,0,0,0,0.2,0.755,-41,0.74,0,0,0,0,0,0,"nature",1,0,0,0,92,130,0,0,0,0,0,0,0,0,0,0,0,NULL,NULL,NULL,0,0,0,0,3,3) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1472,"Gold and Apricots",NULL,"tool/wand/gold-and-apricots",NULL,515,NULL,0,NULL,NULL,0,0,153,NULL,NULL,0,NULL,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1557,1472,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- item groups
        INSERT IGNORE INTO item_group_item (item_group_id, item_id) VALUES (42, 1472);
        EOSQL);

        // Apricot Coffee Bean Tea with Mammal Extract
        $this->addSql(<<<EOSQL
        -- food effect
        INSERT INTO item_food (`id`, `food`, `love`, `junk`, `alcohol`, `earthy`, `fruity`, `tannic`, `spicy`, `creamy`, `meaty`, `planty`, `fishy`, `floral`, `fatty`, `oniony`, `chemically`, `caffeine`, `psychedelic`, `granted_skill`, `chance_for_bonus_item`, `random_flavor`, `contains_tentacles`, `granted_status_effect`, `granted_status_effect_duration`, `is_candy`, `leftovers_id`, `bonus_item_group_id`) VALUES (526,2,-2,1,0,1,1,0,0,0,0,1,0,0,0,0,3,4,0,NULL,NULL,0,0,NULL,NULL,0,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1473,"Apricot Coffee Bean Tea with Mammal Extract","\"I have a great idea,\" said the marketing department, \"let\'s capitalize on a local festival by creating a _festive drink!_\" This, apparently, is what they came up with.","tea/apricot-festival",NULL,NULL,526,5,NULL,NULL,0,0,NULL,NULL,NULL,0,NULL,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1558,1473,"a tall") ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- item groups
        INSERT IGNORE INTO item_group_item (item_group_id, item_id) VALUES (46, 1473);
        EOSQL);

        // Tile: Pluot Parade
        $this->addSql(<<<'EOSQL'
        -- hollow earth tile card
        INSERT INTO hollow_earth_tile_card (`id`, `type_id`, `name`, `event`, `required_action`, `image`, `author`) VALUES (90,5,"Pluot Parade","{\"food\":8,\"love\":4,\"type\":\"onward\",\"buttonText\":\"Onward!\",\"description\":\"You stop in town to watch a parade that appears to be fruit based? Asking some locals, you learn it\'s a \\\"Pluot Parade\\\". You both eat some pluot donuts, have some pluot juice, and grab a couple pluots to take home. Neither of you have the faintest clue what a \\\"pluot\\\" is, but it seems pretty similar to an Apricot, so, \\ud83e\\udd37\\u200d\\u2640\\ufe0f, let\'s just say you brought two Apricots home.\",\"receiveItems\":[\"Apricot\",\"Apricot\"]}",0,"pluot-parade",NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1474,"Tile: Pluot Parade",NULL,"tile/pluot-parade",NULL,NULL,NULL,0,NULL,NULL,30,1,NULL,NULL,NULL,0,90,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
