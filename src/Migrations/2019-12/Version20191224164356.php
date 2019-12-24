<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191224164356 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pet_group (id INT AUTO_INCREMENT NOT NULL, type INT NOT NULL, progress INT NOT NULL, skill_roll_total INT NOT NULL, created_on DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pet_group_pet (pet_group_id INT NOT NULL, pet_id INT NOT NULL, INDEX IDX_825CBAF8B723D08A (pet_group_id), INDEX IDX_825CBAF8966F7FB6 (pet_id), PRIMARY KEY(pet_group_id, pet_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pet_group_pet ADD CONSTRAINT FK_825CBAF8B723D08A FOREIGN KEY (pet_group_id) REFERENCES pet_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pet_group_pet ADD CONSTRAINT FK_825CBAF8966F7FB6 FOREIGN KEY (pet_id) REFERENCES pet (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet_group_pet DROP FOREIGN KEY FK_825CBAF8B723D08A');
        $this->addSql('DROP TABLE pet_group');
        $this->addSql('DROP TABLE pet_group_pet');
    }
}
