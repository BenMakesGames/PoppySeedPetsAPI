<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231006193834 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE known_recipes DROP FOREIGN KEY FK_D636E6E159D8A214');
        $this->addSql('DROP INDEX IDX_D636E6E159D8A214 ON known_recipes');
        $this->addSql('ALTER TABLE known_recipes ADD recipe VARCHAR(45) NOT NULL');

        $this->addSql('UPDATE known_recipes SET known_recipes.recipe=(SELECT recipe.name FROM recipe WHERE recipe.id=known_recipes.recipe_id)');

        $this->addSql('ALTER TABLE known_recipes DROP recipe_id');
        $this->addSql('DROP TABLE recipe');
    }

    public function down(Schema $schema): void
    {
        throw new \Exception('No :P');
    }
}
