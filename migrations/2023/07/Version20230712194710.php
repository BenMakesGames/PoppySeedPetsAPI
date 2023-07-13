<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230712194710 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_link (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, category VARCHAR(40) NOT NULL, website VARCHAR(40) NOT NULL, name_or_id VARCHAR(100) NOT NULL, INDEX IDX_4C2DD538A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_selected_wallpaper (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, wallpaper_id INT NOT NULL, color_a VARCHAR(6) NOT NULL, color_b VARCHAR(6) NOT NULL, UNIQUE INDEX UNIQ_21A9913CA76ED395 (user_id), INDEX IDX_21A9913C488626AA (wallpaper_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_unlocked_wallpaper (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, wallpaper_id INT NOT NULL, unlocked_on DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6E27375AA76ED395 (user_id), INDEX IDX_6E27375A488626AA (wallpaper_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE wallpaper (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(40) NOT NULL, image VARCHAR(40) NOT NULL, width VARCHAR(20) NOT NULL, height VARCHAR(20) NOT NULL, repeat_xy VARCHAR(10) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_link ADD CONSTRAINT FK_4C2DD538A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_selected_wallpaper ADD CONSTRAINT FK_21A9913CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_selected_wallpaper ADD CONSTRAINT FK_21A9913C488626AA FOREIGN KEY (wallpaper_id) REFERENCES wallpaper (id)');
        $this->addSql('ALTER TABLE user_unlocked_wallpaper ADD CONSTRAINT FK_6E27375AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_unlocked_wallpaper ADD CONSTRAINT FK_6E27375A488626AA FOREIGN KEY (wallpaper_id) REFERENCES wallpaper (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_link DROP FOREIGN KEY FK_4C2DD538A76ED395');
        $this->addSql('ALTER TABLE user_selected_wallpaper DROP FOREIGN KEY FK_21A9913CA76ED395');
        $this->addSql('ALTER TABLE user_selected_wallpaper DROP FOREIGN KEY FK_21A9913C488626AA');
        $this->addSql('ALTER TABLE user_unlocked_wallpaper DROP FOREIGN KEY FK_6E27375AA76ED395');
        $this->addSql('ALTER TABLE user_unlocked_wallpaper DROP FOREIGN KEY FK_6E27375A488626AA');
        $this->addSql('DROP TABLE user_link');
        $this->addSql('DROP TABLE user_selected_wallpaper');
        $this->addSql('DROP TABLE user_unlocked_wallpaper');
        $this->addSql('DROP TABLE wallpaper');
    }
}
