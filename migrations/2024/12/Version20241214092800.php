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

final class Version20241214092800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // add a "Bucket" item group
        $this->addSql(<<<EOSQL
        INSERT INTO `item_group` (`id`, `name`, `is_craving`, `is_gift_shop`)
        VALUES (47, 'Bucket', '0', '0')
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // add all buckets to the "Bucket" item group
        $this->addSql(<<<EOSQL
        INSERT INTO item_group_item (item_group_id, item_id)
        SELECT 47 AS item_group_id, item.id AS item_id
        FROM item WHERE item.name LIKE '%Bucket%' OR item.name IN (
            'Fabric Mâché Basket',
            'Saucepan',
            'Upside-down Saucepan'
        )
        ON DUPLICATE KEY UPDATE `item_group_id` = `item_group_id`;
        EOSQL);

        // updated Meat-seeking Claymore
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `use_actions` = '[[\"Go! Seek meats!\", \"seekingClaymore/#/seekMeat\"], [\"Retune\", \"seekingClaymore/#/tune\"]]' WHERE `item`.`id` = 1183; 
        EOSQL);

        // adding Sweet-seeking Claymore
        $this->addSql(<<<EOSQL
        -- enchantment effect
        INSERT INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`) VALUES (477,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,NULL,0,0,0,0,33,12,0,0,0,0,0,0,0,0,0,0,0,NULL,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- enchantment
        INSERT INTO enchantment (`id`, `effects_id`, `name`, `is_suffix`, `aura_id`) VALUES (140,477,"Sweet-seeking",0,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- tool effect
        INSERT INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`) VALUES (478,0,0,4,0,0,-0.01,0.815,-49,0.7,0,1,0,0,2,0,"brawl",0,0,0,0,33,12,0,0,0,0,0,0,0,0,0,0,0,NULL,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1418,"Sweet-seeking Claymore",NULL,"tool/sword/laser-guided-and-winged-sweet","[[\"Go! Seek sweets!\",\"seekingClaymore\\/#\\/seekSweet\"],[\"Retune\",\"seekingClaymore\\/#\\/tune\"]]",478,NULL,0,NULL,NULL,0,22,140,NULL,NULL,0,NULL,0,30) ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // adding Wheat-seeking Claymore
        $this->addSql(<<<EOSQL
        -- enchantment effect
        INSERT INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`) VALUES (480,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,NULL,0,0,0,0,33,15,0,0,0,0,0,0,0,0,0,0,0,NULL,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- enchantment
        INSERT INTO enchantment (`id`, `effects_id`, `name`, `is_suffix`, `aura_id`) VALUES (141,480,"Wheat-seeking",0,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- tool effect
        INSERT INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`) VALUES (479,0,0,4,0,0,-0.01,0.815,-49,0.7,0,1,0,0,2,0,"brawl",0,0,0,0,33,15,0,0,0,0,0,0,0,0,0,0,0,NULL,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1419,"Wheat-seeking Claymore",NULL,"tool/sword/laser-guided-and-winged-wheat","[[\"Go! Seek wheats!\",\"seekingClaymore\\/#\\/seekWheat\"],[\"Retune\",\"seekingClaymore\\/#\\/tune\"]]",479,NULL,0,NULL,NULL,0,22,141,NULL,NULL,0,NULL,0,30) ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // adding Beat-seeking Claymore
        $this->addSql(<<<EOSQL
        -- enchantment effect
        INSERT INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`) VALUES (482,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,NULL,0,0,0,0,33,36,0,0,0,0,0,0,0,0,0,0,0,NULL,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- enchantment
        INSERT INTO enchantment (`id`, `effects_id`, `name`, `is_suffix`, `aura_id`) VALUES (142,482,"Beat-seeking",0,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- tool effect
        INSERT INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`) VALUES (481,0,0,4,0,0,-0.01,0.815,-49,0.7,0,0,1,0,2,0,"brawl",0,0,0,0,33,36,0,0,0,0,0,0,0,0,0,0,0,NULL,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1420,"Beat-seeking Claymore",NULL,"tool/sword/laser-guided-and-winged-beat","[[\"Go! Seek beats!\",\"seekingClaymore\\/#\\/seekBeat\"],[\"Retune\",\"seekingClaymore\\/#\\/tune\"]]",481,NULL,0,NULL,NULL,0,22,142,NULL,NULL,0,NULL,0,30) ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // adding Sheet-seeking Claymore
        $this->addSql(<<<EOSQL
        -- enchantment effect
        INSERT INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`) VALUES (484,0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,NULL,0,0,0,0,33,67,0,0,0,0,0,0,0,0,0,0,0,NULL,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- enchantment
        INSERT INTO enchantment (`id`, `effects_id`, `name`, `is_suffix`, `aura_id`) VALUES (143,484,"Sheet-seeking",0,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- tool effect
        INSERT INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`) VALUES (483,0,0,4,0,1,-0.01,0.815,-49,0.7,0,0,0,0,2,0,"brawl",0,0,0,0,33,67,0,0,0,0,0,0,0,0,0,0,0,NULL,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1421,"Sheet-seeking Claymore",NULL,"tool/sword/laser-guided-and-winged-sheet","[[\"Go! Seek sheets!\",\"seekingClaymore\\/#\\/seekSheet\"],[\"Retune\",\"seekingClaymore\\/#\\/tune\"]]",483,NULL,0,NULL,NULL,0,22,143,NULL,NULL,0,NULL,0,30) ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
