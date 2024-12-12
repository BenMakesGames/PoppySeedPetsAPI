<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241212180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
        UPDATE `design_goal` SET `name` = 'Cute & Nerdy', `description` = 'I started this game because I wanted to simulate Maslow\'s Hierarchy of Needs, and in doing so accidentally created a cute & nerdy game filled with affection, love, sci-fi, magic, and mythology! That cuteness & nerdiness is what drew people to the game in the first place, so it\'s something I want to be intentional about going forward!' WHERE `design_goal`.`id` = 5; 
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
