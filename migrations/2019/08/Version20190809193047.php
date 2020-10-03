<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190809193047 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE item_plant (id INT AUTO_INCREMENT NOT NULL, time_to_grow INT NOT NULL, yield INT NOT NULL, sprout_image VARCHAR(40) NOT NULL, medium_image VARCHAR(40) NOT NULL, adult_image VARCHAR(40) NOT NULL, harvestable_image VARCHAR(40) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE item ADD plant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E1D935652 FOREIGN KEY (plant_id) REFERENCES item_plant (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1F1B251E1D935652 ON item (plant_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E1D935652');
        $this->addSql('DROP TABLE item_plant');
        $this->addSql('DROP INDEX UNIQ_1F1B251E1D935652 ON item');
        $this->addSql('ALTER TABLE item DROP plant_id');
    }
}
