<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230129235421 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            INSERT INTO `user_activity_log_tag` (`title`, `color`, `emoji`) VALUES
                ('Moneys', 'F9C206', '💰'),
                ('Recycling', '2AA82C', '♻️'),
                ('Market', '3E9FC9', '🪙'),
                ('Fireplace', 'F85E0F', '🧱'),
                ('Greenhouse', '23C143', '🎍'),
                ('Beehive', 'FAD12C', '🐝'),
                ('Dragon Den', 'FFE42D', '🐲')
            ;
        ");

        $this->addSql("
            INSERT INTO `user_activity_log` (user_id, entry, created_on)
            SELECT user_id,CONCAT(description, ' (',amount,'~~m~~)'),datetime AS created_on FROM transaction_history;
        ");

        $this->addSql("
            INSERT INTO `user_activity_log_user_activity_log_tag` (user_activity_log_id, user_activity_log_tag_id)
            SELECT id, 1 FROM user_activity_log
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
