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
final class Version20250427115616 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('TRUNCATE TABLE unread_pet_activity_log');

        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE unread_pet_activity_log DROP FOREIGN KEY FK_879B06F4966F7FB6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE unread_pet_activity_log DROP FOREIGN KEY FK_879B06F49F3A396D
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_879B06F4966F7FB6 ON unread_pet_activity_log
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE unread_pet_activity_log DROP pet_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE unread_pet_activity_log ADD CONSTRAINT FK_879B06F49F3A396D FOREIGN KEY (pet_activity_log_id) REFERENCES pet_activity_log_pet (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('TRUNCATE TABLE unread_pet_activity_log');

        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE unread_pet_activity_log DROP FOREIGN KEY FK_879B06F49F3A396D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE unread_pet_activity_log ADD pet_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE unread_pet_activity_log ADD CONSTRAINT FK_879B06F4966F7FB6 FOREIGN KEY (pet_id) REFERENCES pet (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE unread_pet_activity_log ADD CONSTRAINT FK_879B06F49F3A396D FOREIGN KEY (pet_activity_log_id) REFERENCES pet_activity_log (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_879B06F4966F7FB6 ON unread_pet_activity_log (pet_id)
        SQL);
    }
}
