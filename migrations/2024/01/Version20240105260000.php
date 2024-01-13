<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240105260000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'community booster pack';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
        INSERT INTO `item_group` (`id`, `name`, `is_craving`, `is_gift_shop`) VALUES
        (37, 'Community Booster Pack: Common', '0', '0'),
        (38, 'Community Booster Pack: Uncommon', '0', '0'),
        (39, 'Community Booster Pack: Rare', '0', '0')
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        //$this->addSql();
    }

    public function down(Schema $schema): void
    {
    }
}
