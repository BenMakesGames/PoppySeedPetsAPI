<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200921213134 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('UPDATE user_stats SET stat=\'Recipes Learned by Cooking Buddy\' WHERE stat=\'Reciped Learned by Cooking Buddy\'');
        $this->addSql('UPDATE user_stats SET stat=\'Items Recycled\' WHERE stat=\'Items Thrown Away\'');
    }

    public function down(Schema $schema) : void
    {
    }
}
