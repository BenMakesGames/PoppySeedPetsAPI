<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200612030546 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE daily_market_inventory_transaction DROP FOREIGN KEY FK_B0E9DC809EEA759');
        $this->addSql('DROP INDEX IDX_B0E9DC809EEA759 ON daily_market_inventory_transaction');
        $this->addSql('ALTER TABLE daily_market_inventory_transaction CHANGE inventory_id inventory INT NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE daily_market_inventory_transaction CHANGE inventory inventory_id INT NOT NULL');
        $this->addSql('ALTER TABLE daily_market_inventory_transaction ADD CONSTRAINT FK_B0E9DC809EEA759 FOREIGN KEY (inventory_id) REFERENCES inventory (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_B0E9DC809EEA759 ON daily_market_inventory_transaction (inventory_id)');
    }
}
