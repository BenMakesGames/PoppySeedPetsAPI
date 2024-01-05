<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240104210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'fairy floss!';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `use_actions` = '[[\"Say Hello\",\"fairy/#/hello\"],[\"Build a Fireplace\",\"fairy/#/buildFireplace\"],[\"Ask about Quintessence\",\"fairy/#/quintessence\"],[\"Make Fairy Floss\",\"fairy/#/makeFairyFloss\"]]' WHERE `item`.`id` = 333; 
        EOSQL);

        $this->addSql(<<<EOSQL
        -- tool effect
        INSERT IGNORE INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`) VALUES (455,0,0,0,0,0,0.29,0.815,-34,0.59,0,0,0,0,0,0,NULL,0,0,0,0,12,1358,0,0,0,0,1,0,0,0,0,0,1,NULL,NULL,NULL);
        
        -- food effect
        INSERT IGNORE INTO item_food (`id`, `food`, `love`, `junk`, `alcohol`, `earthy`, `fruity`, `tannic`, `spicy`, `creamy`, `meaty`, `planty`, `fishy`, `floral`, `fatty`, `oniony`, `chemically`, `caffeine`, `psychedelic`, `granted_skill`, `chance_for_bonus_item`, `random_flavor`, `contains_tentacles`, `granted_status_effect`, `granted_status_effect_duration`, `is_candy`, `leftovers_id`, `bonus_item_group_id`) VALUES (501,2,2,2,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,NULL,NULL,0,0,NULL,NULL,1,NULL,NULL);
        
        -- the item itself!
        INSERT IGNORE INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1358,"Pink Fairy Floss","And the handle is even made of Paper, so the _whole thing_ is edible!","candy/fairy-floss-pink",NULL,455,501,4,NULL,NULL,30,0,NULL,NULL,NULL,0,NULL,0,1);
        
        -- grammar
        INSERT IGNORE INTO item_grammar (`id`, `item_id`, `article`) VALUES (1468,1358,"some");


        -- tool effect
        INSERT IGNORE INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`) VALUES (456,0,0,0,0,0,0.29,0.815,-34,0.59,0,0,0,0,0,0,NULL,0,0,0,0,12,1359,0,0,0,0,1,0,0,0,0,0,1,NULL,NULL,NULL);
        
        -- food effect
        INSERT IGNORE INTO item_food (`id`, `food`, `love`, `junk`, `alcohol`, `earthy`, `fruity`, `tannic`, `spicy`, `creamy`, `meaty`, `planty`, `fishy`, `floral`, `fatty`, `oniony`, `chemically`, `caffeine`, `psychedelic`, `granted_skill`, `chance_for_bonus_item`, `random_flavor`, `contains_tentacles`, `granted_status_effect`, `granted_status_effect_duration`, `is_candy`, `leftovers_id`, `bonus_item_group_id`) VALUES (502,2,2,2,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,NULL,NULL,0,0,NULL,NULL,1,NULL,NULL);
        
        -- the item itself!
        INSERT IGNORE INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1359,"Blue Fairy Floss","A classic candy in a classic Desikh blue!
        
        Classic!","candy/fairy-floss-blue",NULL,456,502,4,NULL,NULL,30,0,NULL,NULL,NULL,0,NULL,0,1);
        
        -- grammar
        INSERT IGNORE INTO item_grammar (`id`, `item_id`, `article`) VALUES (1469,1359,"some");
        EOSQL);

        $this->addSql('UPDATE `pet_species` SET `sheds_id` = 1358 WHERE `pet_species`.`id` = 3;');
    }

    public function down(Schema $schema): void
    {
    }
}
