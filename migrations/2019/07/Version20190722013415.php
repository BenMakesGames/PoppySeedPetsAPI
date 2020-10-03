<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190722013415 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet ADD note LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE item DROP INDEX IDX_1F1B251E8F7B22CC, ADD UNIQUE INDEX UNIQ_1F1B251E8F7B22CC (tool_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE item DROP INDEX UNIQ_1F1B251E8F7B22CC, ADD INDEX IDX_1F1B251E8F7B22CC (tool_id)');
        $this->addSql('ALTER TABLE pet DROP note');
    }
}
