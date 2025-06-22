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

final class Version20250621223910 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Sportsball update';
    }

    public function up(Schema $schema): void
    {
        // Orange Sportsball Ball
        $this->addSql(<<<'EOSQL'
        UPDATE `item_tool`
        SET
            `adventure_description` = 'do a hoop',
            `leads_to_adventure` = '1'
        WHERE `item_tool`.`id` = 368; 
        EOSQL);

        // Sportsball Pin
        $this->addSql(<<<'EOSQL'
        UPDATE `item_tool`
        SET
            `adventure_description` = 'go skittling',
            `leads_to_adventure` = '1'
        WHERE `item_tool`.`id` = 369; 
        EOSQL);

        // Sportsball Oar
        $this->addSql(<<<'EOSQL'
        UPDATE `item_tool`
        SET
            `adventure_description` = 'engage another sportsballer',
            `leads_to_adventure` = '1'
        WHERE `item_tool`.`id` = 370; 
        EOSQL);

        // Racketing enchantment
        $this->addSql(<<<'EOSQL'
        INSERT INTO `item_tool` (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`, `physics`, `electronics`, `hacking`, `umbra`, `magic_binding`, `mining`) VALUES (517, '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', NULL, '0', '0', '0', '0', '1124', '1124', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', NULL, NULL, NULL, '0', '0', '0', '0', '0', '0')
        ON DUPLICATE KEY UPDATE `id` = `id`;

        INSERT INTO `enchantment` (`id`, `effects_id`, `name`, `is_suffix`, `aura_id`) VALUES (154, '517', 'Racketing', '0', NULL)
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // Sportsball Oar
        $this->addSql(<<<'EOSQL'
        UPDATE `item_tool`
        SET
            `adventure_description` = 'break open',
            `leads_to_adventure` = '1'
        WHERE `item_tool`.`id` = 367;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
