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
final class Version20250114150931 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
        INSERT INTO `pet_activity_log_tag` (`id`, `title`, `color`, `emoji`) VALUES (94, 'Badge!', 'E28024', 'fa-solid fa-badge-check');
        EOSQL);

        $this->addSql('CREATE TABLE pet_badge (id INT AUTO_INCREMENT NOT NULL, pet_id INT NOT NULL, badge VARCHAR(40) NOT NULL, date_acquired DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_538C88BE966F7FB6 (pet_id), INDEX badge_idx (badge), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pet_badge ADD CONSTRAINT FK_538C88BE966F7FB6 FOREIGN KEY (pet_id) REFERENCES pet (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pet_badge DROP FOREIGN KEY FK_538C88BE966F7FB6');
        $this->addSql('DROP TABLE pet_badge');
    }
}
