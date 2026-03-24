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

final class Version20231230204500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'magic crystal ball';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
        -- treasure
        INSERT IGNORE INTO item_treasure (`id`, `silver`, `gold`, `gems`) VALUES (94,0,1,1);
        
        -- hat
        INSERT IGNORE INTO item_hat (`id`, `head_x`, `head_y`, `head_angle`, `head_scale`, `head_angle_fixed`) VALUES (254,0.51,0.865,0,0.56,0);
        
        -- the item itself!
        INSERT IGNORE INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1357,"Magic Crystal Ball","Charged by the elusive tachyons from the heart of a neutron star, this crystal ball holds the power to navigate through the vast tapestry of the cosmos, revealing secrets that span galaxies and time itself.","treasure/magic-crystal-ball","[[\"Gaze into...\",\"magicCrystalBall\",\"page\"]]",NULL,NULL,0,NULL,254,0,7,NULL,NULL,94,0,NULL,0,10);
        
        -- grammar
        INSERT IGNORE INTO item_grammar (`id`, `item_id`, `article`) VALUES (1467,1357,"a");
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
