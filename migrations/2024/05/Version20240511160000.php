<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240511160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Pommegranite';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
        -- the item itself!
        INSERT INTO item (`id`, `name`, `description`, `image`, `use_actions`, `tool_id`, `food_id`, `fertilizer`, `plant_id`, `hat_id`, `fuel`, `recycle_value`, `enchants_id`, `spice_id`, `treasure_id`, `is_bug`, `hollow_earth_tile_card_id`, `cannot_be_thrown_out`, `museum_points`) VALUES (1384,"Pommegranite","This strange fruit from the Umbra is not safe to eat. Some people say it\'s an aphrodisiac, but those people say that about, like, _everything_.","fruit/pommegranite","[[\"Smash\",\"pommegranite\\/#\\/smash\"]]",NULL,NULL,14,NULL,NULL,0,0,NULL,NULL,NULL,0,NULL,0,1) ON DUPLICATE KEY UPDATE `id` = `id`;
        
        -- grammar
        INSERT INTO item_grammar (`id`, `item_id`, `article`) VALUES (1475,1384,"a") ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
