<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240120133119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'delete duplicate museum donations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            DELETE FROM museum_item
            WHERE id IN (
                SELECT id FROM (
                    SELECT id, ROW_NUMBER() OVER (PARTITION BY user_id, item_id ORDER BY id) AS rn
                    FROM museum_item
                ) AS t
                WHERE rn > 1
            );
        ');

        $this->addSql('CREATE UNIQUE INDEX user_id_item_id_idx ON museum_item (user_id, item_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX user_id_item_id_idx ON museum_item');
    }
}
