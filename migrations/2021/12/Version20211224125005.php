<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211224125005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE survey (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', end_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', guid CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', UNIQUE INDEX UNIQ_AD5F9BFC2B6FCFB2 (guid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE survey_question (id INT AUTO_INCREMENT NOT NULL, survey_id INT NOT NULL, title VARCHAR(255) NOT NULL, type VARCHAR(40) NOT NULL, INDEX IDX_EA000F69B3FE509D (survey_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE survey_question_answer (id INT AUTO_INCREMENT NOT NULL, question_id INT NOT NULL, user_id INT NOT NULL, answer LONGTEXT NOT NULL, created_on DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7554B7191E27F6BF (question_id), INDEX IDX_7554B719A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE survey_question ADD CONSTRAINT FK_EA000F69B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id)');
        $this->addSql('ALTER TABLE survey_question_answer ADD CONSTRAINT FK_7554B7191E27F6BF FOREIGN KEY (question_id) REFERENCES survey_question (id)');
        $this->addSql('ALTER TABLE survey_question_answer ADD CONSTRAINT FK_7554B719A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE survey_question DROP FOREIGN KEY FK_EA000F69B3FE509D');
        $this->addSql('ALTER TABLE survey_question_answer DROP FOREIGN KEY FK_7554B7191E27F6BF');
        $this->addSql('DROP TABLE survey');
        $this->addSql('DROP TABLE survey_question');
        $this->addSql('DROP TABLE survey_question_answer');
    }
}
