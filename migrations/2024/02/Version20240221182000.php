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

final class Version20240221182000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'leap day';
    }

    public function up(Schema $schema): void
    {
        // wearable mermaid eggs
        $this->addSql(<<<EOSQL
            INSERT INTO `item_hat` (`id`, `head_x`, `head_y`, `head_angle`, `head_scale`, `head_angle_fixed`) VALUES (255, '0.495', '0.88', '0', '0.1', '0')
            ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        $this->addSql('UPDATE `item` SET `hat_id` = 255 WHERE `item`.`id` = 41;');

        // effect
        $this->addSql(<<<EOSQL
            INSERT INTO `item_tool` (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`) VALUES
            (460, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL)
            ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // aura
        $this->addSql(<<<EOSQL
            INSERT INTO `aura` (`id`, `name`, `image`, `size`, `center_x`, `center_y`) VALUES
            (38, 'Spirit of Leap Day', 'leap-day', 0.7, 0.4, 0.25)
            ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // aura
        $this->addSql(<<<EOSQL
            INSERT INTO `enchantment` (`id`, `effects_id`, `name`, `is_suffix`, `aura_id`) VALUES (137, '460', 'Leap Day\'s', '0', '38')
            ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
