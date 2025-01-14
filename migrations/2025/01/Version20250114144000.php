<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250114144000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('UPDATE known_recipes SET recipe="Rice Noodles (A)" WHERE recipe="Rice Noodles"');
        $this->addSql('UPDATE known_recipes SET recipe="Rice Noodles (B)" WHERE recipe="Rice Noodles B"');

        $this->addSql('UPDATE known_recipes SET recipe="Mackin Cheese (A)" WHERE recipe="Mackin Cheese"');
        $this->addSql('UPDATE known_recipes SET recipe="Mackin Cheese (B)" WHERE recipe="Mackin Cheese B"');

        $this->addSql('UPDATE known_recipes SET recipe="Coffee Jelly (A)" WHERE recipe="Coffee Jelly"');
        $this->addSql('UPDATE known_recipes SET recipe="Coffee Jelly (B)" WHERE recipe="Coffee Jelly B"');
    }

    public function down(Schema $schema): void
    {
    }
}
