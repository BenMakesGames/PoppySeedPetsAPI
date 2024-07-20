<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240720185000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Ace of Hearts item description fix
        $this->addSql(<<<EOSQL
        UPDATE item SET description='There\'s only one thing to do: go fish until you get a pair of \'em!' WHERE name='Ace of Hearts' LIMIT 1;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
