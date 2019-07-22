<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190722203534 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE spirit_companion (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(40) NOT NULL, image VARCHAR(40) NOT NULL, skill VARCHAR(40) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pet ADD spirit_companion_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pet ADD CONSTRAINT FK_E4529B8592EA9615 FOREIGN KEY (spirit_companion_id) REFERENCES spirit_companion (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E4529B8592EA9615 ON pet (spirit_companion_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet DROP FOREIGN KEY FK_E4529B8592EA9615');
        $this->addSql('DROP TABLE spirit_companion');
        $this->addSql('DROP INDEX UNIQ_E4529B8592EA9615 ON pet');
        $this->addSql('ALTER TABLE pet DROP spirit_companion_id');
    }
}
