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

final class Version20250508180543 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Less-heavy Heavy Hammer
        $this->addSql(<<<EOSQL
        -- tool effect
        INSERT INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`, `physics`, `electronics`, `hacking`, `umbra`, `magic_binding`, `mining`) VALUES (511,0,0,3,0,3,0.25,0.745,-49,0.81,0,0,0,4,0,0,"crafts",1,0,0,0,NULL,NULL,0,0,0,0,0,0,0,0,0,0,0,NULL,NULL,NULL,0,0,0,0,0,0) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1465,"Less-heavy Heavy Hammer",NULL,"tool/hammer/heavy-light",NULL,511,NULL,0,NULL,NULL,0,11,NULL,NULL,NULL,0,NULL,0,15) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1550,1465,"a") ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'It flies with the grace of an ill-shapen boulder, but hey: it didn\'t fly _at all_ before!' WHERE `item`.`id` = 1465; 
        EOSQL);

        // Rainbow item group
        $this->addSql(<<<EOSQL
        INSERT INTO `item_group` (`id`, `name`, `is_craving`, `is_gift_shop`) VALUES (49, 'Rainbow', '0', '0')
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        $this->addSql(<<<EOSQL
        INSERT INTO item_group_item (item_id, item_group_id)
        SELECT id AS item_id,49 AS item_group_id FROM item
        WHERE item.name LIKE '%Rainbow%'
        OR item.name IN (
            'Tri-color Scissors',
            'Hyperchromatic Prism',
            'Less-heavy Heavy Hammer',
            'Macintosh',
            'Lunchbox Paint',
            'Propeller Beanie',
            'Stardust',
            'The Unicorn'
        )
        ON DUPLICATE KEY UPDATE `item_group_id` = `item_group_id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
