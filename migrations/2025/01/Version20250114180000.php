<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250114180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Badge! pet tag
        $this->addSql(<<<EOSQL
        INSERT INTO `pet_activity_log_tag` (`id`, `title`, `color`, `emoji`) VALUES (94, 'Badge!', 'E28024', 'fa-solid fa-badge-check')        
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // pets that have Metatron's Touch get the Brilliant Badge:
        $this->addSql(<<<EOSQL
        INSERT INTO pet_badge (pet_id, badge, date_acquired)
        SELECT pet_id AS pet_id, 'foundMetatronsFire' AS badge, NOW() AS date_acquired
        FROM pet_merit WHERE merit_id=61
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
