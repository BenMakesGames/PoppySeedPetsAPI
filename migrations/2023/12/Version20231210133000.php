<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231210133000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Stocking Stuffing Season user activity log tag.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<EOSQL
            INSERT IGNORE INTO user_activity_log_tag (id, title, color, emoji)
            VALUES (20, 'Stocking Stuffing Season', 'A3EEF6', '❄️')
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
