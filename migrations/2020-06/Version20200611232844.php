<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200611232844 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE daily_market_inventory_transaction DROP FOREIGN KEY FK_B0E9DC806C755722');
        $this->addSql('DROP INDEX IDX_B0E9DC806C755722 ON daily_market_inventory_transaction');
        $this->addSql('ALTER TABLE daily_market_inventory_transaction DROP buyer_id, DROP item_exchange_identifier');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE daily_market_inventory_transaction ADD buyer_id INT NOT NULL, ADD item_exchange_identifier VARCHAR(40) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE daily_market_inventory_transaction ADD CONSTRAINT FK_B0E9DC806C755722 FOREIGN KEY (buyer_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_B0E9DC806C755722 ON daily_market_inventory_transaction (buyer_id)');
    }
}
