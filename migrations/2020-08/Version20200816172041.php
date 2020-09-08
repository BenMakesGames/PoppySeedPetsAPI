<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200816172041 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX name_idx ON user_quest');

        $this->addSql('
            DELETE FROM user_quest WHERE id NOT IN (
                SELECT id FROM (
                	SELECT MIN(id) AS id FROM `user_quest`
                	GROUP BY(CONCAT(user_id, \'-\', name))
                ) AS toDelete
            )
        ');

        $this->addSql('CREATE UNIQUE INDEX user_id_name_idx ON user_quest (user_id, name)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX user_id_name_idx ON user_quest');
        $this->addSql('CREATE INDEX name_idx ON user_quest (name)');
    }
}
