<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250504153355 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Actionable Field Guide entries!';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE field_guide_entry ADD action_requirements JSON DEFAULT NULL
        SQL);

        $this->addSql(<<<EOSQL
            UPDATE field_guide_entry SET action_requirements='[]' WHERE name IN ('Huge Toad', 'Argopelter', 'Onion Boy');
        EOSQL);

        $this->addSql(<<<EOSQL
            UPDATE field_guide_entry SET action_requirements='["protection from heat"]' WHERE name='Île Volcan';
        EOSQL);

        $this->addSql(<<<EOSQL
            UPDATE field_guide_entry SET action_requirements='["access to the Umbra"]' WHERE name IN ('Abandondero', 'Cosmic Goat');
        EOSQL);

        $this->addSql(<<<EOSQL
            UPDATE field_guide_entry SET action_requirements='["access to the deep sea"]' WHERE name IN ('Shipwrecked Fleet', 'Whales');
        EOSQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE field_guide_entry DROP action_requirements
        SQL);
    }
}
