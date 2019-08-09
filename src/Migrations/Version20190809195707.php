<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190809195707 extends AbstractMigration
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
        $this->addSql('ALTER TABLE greenhouse_plant ADD CONSTRAINT FK_477F79E31D935652 FOREIGN KEY (plant_id) REFERENCES item_plant (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE greenhouse_plant DROP FOREIGN KEY FK_477F79E31D935652');
        $this->addSql('ALTER TABLE greenhouse_plant ADD CONSTRAINT FK_477F79E31D935652 FOREIGN KEY (plant_id) REFERENCES item (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
