<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231213010000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Delete old known oniony smashed potatoes "known recipes", and update stats accordingly.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("DELETE FROM known_recipes WHERE recipe LIKE 'Oniony Smashed Potatoes%'");
        $this->addSql("UPDATE user_stats SET user_stats.value=(SELECT COUNT(id) FROM known_recipes WHERE known_recipes.user_id=user_stats.id) WHERE user_stats.stat='Recipes Learned by Cooking Buddy';");
    }

    public function down(Schema $schema): void
    {
    }
}
