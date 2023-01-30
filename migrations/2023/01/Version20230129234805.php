<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230129234805 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_activity_log (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, entry LONGTEXT NOT NULL, created_on DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7DCA8A45A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_activity_log_user_activity_log_tag (user_activity_log_id INT NOT NULL, user_activity_log_tag_id INT NOT NULL, INDEX IDX_739E9F6AAACDD109 (user_activity_log_id), INDEX IDX_739E9F6AD00961C9 (user_activity_log_tag_id), PRIMARY KEY(user_activity_log_id, user_activity_log_tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_activity_log_tag (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(40) NOT NULL, color VARCHAR(6) NOT NULL, emoji VARCHAR(12) NOT NULL, UNIQUE INDEX UNIQ_112BAA652B36786B (title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_activity_log ADD CONSTRAINT FK_7DCA8A45A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_activity_log_user_activity_log_tag ADD CONSTRAINT FK_739E9F6AAACDD109 FOREIGN KEY (user_activity_log_id) REFERENCES user_activity_log (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_activity_log_user_activity_log_tag ADD CONSTRAINT FK_739E9F6AD00961C9 FOREIGN KEY (user_activity_log_tag_id) REFERENCES user_activity_log_tag (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_activity_log DROP FOREIGN KEY FK_7DCA8A45A76ED395');
        $this->addSql('ALTER TABLE user_activity_log_user_activity_log_tag DROP FOREIGN KEY FK_739E9F6AAACDD109');
        $this->addSql('ALTER TABLE user_activity_log_user_activity_log_tag DROP FOREIGN KEY FK_739E9F6AD00961C9');
        $this->addSql('DROP TABLE user_activity_log');
        $this->addSql('DROP TABLE user_activity_log_user_activity_log_tag');
        $this->addSql('DROP TABLE user_activity_log_tag');
    }
}
