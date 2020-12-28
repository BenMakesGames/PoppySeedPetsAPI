<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201227215732 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE item_treasure (id INT AUTO_INCREMENT NOT NULL, item_id INT NOT NULL, silver INT NOT NULL, gold INT NOT NULL, gems INT NOT NULL, UNIQUE INDEX UNIQ_ADC8A2B4126F525E (item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE item_treasure ADD CONSTRAINT FK_ADC8A2B4126F525E FOREIGN KEY (item_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE dragon ADD silver INT NOT NULL, ADD gold INT NOT NULL, ADD gems INT NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE item_treasure');
        $this->addSql('ALTER TABLE dragon DROP silver, DROP gold, DROP gems');
    }
}
