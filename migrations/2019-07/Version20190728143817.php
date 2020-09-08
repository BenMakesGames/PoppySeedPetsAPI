<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190728143817 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE park_event ADD is_full TINYINT(1) NOT NULL');
        $this->addSql('CREATE INDEX ran_on_idx ON park_event (ran_on)');
        $this->addSql('CREATE INDEX is_full_idx ON park_event (is_full)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX ran_on_idx ON park_event');
        $this->addSql('DROP INDEX is_full_idx ON park_event');
        $this->addSql('ALTER TABLE park_event DROP is_full');
    }
}
