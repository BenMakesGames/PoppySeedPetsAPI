<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230311010000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO `user_activity_log_tag` (`title`, `color`, `emoji`) VALUES ('Account & Security', 'F950FC', '🔑');");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM user_activity_log_tag WHERE title = \'Account & Security\' LIMIT 1');
    }
}
