<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200814234414 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE plant_yield (id INT AUTO_INCREMENT NOT NULL, plant_id INT NOT NULL, min INT NOT NULL, max INT NOT NULL, INDEX IDX_4A9FDC831D935652 (plant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE plant_yield_item (id INT AUTO_INCREMENT NOT NULL, plant_yield_id INT NOT NULL, item_id INT NOT NULL, percent_chance INT NOT NULL, INDEX IDX_2423C124C80F6465 (plant_yield_id), INDEX IDX_2423C124126F525E (item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE plant_yield ADD CONSTRAINT FK_4A9FDC831D935652 FOREIGN KEY (plant_id) REFERENCES plant (id)');
        $this->addSql('ALTER TABLE plant_yield_item ADD CONSTRAINT FK_2423C124C80F6465 FOREIGN KEY (plant_yield_id) REFERENCES plant_yield (id)');
        $this->addSql('ALTER TABLE plant_yield_item ADD CONSTRAINT FK_2423C124126F525E FOREIGN KEY (item_id) REFERENCES item (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE plant_yield_item DROP FOREIGN KEY FK_2423C124C80F6465');
        $this->addSql('DROP TABLE plant_yield');
        $this->addSql('DROP TABLE plant_yield_item');
    }
}
