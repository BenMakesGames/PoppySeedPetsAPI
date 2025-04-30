<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250430015239 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE item_tool ADD physics INT NOT NULL, ADD electronics INT NOT NULL, ADD hacking INT NOT NULL, ADD explore_umbra INT NOT NULL, ADD magic_binding INT NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE item_tool DROP physics, DROP electronics, DROP hacking, DROP explore_umbra, DROP magic_binding
        SQL);
    }
}
