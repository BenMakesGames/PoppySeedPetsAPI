<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231212203000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Correct Masaton->Mastodon typo.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE `user_link` SET `website` = 'Mastodon' WHERE `website` = 'Mastadon';");
    }

    public function down(Schema $schema): void
    {
    }
}
