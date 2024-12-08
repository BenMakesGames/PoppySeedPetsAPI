<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241207191600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add new cooking buddies';
    }

    public function up(Schema $schema): void
    {
        // cooking... with fire
        $this->addSql(<<<EOSQL
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1415,"Cooking... with Fire",NULL,"robot/cooking-with-fire","[[\"Install\",\"cookingBuddy\\/#\\/addOrReplace\"]]",NULL,NULL,0,NULL,NULL,0,8,NULL,NULL,NULL,0,NULL,0,15) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1506,1415,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // mini cooking buddy
        $this->addSql(<<<EOSQL
        -- hat
        INSERT INTO item_hat (`id`, `head_x`, `head_y`, `head_angle`, `head_scale`, `head_angle_fixed`) VALUES (271,0.485,0.945,0,0.65,0) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1416,"Mini Cooking Buddy","Despite its reduced size, its capacity for cooking is the same as its larger brethren.","robot/micro-cooking","[[\"Install\",\"cookingBuddy\\/#\\/addOrReplace\"]]",NULL,NULL,0,NULL,271,0,0,NULL,NULL,NULL,0,NULL,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1507,1416,"a") ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // mega cooking buddy
        $this->addSql(<<<EOSQL
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1417,"Mega Cooking Buddy","Despite its increased size, its capacity for cooking is the same as its smaller brethren.","robot/mega-cooking","[[\"Install\",\"cookingBuddy\\/#\\/addOrReplace\"]]",NULL,NULL,0,NULL,NULL,0,0,NULL,NULL,NULL,0,NULL,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1508,1417,"a") ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
