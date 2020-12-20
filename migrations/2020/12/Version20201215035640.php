<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201215035640 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE letter (id INT AUTO_INCREMENT NOT NULL, series VARCHAR(40) NOT NULL, body LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_letter (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, letter_id INT NOT NULL, received_on DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', comment VARCHAR(255) NOT NULL, INDEX IDX_EB50F537A76ED395 (user_id), INDEX IDX_EB50F5374525FF26 (letter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_letter ADD CONSTRAINT FK_EB50F537A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_letter ADD CONSTRAINT FK_EB50F5374525FF26 FOREIGN KEY (letter_id) REFERENCES letter (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_letter DROP FOREIGN KEY FK_EB50F5374525FF26');
        $this->addSql('DROP TABLE letter');
        $this->addSql('DROP TABLE user_letter');
    }
}
