<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191223030512 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE story (id INT AUTO_INCREMENT NOT NULL, first_section_id INT NOT NULL, title VARCHAR(40) NOT NULL, UNIQUE INDEX UNIQ_EB56043849BB827D (first_section_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE story_section (id INT AUTO_INCREMENT NOT NULL, story_id INT NOT NULL, style VARCHAR(20) NOT NULL, background VARCHAR(40) DEFAULT NULL, image VARCHAR(40) DEFAULT NULL, content LONGTEXT NOT NULL, choices JSON NOT NULL, INDEX IDX_7E8FEA6DAA5D4036 (story_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE story ADD CONSTRAINT FK_EB56043849BB827D FOREIGN KEY (first_section_id) REFERENCES story_section (id)');
        $this->addSql('ALTER TABLE story_section ADD CONSTRAINT FK_7E8FEA6DAA5D4036 FOREIGN KEY (story_id) REFERENCES story (id)');
        $this->addSql('ALTER TABLE user_quest CHANGE name name VARCHAR(120) NOT NULL');
        $this->addSql('CREATE INDEX name_idx ON user_quest (name)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE story_section DROP FOREIGN KEY FK_7E8FEA6DAA5D4036');
        $this->addSql('ALTER TABLE story DROP FOREIGN KEY FK_EB56043849BB827D');
        $this->addSql('DROP TABLE story');
        $this->addSql('DROP TABLE story_section');
        $this->addSql('DROP INDEX name_idx ON user_quest');
        $this->addSql('ALTER TABLE user_quest CHANGE name name VARCHAR(40) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
