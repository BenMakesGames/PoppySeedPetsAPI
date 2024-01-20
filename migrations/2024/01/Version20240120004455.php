<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240120004455 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX sell_price_idx ON inventory');
        $this->addSql('ALTER TABLE inventory DROP sell_price, DROP sell_list_date');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE inventory ADD sell_price INT DEFAULT NULL, ADD sell_list_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE INDEX sell_price_idx ON inventory (sell_price)');
    }
}
