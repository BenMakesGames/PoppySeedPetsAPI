<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230707221407 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pet_baby ADD spirit_parent_id INT DEFAULT NULL, CHANGE other_parent_id other_parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pet_baby ADD CONSTRAINT FK_9C246454C3065B47 FOREIGN KEY (spirit_parent_id) REFERENCES spirit_companion (id)');
        $this->addSql('CREATE INDEX IDX_9C246454C3065B47 ON pet_baby (spirit_parent_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pet_baby DROP FOREIGN KEY FK_9C246454C3065B47');
        $this->addSql('DROP INDEX IDX_9C246454C3065B47 ON pet_baby');
        $this->addSql('ALTER TABLE pet_baby DROP spirit_parent_id, CHANGE other_parent_id other_parent_id INT NOT NULL');
    }
}
