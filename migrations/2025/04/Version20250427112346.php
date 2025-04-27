<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250427112346 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            INSERT INTO pet_activity_log_pet
            (activity_log_id, pet_id, equipped_item_id, changes)
            SELECT id, pet_id, equipped_item_id, changes
            FROM pet_activity_log
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE pet_activity_log DROP FOREIGN KEY FK_198EED16966F7FB6
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_198EED16966F7FB6 ON pet_activity_log
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pet_activity_log DROP pet_id
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE pet_activity_log ADD pet_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pet_activity_log ADD CONSTRAINT FK_198EED16966F7FB6 FOREIGN KEY (pet_id) REFERENCES pet (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_198EED16966F7FB6 ON pet_activity_log (pet_id)
        SQL);
    }
}
