<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240705020421 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Monday Coin
        $this->addSql(<<<EOSQL
        -- tool effect
        INSERT INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`) VALUES (462,1,0,0,0,0,0.515,0.525,-20,0.25,0,0,0,0,0,0,"stealth",0,0,0,0,NULL,NULL,0,0,0,0,0,0,0,0,0,0,0,NULL,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1385,"Monday Coin","\"Well this one is pretty: Monday... Moon... you get it.\" ~Kat","coin/moon",NULL,462,NULL,0,NULL,NULL,0,1,NULL,NULL,NULL,0,NULL,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1476,1385,"a") ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // Tuesday Coin
        $this->addSql(<<<EOSQL
        -- tool effect
        INSERT INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`) VALUES (463,0,0,1,0,0,0.515,0.525,-20,0.25,0,0,0,0,0,0,"brawl",0,0,0,0,NULL,NULL,0,0,0,0,0,0,0,0,0,0,0,NULL,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1386,"Tuesday Coin","\"Tuesday is named for an ancient god of the sky. I can never remember how to spell his name >_> Anyway: so it\'s got a cloud, and a little wind... thing... I guess? 🤷‍♀️ Why not.\" ~Kat","coin/tiwaz",NULL,463,NULL,0,NULL,NULL,0,1,NULL,NULL,NULL,0,NULL,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1477,1386,"a") ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // Wednesday Coin
        $this->addSql(<<<EOSQL
        -- tool effect
        INSERT INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`) VALUES (464,0,0,0,1,0,0.515,0.525,-20,0.25,0,0,0,0,0,0,"arcana",0,0,0,0,NULL,NULL,0,0,0,0,0,0,0,0,0,0,0,NULL,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1387,"Wednesday Coin","\"Wednesday is named after Woden, who\'s kind of similar to Odin. I don\'t know why there\'s two opposite-pointing triangles on his coin, though. Weird.\" ~Kat","coin/woden",NULL,464,NULL,0,NULL,NULL,0,1,NULL,NULL,NULL,0,NULL,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1478,1387,"a") ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // Thursday Coin
        $this->addSql(<<<EOSQL
        -- tool effect
        INSERT INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`) VALUES (465,0,1,0,0,0,0.515,0.525,-20,0.25,0,0,0,0,0,0,"nature",0,0,0,0,NULL,NULL,0,0,0,0,0,0,0,0,0,0,0,NULL,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1388,"Thursday Coin","\"Thursday is named after Thor, of course - everyone knows _this_ guy! And he\'s the god of thunder, sure-sure, but _also_ the god of storms, sacred groves, and the protection of humans! What a great guy!\" ~Kat","coin/thor",NULL,465,NULL,0,NULL,NULL,0,1,NULL,NULL,NULL,0,NULL,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1479,1388,"a") ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // Friday Coin
        $this->addSql(<<<EOSQL
        -- tool effect
        INSERT INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`) VALUES (466,0,0,0,0,0,0.515,0.525,-20,0.25,0,0,0,0,0,0,NULL,0,0,0,0,NULL,NULL,0,0,0,0,0,0,0,0,0,1,0,NULL,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1389,"Friday Coin","\"I know it\'s a language from however many thousands of years ago, but the idea of naming a god \'Frigga\' is so funny to me. Anyway, she\'s who Friday is named after, and she\'s a goddess of love and beauty and sex. SOMEHOW, it\'s always the women who are symbols of these things; never the dudes! (Gosh, I wonder why!) But it\'s like, gimme a hot sex man god, you know? Oh, uh, maybe leave that part out of the quote, though??\" ~Kat","coin/frigga",NULL,466,NULL,0,NULL,NULL,0,1,NULL,NULL,NULL,0,NULL,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1480,1389,"a") ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // Saturday Coin
        $this->addSql(<<<EOSQL
        -- tool effect
        INSERT INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`) VALUES (467,0,0,0,0,0,0.515,0.525,-20,0.25,0,1,0,0,0,0,"science",0,0,0,0,NULL,NULL,0,0,0,0,0,0,0,0,0,0,0,NULL,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1390,"Saturday Coin","\"Coming from Satyr Dice, you\'d super-think Saturday was named after Satyrs, but no! It\'s Saturn! B-O-R-I-N-G! Well, no, sorry, that\'s not true: Saturn\'s a super-interesting god of, like, liberation, time, wealth, and peace? And then somehow also agriculture?? Mythology! What a thing!\" ~Kat","coin/saturn",NULL,467,NULL,0,NULL,NULL,0,1,NULL,NULL,NULL,0,NULL,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1481,1390,"a") ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // Kat's Gift Package
        $this->addSql(<<<EOSQL
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1391,"Kat\'s Gift Package","\"Custom-made with your preferences in mind! ... ... What is it you like, again?\" ~Kat","box/florist","[[\"Chocolate\",\"katsGift\\/#\\/chocolates\"],[\"Gardening\",\"katsGift\\/#\\/gardeningSupplies\"],[\"Fishing\",\"katsGift\\/#\\/fishingGear\"],[\"Lava. I like lava.\",\"katsGift\\/#\\/lava\"]]",NULL,NULL,0,NULL,NULL,0,0,NULL,NULL,NULL,0,NULL,0,7) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1482,1391,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // Sunday Coin
        $this->addSql(<<<EOSQL
        -- tool effect
        INSERT INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`) VALUES (468,0,0,0,0,0,0.515,0.525,-20,0.25,0,0,0,0,0,0,NULL,0,0,0,0,957,957,0,0,0,0,0,0,0,0,0,0,0,NULL,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1392,"Sunday Coin","\"When you think about it, isn\'t \'Sunday\', like, the LEAST creative name possible for a day? \'Oh, what should we call this one?\' Looks up. \'Sun... day?\' \'Sounds good to me!\'\" ~Kat","coin/sun",NULL,468,NULL,0,NULL,NULL,0,1,NULL,NULL,NULL,0,NULL,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1483,1392,"a") ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
