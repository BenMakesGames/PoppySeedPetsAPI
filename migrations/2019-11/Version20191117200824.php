<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191117200824 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE INDEX time_idx ON device_stats (time)');
        $this->addSql('CREATE INDEX type_idx ON dev_task (type)');
        $this->addSql('CREATE INDEX status_idx ON dev_task (status)');
        $this->addSql('CREATE INDEX released_on_idx ON dev_task (released_on)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX type_idx ON dev_task');
        $this->addSql('DROP INDEX status_idx ON dev_task');
        $this->addSql('DROP INDEX released_on_idx ON dev_task');
        $this->addSql('DROP INDEX time_idx ON device_stats');
    }
}
