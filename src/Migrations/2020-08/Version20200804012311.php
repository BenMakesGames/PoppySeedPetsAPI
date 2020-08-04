<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200804012311 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pet_house_time (id INT AUTO_INCREMENT NOT NULL, pet_id INT NOT NULL, activity_time INT NOT NULL, social_energy INT NOT NULL, time_spent INT NOT NULL, last_social_hangout_attempt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_3072EEBF966F7FB6 (pet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pet_house_time ADD CONSTRAINT FK_3072EEBF966F7FB6 FOREIGN KEY (pet_id) REFERENCES pet (id)');

        $this->addSql('
            INSERT INTO pet_house_time (pet_id, activity_time, social_energy, time_spent, last_social_hangout_attempt)
            SELECT
                pet.id AS pet_id,
                pet.time AS activity_time,
                pet.social_energy AS social_energy,
                pet.time_spent AS time_spent,
                NOW()
            FROM pet
        ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE pet_house_time');
    }
}
