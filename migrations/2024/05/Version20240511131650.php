<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240511131650 extends AbstractMigration
{
    public const IconUpdates = [
        [ 'id' => '', 'icon' => '' ],
    ];

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pet_activity_log_tag CHANGE emoji emoji VARCHAR(100) NOT NULL');

        foreach(self::IconUpdates as $update)
        {
            $this->addSql(<<<EOSQL
            UPDATE pet_activity_log_tag
            SET emoji = "{$update['icon']}"
            WHERE id = {$update['id']};
            EOSQL);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pet_activity_log_tag CHANGE emoji emoji VARCHAR(12) NOT NULL');
    }
}
