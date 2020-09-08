<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Enum\LocationEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200406180000 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('
            UPDATE inventory AS i
            LEFT JOIN pet AS p ON p.tool_id=i.id OR p.hat_id=i.id
            SET i.location=' . LocationEnum::WARDROBE . '
            WHERE p.id IS NOT NULL
        ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('UPDATE inventory SET location=' . LocationEnum::HOME . ' WHERE location=' . LocationEnum::WARDROBE);
    }
}
