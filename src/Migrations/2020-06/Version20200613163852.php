<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200613163852 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet_activity_log ADD equipped_item_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pet_activity_log ADD CONSTRAINT FK_198EED161882B7CF FOREIGN KEY (equipped_item_id) REFERENCES item (id)');
        $this->addSql('CREATE INDEX IDX_198EED161882B7CF ON pet_activity_log (equipped_item_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet_activity_log DROP FOREIGN KEY FK_198EED161882B7CF');
        $this->addSql('DROP INDEX IDX_198EED161882B7CF ON pet_activity_log');
        $this->addSql('ALTER TABLE pet_activity_log DROP equipped_item_id');
    }
}
