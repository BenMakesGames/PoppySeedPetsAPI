<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201014153726 extends AbstractMigration
{
    const ALTERNATE_ITEM_IDS = [ 61, 24, 831, 115, 36, 7, 437, 536, 112, 169, 11, 719 ];

    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE beehive ADD alternate_requested_item_id INT NOT NULL');

        foreach(self::ALTERNATE_ITEM_IDS as $i=>$id)
            $this->addSql('UPDATE beehive SET alternate_requested_item_id=' . $id . ' WHERE MOD(id, ' . count(self::ALTERNATE_ITEM_IDS) . ') = ' . $i);

        $this->addSql('ALTER TABLE beehive ADD CONSTRAINT FK_75878082F7E16CD4 FOREIGN KEY (alternate_requested_item_id) REFERENCES item (id)');
        $this->addSql('CREATE INDEX IDX_75878082F7E16CD4 ON beehive (alternate_requested_item_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE beehive DROP FOREIGN KEY FK_75878082F7E16CD4');
        $this->addSql('DROP INDEX IDX_75878082F7E16CD4 ON beehive');
        $this->addSql('ALTER TABLE beehive DROP alternate_requested_item_id');
    }
}
