<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190706163631 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user_notification_preferences (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, email_new_news TINYINT(1) NOT NULL, push_new_news TINYINT(1) NOT NULL, email_pet_reminders INT DEFAULT NULL, push_pet_reminders INT DEFAULT NULL, UNIQUE INDEX UNIQ_207F257FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_notification_preferences ADD CONSTRAINT FK_207F257FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');

        // add new row for each user
        $this->addSql('INSERT INTO user_notification_preferences (user_id,email_new_news,push_new_news) SELECT id,0,0 FROM user');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE user_notification_preferences');
    }
}
