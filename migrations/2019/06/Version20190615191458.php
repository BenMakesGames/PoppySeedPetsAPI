<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190615191458 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE inventory ADD created_by_id INT DEFAULT NULL, ADD comments JSON NOT NULL, DROP comment');
        $this->addSql('ALTER TABLE inventory ADD CONSTRAINT FK_B12D4A36B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_B12D4A36B03A8386 ON inventory (created_by_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE inventory DROP FOREIGN KEY FK_B12D4A36B03A8386');
        $this->addSql('DROP INDEX IDX_B12D4A36B03A8386 ON inventory');
        $this->addSql('ALTER TABLE inventory ADD comment VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, DROP created_by_id, DROP comments');
    }
}
