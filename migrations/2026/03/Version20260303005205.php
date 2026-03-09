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
final class Version20260303005205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE vault (id BINARY(16) NOT NULL, open_until DATETIME NOT NULL, version INT DEFAULT 1 NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_FF304921A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE vault_inventory (id BINARY(16) NOT NULL, quantity INT NOT NULL, version INT DEFAULT 1 NOT NULL, user_id INT NOT NULL, item_id INT NOT NULL, maker_id INT DEFAULT NULL, INDEX IDX_39E42FD4A76ED395 (user_id), INDEX IDX_39E42FD4126F525E (item_id), INDEX IDX_39E42FD468DA5EC3 (maker_id), UNIQUE INDEX user_item_maker_idx (user_id, item_id, maker_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE vault ADD CONSTRAINT FK_FF304921A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE vault_inventory ADD CONSTRAINT FK_39E42FD4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE vault_inventory ADD CONSTRAINT FK_39E42FD4126F525E FOREIGN KEY (item_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE vault_inventory ADD CONSTRAINT FK_39E42FD468DA5EC3 FOREIGN KEY (maker_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vault DROP FOREIGN KEY FK_FF304921A76ED395');
        $this->addSql('ALTER TABLE vault_inventory DROP FOREIGN KEY FK_39E42FD4A76ED395');
        $this->addSql('ALTER TABLE vault_inventory DROP FOREIGN KEY FK_39E42FD4126F525E');
        $this->addSql('ALTER TABLE vault_inventory DROP FOREIGN KEY FK_39E42FD468DA5EC3');
        $this->addSql('DROP TABLE vault');
        $this->addSql('DROP TABLE vault_inventory');
    }
}
