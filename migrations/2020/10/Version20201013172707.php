<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201013172707 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE greenhouse_plant ADD ordinal SMALLINT NOT NULL');
        $this->addSql('
            UPDATE `greenhouse_plant` AS gp
            SET gp.ordinal = 1 + (
                SELECT COUNT(g.id)
                FROM (SELECT id,owner_id FROM greenhouse_plant) AS g
                WHERE
                    g.id < gp.id AND
                    g.owner_id = gp.owner_id
            )
        ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE greenhouse_plant DROP ordinal');
    }
}
