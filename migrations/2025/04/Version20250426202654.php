<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250426202654 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // pets can now crave Circles - just like irl
        $this->addSql(<<<EOSQL
        UPDATE `item_group` SET `is_craving` = '1' WHERE `item_group`.`id` = 28; 
        EOSQL);

        // new location tags
        $this->addSql(<<<EOSQL
        INSERT INTO `pet_activity_log_tag` (`id`, `title`, `color`, `emoji`) VALUES
        (96, 'Location: The Burnt Forest', 'a0461c', 'fa-solid fa-trees'),
        (97, 'Location: The Fructal Plane', 'f95d09', 'fa-solid fa-strawberry')
        ON DUPLICATE KEY UPDATE id=id;
        EOSQL);

        // gooderberry:
        $this->addSql(<<<EOSQL
        -- enchantment effect
        INSERT INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`) VALUES (498,0,1,0,1,0,0,0,0,0,0,0,0,0,0,0,NULL,0,0,0,0,NULL,NULL,0,1,0,0,0,0,0,0,0,0,0,"The Fructal Plane",NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- enchantment
        INSERT INTO enchantment (`id`, `effects_id`, `name`, `is_suffix`, `aura_id`) VALUES (148,498,"Gooder",0,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- hat
        INSERT INTO item_hat (`id`, `head_x`, `head_y`, `head_angle`, `head_scale`, `head_angle_fixed`) VALUES (286,0.515,0.825,0,0.5,0) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- food effect
        INSERT INTO item_food (`id`, `food`, `love`, `junk`, `alcohol`, `earthy`, `fruity`, `tannic`, `spicy`, `creamy`, `meaty`, `planty`, `fishy`, `floral`, `fatty`, `oniony`, `chemically`, `caffeine`, `psychedelic`, `granted_skill`, `chance_for_bonus_item`, `random_flavor`, `contains_tentacles`, `granted_status_effect`, `granted_status_effect_duration`, `is_candy`, `leftovers_id`, `bonus_item_group_id`) VALUES (521,6,4,0,0,3,3,0,0,0,0,0,0,0,0,0,3,0,0,NULL,NULL,0,0,"Inspired",270,0,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1447,"Gooderberry",NULL,"fruit/gooderberry",NULL,NULL,521,10,NULL,286,0,0,148,NULL,NULL,0,NULL,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1532,1447,"a") ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- item groups
        INSERT IGNORE INTO item_group_item (item_group_id, item_id) VALUES (7, 1447), (28, 1447), (46, 1447);
        EOSQL);

        // evilberries:
        $this->addSql(<<<EOSQL
        -- enchantment effect
        INSERT INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`) VALUES (499,0,0,0,1,0,0,0,0,0,0,0,0,0,1,0,NULL,0,0,0,0,108,415,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- enchantment
        INSERT INTO enchantment (`id`, `effects_id`, `name`, `is_suffix`, `aura_id`) VALUES (149,499,"Volatitheric",0,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- hat
        INSERT INTO item_hat (`id`, `head_x`, `head_y`, `head_angle`, `head_scale`, `head_angle_fixed`) VALUES (287,0.42,0.815,-16,0.25,0) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1448,"Evilberries","These berries are _much_ too flammable to eat. (You know how it is.) At least they make good decorations??","fruit/evilberries",NULL,NULL,NULL,0,NULL,287,300,0,149,NULL,NULL,0,NULL,0,5) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1533,1448,"some") ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- item groups
        INSERT IGNORE INTO item_group_item (item_group_id, item_id) VALUES (7, 1448), (28, 1448);
        EOSQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
