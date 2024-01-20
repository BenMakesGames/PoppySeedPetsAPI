<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240120140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create book item group';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
            INSERT INTO `item_group` (`id`, `name`, `is_craving`, `is_gift_shop`) VALUES (40, 'Book', '0', '0')
            ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
