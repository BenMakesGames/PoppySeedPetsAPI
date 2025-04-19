<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250419183219 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX location_idx ON inventory
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX owner_location_item_idx ON inventory (owner_id, location, item_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX owner_location_item_idx ON inventory
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX location_idx ON inventory (location)
        SQL);
    }
}
