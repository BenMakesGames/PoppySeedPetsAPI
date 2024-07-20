<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240713152000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Sour Cream and Onion dip
        $this->addSql(<<<EOSQL
        -- enchantment effect
        INSERT INTO item_tool (`id`, `stealth`, `nature`, `brawl`, `arcana`, `crafts`, `grip_x`, `grip_y`, `grip_angle`, `grip_scale`, `fishing`, `gathering`, `music`, `smithing`, `science`, `grip_angle_fixed`, `focus_skill`, `provides_light`, `protection_from_heat`, `always_in_front`, `is_ranged`, `when_gather_id`, `when_gather_also_gather_id`, `climbing`, `leads_to_adventure`, `prevents_bugs`, `attracts_bugs`, `can_be_nibbled`, `increases_pooping`, `dreamcatcher`, `is_grayscaling`, `social_energy_modifier`, `sex_drive`, `when_gather_prevent_gather`, `adventure_description`, `when_gather_apply_status_effect`, `when_gather_apply_status_effect_duration`) VALUES (470,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,NULL,0,0,0,0,7,304,0,0,0,1,0,0,0,0,0,0,0,NULL,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- enchantment
        INSERT INTO enchantment (`id`, `effects_id`, `name`, `is_suffix`, `aura_id`) VALUES (138,470,"Sour Cream and Onion",0,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- hat
        INSERT INTO item_hat (`id`, `head_x`, `head_y`, `head_angle`, `head_scale`, `head_angle_fixed`) VALUES (258,0.505,0.665,0,0.55,0) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- food effect
        INSERT INTO item_food (`id`, `food`, `love`, `junk`, `alcohol`, `earthy`, `fruity`, `tannic`, `spicy`, `creamy`, `meaty`, `planty`, `fishy`, `floral`, `fatty`, `oniony`, `chemically`, `caffeine`, `psychedelic`, `granted_skill`, `chance_for_bonus_item`, `random_flavor`, `contains_tentacles`, `granted_status_effect`, `granted_status_effect_duration`, `is_candy`, `leftovers_id`, `bonus_item_group_id`) VALUES (509,3,0,0,0,0,0,0,0,1,0,0,0,0,1,2,0,0,0,NULL,NULL,0,0,NULL,NULL,0,NULL,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1396,"Sour Cream and Onion Dip",NULL,"ingredient/sour-cream-and-onion",NULL,NULL,509,3,NULL,258,0,0,138,NULL,NULL,0,NULL,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1487,1396,NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // Ice "Mango"
        $this->addSql('UPDATE `item` SET `name`=\'Ice "Mango"\' WHERE `id`=1395;');
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description`='This is not merely some ice-encrusted Mango! Ha! _Perish_ the thought!' WHERE  `id`=1395;
        EOSQL);

        // Onion description fix
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = '\"What can you do with onions?\" \"WHAT CAN YOU DO WITH ONIONS?!?\" I mean, Onion Rings, for one! Coat \'em in a mixture of flour and Baking Soda... dip \'em in Oil... MM!\n\n<small>\"What can you do with onions.\" \\*shakes head\\* Some people; honestly...</small>' WHERE `item`.`id` = 7;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
