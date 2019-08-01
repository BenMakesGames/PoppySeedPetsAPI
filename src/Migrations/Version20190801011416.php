<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190801011416 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE park_event (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(40) NOT NULL, results LONGTEXT NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE park_event_pet (park_event_id INT NOT NULL, pet_id INT NOT NULL, INDEX IDX_8BE50733000B791 (park_event_id), INDEX IDX_8BE5073966F7FB6 (pet_id), PRIMARY KEY(park_event_id, pet_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE park_event_pet ADD CONSTRAINT FK_8BE50733000B791 FOREIGN KEY (park_event_id) REFERENCES park_event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE park_event_pet ADD CONSTRAINT FK_8BE5073966F7FB6 FOREIGN KEY (pet_id) REFERENCES pet (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE park_event_pet DROP FOREIGN KEY FK_8BE50733000B791');
        $this->addSql('DROP TABLE park_event');
        $this->addSql('DROP TABLE park_event_pet');
    }
}
