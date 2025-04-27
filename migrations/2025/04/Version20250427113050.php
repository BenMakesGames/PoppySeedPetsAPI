<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250427113050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE pet_activity_log DROP FOREIGN KEY FK_198EED161882B7CF
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_198EED161882B7CF ON pet_activity_log
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pet_activity_log DROP equipped_item_id, DROP changes
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE pet_activity_log ADD equipped_item_id INT DEFAULT NULL, ADD changes LONGTEXT DEFAULT NULL COMMENT '(DC2Type:object)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pet_activity_log ADD CONSTRAINT FK_198EED161882B7CF FOREIGN KEY (equipped_item_id) REFERENCES item (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_198EED161882B7CF ON pet_activity_log (equipped_item_id)
        SQL);
    }
}
