<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190615141459 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE item ADD food LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', ADD size INT NOT NULL, CHANGE name name VARCHAR(40) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1F1B251E5E237E06 ON item (name)');
        $this->addSql('ALTER TABLE pet ADD is_dead TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE name name VARCHAR(40) NOT NULL, CHANGE session_id session_id VARCHAR(40) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649613FECDF ON user (session_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_1F1B251E5E237E06 ON item');
        $this->addSql('ALTER TABLE item DROP food, DROP size, CHANGE name name VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE pet DROP is_dead');
        $this->addSql('DROP INDEX UNIQ_8D93D649613FECDF ON user');
        $this->addSql('ALTER TABLE user CHANGE name name VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE session_id session_id VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
