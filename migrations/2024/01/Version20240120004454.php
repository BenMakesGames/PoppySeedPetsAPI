<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240120004454 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO inventory_for_sale (inventory_id, sell_price, sell_list_date) SELECT id, sell_price, sell_list_date FROM inventory WHERE sell_price IS NOT NULL');
    }

    public function down(Schema $schema): void
    {
    }
}
