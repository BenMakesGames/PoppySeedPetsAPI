<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250209153500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Chang'e's Love
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = '\"from only Whom shall time no refuge keep<br>though all the weird worlds must be opened?\"<br>~ e.e. cummings' WHERE `item`.`id` = 1263; 
        EOSQL);

        // Bird's-foot Trefoil
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'The Bird\'s-foot Trefoil is fine, but - personally - I prefer the Fish\'s-gill Quatrefoil.' WHERE `item`.`id` = 52; 
        EOSQL);

        // Merigold
        $this->addSql(<<<EOSQL
        -- tool effect
        INSERT INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`) VALUES (497,0,0,0,0,0,0.67,0.81,43,0.46,0,0,0,0,0,0,"nature",0,0,0,0,NULL,NULL,0,0,0,1,0,0,0,0,0,0,0,NULL,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- food effect
        INSERT INTO item_food (`id`, `food`, `love`, `junk`, `alcohol`, `earthy`, `fruity`, `tannic`, `spicy`, `creamy`, `meaty`, `planty`, `fishy`, `floral`, `fatty`, `oniony`, `chemically`, `caffeine`, `psychedelic`, `granted_skill`, `chance_for_bonus_item`, `random_flavor`, `contains_tentacles`, `granted_status_effect`, `granted_status_effect_duration`, `is_candy`, `leftovers_id`, `bonus_item_group_id`) VALUES (520,2,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,NULL,NULL,0,0,NULL,NULL,0,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1442,"Merigold","\"there is a lady, whose name is Afterwards<br>
        she is sitting beside young death, is slender;<br>
        likes flowers.\"<br>
        ~ e.e. cummings","flower/merigold","[]",497,520,2,NULL,NULL,0,0,NULL,NULL,NULL,0,NULL,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1527,1442,"a") ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- item groups
        INSERT IGNORE INTO item_group_item (item_group_id, item_id) VALUES (34, 1442), (46, 1442);
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
