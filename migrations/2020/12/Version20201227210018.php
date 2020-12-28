<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201227210018 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dragon (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, name VARCHAR(20) DEFAULT NULL, food INT NOT NULL, color_a VARCHAR(6) DEFAULT NULL, color_b VARCHAR(6) DEFAULT NULL, is_adult TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_27D829B47E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dragon ADD CONSTRAINT FK_27D829B47E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');

        $this->addSql('
            INSERT INTO dragon (owner_id, name, food, color_a, color_b, is_adult)
            SELECT
                f.user_id AS owner_id,
                f.whelp_name AS name,
                f.whelp_food AS food,
                f.whelp_color_a AS color_a,
                f.whelp_color_b AS color_b,
                0 AS is_adult
            FROM fireplace AS f
            WHERE f.whelp_name IS NOT NULL
        ');

        $this->addSql('ALTER TABLE fireplace DROP whelp_name, DROP whelp_food, DROP whelp_color_a, DROP whelp_color_b');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE dragon');
        $this->addSql('ALTER TABLE fireplace ADD whelp_name VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD whelp_food INT NOT NULL, ADD whelp_color_a VARCHAR(6) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD whelp_color_b VARCHAR(6) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
