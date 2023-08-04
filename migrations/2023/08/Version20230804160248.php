<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230804160248 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_species_collected (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, species_id INT NOT NULL, discovered_on DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_681CA342A76ED395 (user_id), INDEX IDX_681CA342B2A1D860 (species_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_species_collected ADD CONSTRAINT FK_681CA342A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_species_collected ADD CONSTRAINT FK_681CA342B2A1D860 FOREIGN KEY (species_id) REFERENCES pet_species (id)');
        $this->addSql('ALTER TABLE pet_species ADD zoologist_thoughts LONGTEXT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_species_collected DROP FOREIGN KEY FK_681CA342A76ED395');
        $this->addSql('ALTER TABLE user_species_collected DROP FOREIGN KEY FK_681CA342B2A1D860');
        $this->addSql('DROP TABLE user_species_collected');
        $this->addSql('ALTER TABLE pet_species DROP zoologist_thoughts');
    }
}
