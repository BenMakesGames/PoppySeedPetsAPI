<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240928091000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Sandwich OVERSIGHTS
        $this->addSql(<<<EOSQL
        INSERT INTO item_group_item (item_group_id,item_id)
        SELECT 14 AS item_group_id, id AS item_id
        FROM `item`
        WHERE name IN ('Hot Dog', 'Naner Dog')
        ON DUPLICATE KEY UPDATE `item_group_id` = `item_group_id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
