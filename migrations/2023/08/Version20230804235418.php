<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Enum\UnlockableFeatureEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230804235418 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO user_unlocked_feature (user_id, feature, unlocked_on) SELECT id, \'' . UnlockableFeatureEnum::HollowEarth . '\', unlocked_hollow_earth FROM user WHERE unlocked_hollow_earth IS NOT NULL');

        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP unlocked_hollow_earth');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD unlocked_hollow_earth DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
