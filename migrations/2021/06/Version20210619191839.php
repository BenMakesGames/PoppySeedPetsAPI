<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210619191839 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE field_guide_entry (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(20) NOT NULL, name VARCHAR(40) NOT NULL, image VARCHAR(40) DEFAULT NULL, description LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_field_guide_entry (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, entry_id INT NOT NULL, discovered_on DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', comment VARCHAR(255) NOT NULL, INDEX IDX_52945EC4A76ED395 (user_id), INDEX IDX_52945EC4BA364942 (entry_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_field_guide_entry ADD CONSTRAINT FK_52945EC4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_field_guide_entry ADD CONSTRAINT FK_52945EC4BA364942 FOREIGN KEY (entry_id) REFERENCES field_guide_entry (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_field_guide_entry DROP FOREIGN KEY FK_52945EC4BA364942');
        $this->addSql('DROP TABLE field_guide_entry');
        $this->addSql('DROP TABLE user_field_guide_entry');
    }
}
