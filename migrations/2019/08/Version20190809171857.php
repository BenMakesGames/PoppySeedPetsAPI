<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190809171857 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE greenhouse_plant (id INT AUTO_INCREMENT NOT NULL, plant_id INT NOT NULL, growth INT NOT NULL, weeds INT NOT NULL, last_interaction DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_477F79E31D935652 (plant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE greenhouse_plant ADD CONSTRAINT FK_477F79E31D935652 FOREIGN KEY (plant_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE user ADD unlocked_greenhouse DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE greenhouse_plant');
        $this->addSql('ALTER TABLE user DROP unlocked_greenhouse');
    }
}
