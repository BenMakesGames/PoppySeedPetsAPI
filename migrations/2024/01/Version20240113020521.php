<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240113020521 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add birdbath tag';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<EOSQL
        INSERT INTO `user_activity_log_tag` (`id`, `title`, `color`, `emoji`) VALUES (21, 'Birdbath', '23C143', '🐦')
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
