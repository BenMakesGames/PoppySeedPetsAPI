<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191226211455 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE beehive ADD requested_item_id INT NOT NULL, ADD flower_power INT NOT NULL, ADD royal_jelly_progress INT NOT NULL, ADD honeycomb_progress INT NOT NULL, ADD misc_progress INT NOT NULL, ADD interaction_power INT NOT NULL');
        $this->addSql('ALTER TABLE beehive ADD CONSTRAINT FK_75878082D0A34B1A FOREIGN KEY (requested_item_id) REFERENCES item (id)');
        $this->addSql('CREATE INDEX IDX_75878082D0A34B1A ON beehive (requested_item_id)');
        $this->addSql('CREATE INDEX workers_idx ON beehive (workers)');
        $this->addSql('CREATE INDEX flower_power_idx ON beehive (flower_power)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE beehive DROP FOREIGN KEY FK_75878082D0A34B1A');
        $this->addSql('DROP INDEX IDX_75878082D0A34B1A ON beehive');
        $this->addSql('DROP INDEX workers_idx ON beehive');
        $this->addSql('DROP INDEX flower_power_idx ON beehive');
        $this->addSql('ALTER TABLE beehive DROP requested_item_id, DROP flower_power, DROP royal_jelly_progress, DROP honeycomb_progress, DROP misc_progress, DROP interaction_power');
    }
}
