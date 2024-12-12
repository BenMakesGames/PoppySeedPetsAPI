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
        UPDATE `design_goal` SET `name` = 'Cute & Nerdy', `description` = 'I\'m a nerdy person; my love of science will always show itself in anything I make. Poppy Seed Pets is also a cute game about taking care of pets! I think Maslow\'s Hierarchy of Needs has set some good groundwork for merging cuteness & nerdiness, and I want to keep that blend going!' WHERE `design_goal`.`id` = 5; 
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
