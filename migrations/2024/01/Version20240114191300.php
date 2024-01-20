<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240114191300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'remove duplicate recipes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            DELETE t1 FROM known_recipes t1
            JOIN known_recipes t2 
            WHERE 
                t1.id > t2.id AND 
                t1.user_id = t2.user_id AND 
                t1.recipe = t2.recipe;
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
