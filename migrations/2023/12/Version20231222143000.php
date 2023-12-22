<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231222143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'oobleck';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
            -- hat
            INSERT IGNORE INTO item_hat (`id`, `head_x`, `head_y`, `head_angle`, `head_scale`, `head_angle_fixed`) VALUES (251,0.47,0.58,-1,0.59,0);
            
            -- food effect
            INSERT IGNORE INTO item_food (`id`, `food`, `love`, `junk`, `alcohol`, `earthy`, `fruity`, `tannic`, `spicy`, `creamy`, `meaty`, `planty`, `fishy`, `floral`, `fatty`, `oniony`, `chemically`, `caffeine`, `psychedelic`, `granted_skill`, `chance_for_bonus_item`, `random_flavor`, `contains_tentacles`, `granted_status_effect`, `granted_status_effect_duration`, `is_candy`, `leftovers_id`, `bonus_item_group_id`) VALUES (497,6,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,NULL,NULL,0,0,NULL,NULL,0,NULL,NULL);
            
            -- the item itself!
            INSERT IGNORE INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1353,"Green Oobleck","Euuoooogh! Goopy!","ambiguous/oobleck",NULL,NULL,497,6,NULL,251,0,0,NULL,NULL,NULL,0,NULL,0,1);
            
            -- grammar
            INSERT IGNORE INTO item_grammar (`id`, `item_id`, `article`) VALUES (1463,1353,"some");


            -- hat
            INSERT IGNORE INTO item_hat (`id`, `head_x`, `head_y`, `head_angle`, `head_scale`, `head_angle_fixed`) VALUES (252,0.47,0.58,-1,0.59,0);
            
            -- food effect
            INSERT IGNORE INTO item_food (`id`, `food`, `love`, `junk`, `alcohol`, `earthy`, `fruity`, `tannic`, `spicy`, `creamy`, `meaty`, `planty`, `fishy`, `floral`, `fatty`, `oniony`, `chemically`, `caffeine`, `psychedelic`, `granted_skill`, `chance_for_bonus_item`, `random_flavor`, `contains_tentacles`, `granted_status_effect`, `granted_status_effect_duration`, `is_candy`, `leftovers_id`, `bonus_item_group_id`) VALUES (498,6,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,NULL,NULL,0,0,NULL,NULL,0,NULL,NULL);
            
            -- the item itself!
            INSERT IGNORE INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1354,"Yellow Oobleck","*splortch, squalsh, squick*","ambiguous/oobleck-yellow",NULL,NULL,498,6,NULL,252,0,0,NULL,NULL,NULL,0,NULL,0,1);
            
            -- grammar
            INSERT IGNORE INTO item_grammar (`id`, `item_id`, `article`) VALUES (1464,1354,"some");

            
            -- hat
            INSERT IGNORE INTO item_hat (`id`, `head_x`, `head_y`, `head_angle`, `head_scale`, `head_angle_fixed`) VALUES (253,0.47,0.58,-1,0.59,0);
            
            -- food effect
            INSERT IGNORE INTO item_food (`id`, `food`, `love`, `junk`, `alcohol`, `earthy`, `fruity`, `tannic`, `spicy`, `creamy`, `meaty`, `planty`, `fishy`, `floral`, `fatty`, `oniony`, `chemically`, `caffeine`, `psychedelic`, `granted_skill`, `chance_for_bonus_item`, `random_flavor`, `contains_tentacles`, `granted_status_effect`, `granted_status_effect_duration`, `is_candy`, `leftovers_id`, `bonus_item_group_id`) VALUES (499,6,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,2,"science",NULL,0,0,NULL,NULL,0,NULL,NULL);
            
            -- the item itself!
            INSERT IGNORE INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1355,"Heliotropic Oobleck","Scientific!","ambiguous/oobleck-purple",NULL,NULL,499,6,NULL,253,0,0,NULL,NULL,NULL,0,NULL,0,1);
            
            -- grammar
            INSERT IGNORE INTO item_grammar (`id`, `item_id`, `article`) VALUES (1465,1355,"some");
            
            
            -- purple corn & konpeito get psychadelic, due to quinacridone dye
            UPDATE `item_food` SET `psychedelic` = '2' WHERE `item_food`.`id` = 416;
            UPDATE `item_food` SET `psychedelic` = '2' WHERE `item_food`.`id` = 252;
            
            UPDATE `item` SET `description` = 'This uncomfortably-thick dye is found in the Umbra.\n\nNot safe to eat. Keep out of reach of children.' WHERE `item`.`id` = 455;   
        EOSQL);

    }

    public function down(Schema $schema): void
    {
    }
}
