<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230816095500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE item SET description='The largest-ever recorded Pineapple weighed an unbelievable 7 pounds and 11 inches!' WHERE name='Pineapple';");
    }

    public function down(Schema $schema): void
    {
    }
}
