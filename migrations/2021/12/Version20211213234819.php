<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211213234819 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dragon_hostage (id INT AUTO_INCREMENT NOT NULL, dragon_id INT NOT NULL, type VARCHAR(40) NOT NULL, name VARCHAR(40) NOT NULL, appearance VARCHAR(40) NOT NULL, dialog VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_29942C3E685856D6 (dragon_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dragon_hostage ADD CONSTRAINT FK_29942C3E685856D6 FOREIGN KEY (dragon_id) REFERENCES dragon (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE dragon_hostage');
    }
}
