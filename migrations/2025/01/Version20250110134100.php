<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250110134100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Mystery Syrup is now a hat:
        $this->addSql(<<<EOSQL
        INSERT INTO `item_hat` (`id`, `head_x`, `head_y`, `head_angle`, `head_scale`, `head_angle_fixed`) VALUES (274, '0.495', '0.73', '0', '0.55', '0')
        ON DUPLICATE KEY UPDATE `id`=`id`;

        UPDATE `item` SET `hat_id` = '274' WHERE `item`.`id` = 1307;
        EOSQL);

        // bulbun plushy is now a hat:
        $this->addSql(<<<EOSQL
        INSERT INTO `item_hat` (`id`, `head_x`, `head_y`, `head_angle`, `head_scale`, `head_angle_fixed`) VALUES (275, '0.53', '0.91', '0', '0.6', '0')
        ON DUPLICATE KEY UPDATE `id`=`id`;

        UPDATE `item` SET `hat_id` = '275' WHERE `item`.`id` = 742;
        EOSQL);

        // takoyaki is now a hat:
        $this->addSql(<<<EOSQL
        INSERT INTO `item_hat` (`id`, `head_x`, `head_y`, `head_angle`, `head_scale`, `head_angle_fixed`) VALUES (276, '0.595', '0.525', '34', '0.6', '0')
        ON DUPLICATE KEY UPDATE `id`=`id`;

        UPDATE `item` SET `hat_id` = '276' WHERE `item`.`id` = 1356;
        EOSQL);

        // scroll of the star monkey
        $this->addSql(<<<EOSQL
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1429,"Scroll of the Star Monkey","You could read this scroll, sure; nothing wrong with that. Alternatively, you could also give it two Naners!
        
        Curious...","scroll/star-monkey","[[\"Read it\",\"scroll\\/starMonkey\\/#\\/items\"],[\"Give it Two Naners\",\"scroll\\/starMonkey\\/#\\/summoningScroll\"]]",NULL,NULL,0,NULL,NULL,720,0,NULL,NULL,NULL,0,NULL,0,5) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1514,1429,"a") ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- item groups
        INSERT IGNORE INTO item_group_item (item_group_id, item_id) VALUES (44, 1429);
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
