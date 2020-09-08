<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200814233723 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE greenhouse_plant DROP FOREIGN KEY FK_477F79E31D935652');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E1D935652');

        $this->addSql('ALTER TABLE item_plant RENAME plant');

        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E1D935652 FOREIGN KEY (plant_id) REFERENCES plant (id)');
        $this->addSql('ALTER TABLE greenhouse_plant ADD CONSTRAINT FK_477F79E31D935652 FOREIGN KEY (plant_id) REFERENCES plant (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E1D935652');
        $this->addSql('ALTER TABLE greenhouse_plant DROP FOREIGN KEY FK_477F79E31D935652');

        $this->addSql('ALTER TABLE plant RENAME item_plant');

        $this->addSql('ALTER TABLE greenhouse_plant ADD CONSTRAINT FK_477F79E31D935652 FOREIGN KEY (plant_id) REFERENCES item_plant (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E1D935652 FOREIGN KEY (plant_id) REFERENCES item_plant (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
