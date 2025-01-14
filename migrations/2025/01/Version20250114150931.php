<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250114150931 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pet_badge (id INT AUTO_INCREMENT NOT NULL, pet_id INT NOT NULL, badge VARCHAR(40) NOT NULL, date_acquired DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_538C88BE966F7FB6 (pet_id), INDEX badge_idx (badge), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pet_badge ADD CONSTRAINT FK_538C88BE966F7FB6 FOREIGN KEY (pet_id) REFERENCES pet (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pet_badge DROP FOREIGN KEY FK_538C88BE966F7FB6');
        $this->addSql('DROP TABLE pet_badge');
    }
}
