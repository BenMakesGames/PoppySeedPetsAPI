<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200605222008 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_following RENAME INDEX idx_30bcb75ca76ed395 TO IDX_715F0007A76ED395');
        $this->addSql('ALTER TABLE user_following RENAME INDEX idx_30bcb75c6a5458e8 TO IDX_715F00076A5458E8');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_following RENAME INDEX idx_715f00076a5458e8 TO IDX_30BCB75C6A5458E8');
        $this->addSql('ALTER TABLE user_following RENAME INDEX idx_715f0007a76ed395 TO IDX_30BCB75CA76ED395');
    }
}
