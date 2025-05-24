<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250524225808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
        -- food effect
        INSERT INTO item_food (`id`, `food`, `love`, `junk`, `alcohol`, `earthy`, `fruity`, `tannic`, `spicy`, `creamy`, `meaty`, `planty`, `fishy`, `floral`, `fatty`, `oniony`, `chemically`, `caffeine`, `psychedelic`, `granted_skill`, `chance_for_bonus_item`, `random_flavor`, `contains_tentacles`, `granted_status_effect`, `granted_status_effect_duration`, `is_candy`, `leftovers_id`, `bonus_item_group_id`) VALUES (524,1,4,1,0,0,0,0,0,0,0,0,0,0,0,0,3,0,0,NULL,NULL,0,0,NULL,NULL,1,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1468,"Licorice Gooplin",NULL,"candy/gummy/licorice-gooplin",NULL,NULL,524,5,NULL,NULL,0,0,NULL,NULL,NULL,0,NULL,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1553,1468,"a") ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- item groups
        INSERT IGNORE INTO item_group_item (item_group_id, item_id) VALUES (46, 1468);
        EOSQL);

        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'Some people say Gooplins smell like licorice? Apparently that\'s the origin for this candy, anyway. I don\'t think _my_ Gooplin smells like that, but my Gooplin also has Black Hole Tum, so mostly he just smells like... _Dark Matter_...' WHERE `item`.`id` = 1468; 
        EOSQL);

        $this->addSql(<<<EOSQL
        INSERT INTO `item_tool` (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`, `physics`, `electronics`, `hacking`, `umbra`, `magic_binding`, `mining`) VALUES (514, '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', NULL, '0', '0', '0', '0', '12', '1468', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', NULL, NULL, NULL, '0', '0', '0', '0', '0', '0')
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        $this->addSql(<<<EOSQL
        INSERT INTO `enchantment` (`id`, `effects_id`, `name`, `is_suffix`, `aura_id`) VALUES (152, '514', 'Glycyrrhiza', '0', NULL)
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
