<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220122154858 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pet_activity_log_pet_activity_log_tag (pet_activity_log_id INT NOT NULL, pet_activity_log_tag_id INT NOT NULL, INDEX IDX_71B15D7C9F3A396D (pet_activity_log_id), INDEX IDX_71B15D7CCEF233DC (pet_activity_log_tag_id), PRIMARY KEY(pet_activity_log_id, pet_activity_log_tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pet_activity_log_tag (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(40) NOT NULL, color VARCHAR(6) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pet_activity_log_pet_activity_log_tag ADD CONSTRAINT FK_71B15D7C9F3A396D FOREIGN KEY (pet_activity_log_id) REFERENCES pet_activity_log (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pet_activity_log_pet_activity_log_tag ADD CONSTRAINT FK_71B15D7CCEF233DC FOREIGN KEY (pet_activity_log_tag_id) REFERENCES pet_activity_log_tag (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pet_activity_log_pet_activity_log_tag DROP FOREIGN KEY FK_71B15D7CCEF233DC');
        $this->addSql('DROP TABLE pet_activity_log_pet_activity_log_tag');
        $this->addSql('DROP TABLE pet_activity_log_tag');
    }
}
