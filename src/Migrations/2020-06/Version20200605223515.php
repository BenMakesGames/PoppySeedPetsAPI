<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200605223515 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_following DROP FOREIGN KEY FK_30BCB75C6A5458E8');
        $this->addSql('DROP INDEX IDX_715F00076A5458E8 ON user_following');
        $this->addSql('ALTER TABLE user_following CHANGE friend_id following_id INT NOT NULL');
        $this->addSql('ALTER TABLE user_following ADD CONSTRAINT FK_715F00071816E3A3 FOREIGN KEY (following_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_715F00071816E3A3 ON user_following (following_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_following DROP FOREIGN KEY FK_715F00071816E3A3');
        $this->addSql('DROP INDEX IDX_715F00071816E3A3 ON user_following');
        $this->addSql('ALTER TABLE user_following CHANGE following_id friend_id INT NOT NULL');
        $this->addSql('ALTER TABLE user_following ADD CONSTRAINT FK_30BCB75C6A5458E8 FOREIGN KEY (friend_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_715F00076A5458E8 ON user_following (friend_id)');
    }
}
