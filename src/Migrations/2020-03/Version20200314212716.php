<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200314212716 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE item_food DROP FOREIGN KEY FK_1C086D0CE3F4BA56');
        $this->addSql('DROP INDEX IDX_1C086D0CE3F4BA56 ON item_food');
        $this->addSql('ALTER TABLE item_food DROP bonus_item_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE item_food ADD bonus_item_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE item_food ADD CONSTRAINT FK_1C086D0CE3F4BA56 FOREIGN KEY (bonus_item_id) REFERENCES item (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_1C086D0CE3F4BA56 ON item_food (bonus_item_id)');
    }
}
