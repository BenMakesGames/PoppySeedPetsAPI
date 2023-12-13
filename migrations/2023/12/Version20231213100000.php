<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231213100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'correct Recipes Learned by Cooking Buddy stats';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE user_stats SET value=(SELECT COUNT(*) FROM known_recipes WHERE known_recipes.user_id=user_stats.user_id) WHERE user_stats.stat='Recipes Learned by Cooking Buddy'");
    }

    public function down(Schema $schema): void
    {
    }
}
