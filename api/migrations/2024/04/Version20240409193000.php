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

final class Version20240409193000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'S. Epée';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
        -- tool effect
        INSERT INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`) VALUES (461,0,0,4,4,0,0.195,0.835,-38,0.56,0,0,0,0,0,0,"brawl",0,0,0,0,482,253,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1382,"S. Epée","\"Preliminary testing has indicated that S. Epée possesses a rudimentary form of telepathy, capable of projecting emotions and simple urges into the minds of those nearby. Subjects report feeling sensations of anger, hunger, and an overwhelming desire to use S. Epée in acts of violence. These effects are significantly diminished in the presence of lead, suggesting that S. Epée\'s psychic abilities are susceptible to standard psychic dampening measures.\"","tool/sword/epee-ess",NULL,461,NULL,0,NULL,NULL,0,8,NULL,NULL,NULL,0,NULL,0,10) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1473,1382,"an") ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
