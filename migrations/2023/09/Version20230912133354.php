<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230912133354 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_selected_wallpaper DROP FOREIGN KEY FK_21A9913C488626AA');
        $this->addSql('ALTER TABLE user_selected_wallpaper DROP FOREIGN KEY FK_21A9913CA76ED395');
        $this->addSql('ALTER TABLE user_unlocked_wallpaper DROP FOREIGN KEY FK_6E27375A488626AA');
        $this->addSql('ALTER TABLE user_unlocked_wallpaper DROP FOREIGN KEY FK_6E27375AA76ED395');
        $this->addSql('DROP TABLE user_selected_wallpaper');
        $this->addSql('DROP TABLE user_unlocked_wallpaper');
        $this->addSql('DROP TABLE wallpaper');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_selected_wallpaper (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, wallpaper_id INT NOT NULL, color_a VARCHAR(6) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, color_b VARCHAR(6) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, UNIQUE INDEX UNIQ_21A9913CA76ED395 (user_id), INDEX IDX_21A9913C488626AA (wallpaper_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user_unlocked_wallpaper (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, wallpaper_id INT NOT NULL, unlocked_on DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6E27375AA76ED395 (user_id), INDEX IDX_6E27375A488626AA (wallpaper_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE wallpaper (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(40) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, image VARCHAR(40) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, width VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, height VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, repeat_xy VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user_selected_wallpaper ADD CONSTRAINT FK_21A9913C488626AA FOREIGN KEY (wallpaper_id) REFERENCES wallpaper (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE user_selected_wallpaper ADD CONSTRAINT FK_21A9913CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE user_unlocked_wallpaper ADD CONSTRAINT FK_6E27375A488626AA FOREIGN KEY (wallpaper_id) REFERENCES wallpaper (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE user_unlocked_wallpaper ADD CONSTRAINT FK_6E27375AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
