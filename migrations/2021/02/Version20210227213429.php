<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210227213429 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article_design_goal (article_id INT NOT NULL, design_goal_id INT NOT NULL, INDEX IDX_3B79C5EC7294869C (article_id), INDEX IDX_3B79C5ECFFD68AD4 (design_goal_id), PRIMARY KEY(article_id, design_goal_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE design_goal (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(40) NOT NULL, description LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE article_design_goal ADD CONSTRAINT FK_3B79C5EC7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_design_goal ADD CONSTRAINT FK_3B79C5ECFFD68AD4 FOREIGN KEY (design_goal_id) REFERENCES design_goal (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article_design_goal DROP FOREIGN KEY FK_3B79C5ECFFD68AD4');
        $this->addSql('DROP TABLE article_design_goal');
        $this->addSql('DROP TABLE design_goal');
    }
}
