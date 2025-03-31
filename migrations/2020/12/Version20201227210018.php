<?php

declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


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
