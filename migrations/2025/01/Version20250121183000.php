<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250121183000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // hat data for canned food:
        $this->addSql(<<<EOSQL
        INSERT INTO `item_hat` (`id`, `head_x`, `head_y`, `head_angle`, `head_scale`, `head_angle_fixed`) VALUES (280, '0.495', '0.75', '0', '0.38', '0')
        ON DUPLICATE KEY UPDATE `id` = `id`;
        EOSQL);

        // add hat to canned food:
        $this->addSql('UPDATE `item` SET `hat_id` = 280 WHERE `item`.`id` = 814;');
    }

    public function down(Schema $schema): void
    {
    }
}
