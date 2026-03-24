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

final class Version20250918161328 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'EOSQL'
        -- tool effect
        INSERT INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`, `physics`, `electronics`, `hacking`, `umbra`, `magic_binding`, `mining`) VALUES (518,0,0,1,0,0,0.365,0.4,-99,0.35,0,0,0,0,0,0,NULL,0,0,0,0,NULL,NULL,0,0,0,0,0,0,0,0,0,0,0,NULL,NULL,NULL,0,0,0,0,0,0) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- hat
        INSERT INTO item_hat (`id`, `head_x`, `head_y`, `head_angle`, `head_scale`, `head_angle_fixed`) VALUES (299,0.43,0.645,-12,0.43,0) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1484,"Clay Brick","A brick is a versatile thing! Why, you can even wear it as a hat! How many things can you say _that_ about in Poppy Seed Pets?
        
        Huh? What? You can wear Gummy Worms and bottles of Ponzu as hats?!?!
        
        Well... ... ... fine. I guess bricks aren\'t so special after all! I HOPE YOU'RE HAPPY! \\*flees, crying\\*","mineral/brick","[[\"Add to Fireplace\",\"brick\\/#\\/addToFireplace\"]]",518,NULL,0,NULL,299,0,0,NULL,NULL,NULL,0,NULL,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
