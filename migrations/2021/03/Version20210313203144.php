<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210313203144 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX name_idx ON pet_species');
        $this->addSql('ALTER TABLE pet_species ADD name_sort VARCHAR(40) NOT NULL');
        $this->addSql('CREATE INDEX name_sort_idx ON pet_species (name_sort)');

        $this->addSql('UPDATE pet_species SET name_sort=name');
        $this->addSql('UPDATE pet_species SET name_sort="Lost" WHERE image="elemental/tig\'s"');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX name_sort_idx ON pet_species');
        $this->addSql('ALTER TABLE pet_species DROP name_sort');
        $this->addSql('CREATE INDEX name_idx ON pet_species (name)');
    }
}
