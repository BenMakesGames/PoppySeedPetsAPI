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

final class Version20260103040021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add two new auras';
    }

    public function up(Schema $schema): void
    {
        // Fruity Rings aura:

        // aura
        $this->addSql(<<<EOSQL
            INSERT INTO `aura` (`id`, `name`, `image`, `size`, `center_x`, `center_y`) VALUES
            (39, 'Fruity Rings', 'fruity-rings', 1.2, 0.5, 0.3333)
            ON DUPLICATE KEY UPDATE id=id;
        EOSQL);

        // tool effect
        $this->addSql(<<<EOSQL
            INSERT INTO `item_tool` (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`, `physics`, `electronics`, `hacking`, `umbra`, `magic_binding`, `mining`) VALUES
            (529, '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', NULL, '0', '0', '0', '0', NULL, NULL, '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', NULL, NULL, NULL, '0', '0', '0', '0', '0', '0')
            ON DUPLICATE KEY UPDATE id=id;
        EOSQL);

        // bonus for applying the aura
        $this->addSql(<<<EOSQL
            INSERT INTO `enchantment` (`id`, `effects_id`, `name`, `is_suffix`, `aura_id`) VALUES
            (157, '529', 'Breakfast', '0', '39')
            ON DUPLICATE KEY UPDATE id=id;
        EOSQL);

        // Treble Scale aura:

        // aura
        $this->addSql(<<<EOSQL
            INSERT INTO `aura` (`id`, `name`, `image`, `size`, `center_x`, `center_y`) VALUES
            (40, 'Treble Scale', 'musical-scale', '1.0', '0.1', '0.6')
            ON DUPLICATE KEY UPDATE id=id;
        EOSQL);

        // tool effect
        $this->addSql(<<<EOSQL
            INSERT INTO `item_tool` (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`, `physics`, `electronics`, `hacking`, `umbra`, `magic_binding`, `mining`) VALUES
            (530, '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', NULL, '0', '0', '0', '0', NULL, NULL, '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', NULL, NULL, NULL, '0', '0', '0', '0', '0', '0')
            ON DUPLICATE KEY UPDATE id=id;
        EOSQL);

        $this->addSql(<<<EOSQL
            INSERT INTO `enchantment` (`id`, `effects_id`, `name`, `is_suffix`, `aura_id`) VALUES
            (158, '530', 'Treble', '0', '40')
            ON DUPLICATE KEY UPDATE id=id;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
