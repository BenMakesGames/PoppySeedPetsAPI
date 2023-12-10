<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231210110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Rock-painting Kit (for Kids) and Sneqos & Ladders.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<EOSQL
            -- the item itself!
            INSERT IGNORE INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1349,"Rock-painting Kit (for Kids)","There\'s a rock-painting kit for _adults?_ >_>","box/rock-painting-kit","[[\"Open\",\"ohGames\\/#\\/rockPaintingKit\"]]",NULL,NULL,0,NULL,NULL,120,0,NULL,NULL,NULL,0,NULL,0,1);
            
            -- grammar
            INSERT IGNORE INTO item_grammar (`id`, `item_id`, `article`) VALUES (1459,1349,"a");

            -- the item itself!
            INSERT IGNORE INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1350,"Sneqos & Ladders","Some assembly required.","box/snakes-and-ladders","[[\"Open\",\"ohGames\\/#\\/sneqosAndLadders\"]]",NULL,NULL,0,NULL,NULL,120,0,NULL,NULL,NULL,0,NULL,0,1);
            
            -- grammar
            INSERT IGNORE INTO item_grammar (`id`, `item_id`, `article`) VALUES (1460,1350,NULL);
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
