<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190804170347 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pet_relationship (id INT AUTO_INCREMENT NOT NULL, pet_id INT NOT NULL, relationship_id INT NOT NULL, intimacy INT NOT NULL, passion INT NOT NULL, commitment INT NOT NULL, met_description VARCHAR(255) NOT NULL, met_on DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_C48C5FF1966F7FB6 (pet_id), INDEX IDX_C48C5FF12C41D668 (relationship_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pet_relationship ADD CONSTRAINT FK_C48C5FF1966F7FB6 FOREIGN KEY (pet_id) REFERENCES pet (id)');
        $this->addSql('ALTER TABLE pet_relationship ADD CONSTRAINT FK_C48C5FF12C41D668 FOREIGN KEY (relationship_id) REFERENCES pet (id)');

        // increase all pets' stomach sizes by 6
        $this->addSql('UPDATE pet SET stomach_size=stomach_size+6');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE pet_relationship');
    }
}
