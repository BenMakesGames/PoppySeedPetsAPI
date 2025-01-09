<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250109170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Heliotropic Oobleck should have the Skill Food item group
        $this->addSql(<<<EOSQL
        INSERT INTO item_group_item (item_group_id, item_id)
        SELECT 21 AS item_group_id, item.id AS item_id
        FROM item
        WHERE item.name = 'Heliotropic Oobleck'
        ON DUPLICATE KEY UPDATE `item_group_id` = `item_group_id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
