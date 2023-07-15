<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230715193747 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE push_subscription DROP FOREIGN KEY FK_562830F3A76ED395');
        $this->addSql('ALTER TABLE transaction_history DROP FOREIGN KEY FK_51104CA9A76ED395');
        $this->addSql('ALTER TABLE user_notification_preferences DROP FOREIGN KEY FK_207F257FA76ED395');
        $this->addSql('DROP TABLE push_subscription');
        $this->addSql('DROP TABLE transaction_history');
        $this->addSql('DROP TABLE user_notification_preferences');
        $this->addSql('ALTER TABLE user_link ADD visibility VARCHAR(20) NOT NULL, DROP category');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE push_subscription (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, endpoint VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, p256dh VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, auth VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_on DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', name VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_562830F3A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE transaction_history (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, datetime DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', amount INT NOT NULL, description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_51104CA9A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user_notification_preferences (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, push_new_news TINYINT(1) NOT NULL, push_reminders TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_207F257FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE push_subscription ADD CONSTRAINT FK_562830F3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE transaction_history ADD CONSTRAINT FK_51104CA9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_notification_preferences ADD CONSTRAINT FK_207F257FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_link ADD category VARCHAR(40) NOT NULL, DROP visibility');
    }
}
