<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240625232823 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_activity_log_tag CHANGE emoji emoji VARCHAR(100) NOT NULL');

        $this->addSql(<<<EOSQL
        INSERT INTO user_activity_log_tag
        (title, color, emoji) VALUES
        ('Item Use', '999999', 'fa-solid fa-hand-sparkles')
        EOSQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_activity_log_tag CHANGE emoji emoji VARCHAR(12) NOT NULL');
    }
}
