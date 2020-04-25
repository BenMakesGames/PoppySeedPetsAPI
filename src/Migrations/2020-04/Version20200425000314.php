<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200425000314 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE lunchbox_item (id INT AUTO_INCREMENT NOT NULL, pet_id INT NOT NULL, inventory_item_id INT NOT NULL, INDEX IDX_7440444C966F7FB6 (pet_id), INDEX IDX_7440444C536BF4A2 (inventory_item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lunchbox_item ADD CONSTRAINT FK_7440444C966F7FB6 FOREIGN KEY (pet_id) REFERENCES pet (id)');
        $this->addSql('ALTER TABLE lunchbox_item ADD CONSTRAINT FK_7440444C536BF4A2 FOREIGN KEY (inventory_item_id) REFERENCES inventory (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE lunchbox_item');
    }
}
