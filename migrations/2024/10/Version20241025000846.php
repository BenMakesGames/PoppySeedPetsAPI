<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241025000846 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add index to pet_relationship.current_relationship';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX current_relationship_idx ON pet_relationship (current_relationship)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX current_relationship_idx ON pet_relationship');
    }
}
