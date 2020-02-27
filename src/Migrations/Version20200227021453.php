<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200227021453 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE item_tool ADD when_gather_id INT DEFAULT NULL, ADD when_gather_also_gather_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE item_tool ADD CONSTRAINT FK_E8C37A2A2182ABDD FOREIGN KEY (when_gather_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE item_tool ADD CONSTRAINT FK_E8C37A2A7C7A1896 FOREIGN KEY (when_gather_also_gather_id) REFERENCES item (id)');
        $this->addSql('CREATE INDEX IDX_E8C37A2A2182ABDD ON item_tool (when_gather_id)');
        $this->addSql('CREATE INDEX IDX_E8C37A2A7C7A1896 ON item_tool (when_gather_also_gather_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE item_tool DROP FOREIGN KEY FK_E8C37A2A2182ABDD');
        $this->addSql('ALTER TABLE item_tool DROP FOREIGN KEY FK_E8C37A2A7C7A1896');
        $this->addSql('DROP INDEX IDX_E8C37A2A2182ABDD ON item_tool');
        $this->addSql('DROP INDEX IDX_E8C37A2A7C7A1896 ON item_tool');
        $this->addSql('ALTER TABLE item_tool DROP when_gather_id, DROP when_gather_also_gather_id');
    }
}
