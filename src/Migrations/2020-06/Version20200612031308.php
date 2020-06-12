<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200612031308 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('TRUNCATE daily_market_inventory_transaction');
        $this->addSql('ALTER TABLE daily_market_inventory_transaction ADD item_id INT NOT NULL');
        $this->addSql('ALTER TABLE daily_market_inventory_transaction ADD CONSTRAINT FK_B0E9DC80126F525E FOREIGN KEY (item_id) REFERENCES item (id)');
        $this->addSql('CREATE INDEX IDX_B0E9DC80126F525E ON daily_market_inventory_transaction (item_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE daily_market_inventory_transaction DROP FOREIGN KEY FK_B0E9DC80126F525E');
        $this->addSql('DROP INDEX IDX_B0E9DC80126F525E ON daily_market_inventory_transaction');
        $this->addSql('ALTER TABLE daily_market_inventory_transaction DROP item_id');
    }
}
