<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250109154300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Wheat Flour should have the Cooking group!
        $this->addSql(<<<EOSQL
        INSERT INTO item_group_item (item_group_id, item_id)
        SELECT 46 AS item_group_id, item.id AS item_id
        FROM item
        WHERE item.name IN (
            'Wheat Flour'
        )
        ON DUPLICATE KEY UPDATE `item_group_id` = `item_group_id`;
        EOSQL);

        // cleaning up some inappropriate pet names
        $this->addSql("UPDATE pet SET name='Benmakesgames is a ne\\'er-do-well' WHERE id=2238");
        $this->addSql("UPDATE pet SET name='Sbutt' WHERE id=1686");
        $this->addSql("UPDATE pet SET name='umplsing opo pete' WHERE id=12716");
        $this->addSql("UPDATE pet SET name='Derumplsing S' WHERE id=48100");
    }

    public function down(Schema $schema): void
    {
    }
}
