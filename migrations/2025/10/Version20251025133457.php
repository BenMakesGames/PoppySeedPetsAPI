<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251025133457 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Mirrors aren\'t edible!';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE item_group SET is_craving = false WHERE name = \'Mirror\'');
        $this->addSql('DELETE FROM pet_craving WHERE food_group_id = (SELECT id FROM item_group WHERE name = \'Mirror\')');
    }

    public function down(Schema $schema): void
    {
    }
}
