<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250114143605 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE house_sitter DROP FOREIGN KEY FK_ACF2ACC9A44B917');
        $this->addSql('ALTER TABLE house_sitter DROP FOREIGN KEY FK_ACF2ACCA76ED395');
        $this->addSql('DROP TABLE house_sitter');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE house_sitter (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, house_sitter_id INT NOT NULL, INDEX IDX_ACF2ACC9A44B917 (house_sitter_id), UNIQUE INDEX UNIQ_ACF2ACCA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE house_sitter ADD CONSTRAINT FK_ACF2ACC9A44B917 FOREIGN KEY (house_sitter_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE house_sitter ADD CONSTRAINT FK_ACF2ACCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
