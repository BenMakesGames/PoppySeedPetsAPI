<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201120220433 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item_food ADD leftovers_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE item_food ADD CONSTRAINT FK_1C086D0CBB6A5262 FOREIGN KEY (leftovers_id) REFERENCES item (id)');
        $this->addSql('CREATE INDEX IDX_1C086D0CBB6A5262 ON item_food (leftovers_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item_food DROP FOREIGN KEY FK_1C086D0CBB6A5262');
        $this->addSql('DROP INDEX IDX_1C086D0CBB6A5262 ON item_food');
        $this->addSql('ALTER TABLE item_food DROP leftovers_id');
    }
}
