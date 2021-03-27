<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210327155950 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item ADD is_bug TINYINT(1) NOT NULL');

        $this->addSql("
            UPDATE item SET is_bug=1 WHERE name IN (
                'Weird Beetle', 'Spider', 'Centipede', 'Cockroach', 'Line of Ants', 'Fruit Fly', 'Stink Bug', 'Moth'
            )
        ");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item DROP is_bug');
    }
}
