<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200611223759 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE daily_market_inventory_transaction DROP FOREIGN KEY FK_B0E9DC80A76ED395');
        $this->addSql('DROP INDEX IDX_B0E9DC80A76ED395 ON daily_market_inventory_transaction');
        $this->addSql('ALTER TABLE daily_market_inventory_transaction CHANGE user_id buyer_id INT NOT NULL');
        $this->addSql('ALTER TABLE daily_market_inventory_transaction ADD CONSTRAINT FK_B0E9DC806C755722 FOREIGN KEY (buyer_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_B0E9DC806C755722 ON daily_market_inventory_transaction (buyer_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE daily_market_inventory_transaction DROP FOREIGN KEY FK_B0E9DC806C755722');
        $this->addSql('DROP INDEX IDX_B0E9DC806C755722 ON daily_market_inventory_transaction');
        $this->addSql('ALTER TABLE daily_market_inventory_transaction CHANGE buyer_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE daily_market_inventory_transaction ADD CONSTRAINT FK_B0E9DC80A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_B0E9DC80A76ED395 ON daily_market_inventory_transaction (user_id)');
    }
}
