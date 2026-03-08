<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260308152646 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
        -- hollow earth tile card
        INSERT INTO hollow_earth_tile_card (`id`, `type_id`, `name`, `event`, `required_action`, `image`, `author`) VALUES (91,12,"Friendly Faefolk?","{\"exp\":{\"stats\":[\"arcana\"],\"amount\":1},\"type\":\"onward\",\"buttonText\":\"Weird!\",\"description\":\"You see a party of faefolk dancing on and around the pier. %pet.name% goes to join them, and time begins slipping past you. You awake from laughter-filled dreams to find yourself back where you started, AND with a basement that can hold %player.basementSize% items!\",\"increaseBasement\":5}",2,"friendly-faefolk",NULL) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1515,"Tile: Friendly Faefolk?",NULL,"tile/friendly-faefolk",NULL,NULL,NULL,0,NULL,NULL,30,1,NULL,NULL,NULL,0,91,0,15) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- item groups
        INSERT IGNORE INTO item_group_item (item_group_id, item_id) VALUES (5, 1515);
        EOSQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
