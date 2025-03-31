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
final class Version20191110181242 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet DROP FOREIGN KEY FK_E4529B857DE6EAAB');
        $this->addSql('ALTER TABLE pet DROP FOREIGN KEY FK_E4529B858C6A5980');
        $this->addSql('ALTER TABLE pet DROP FOREIGN KEY FK_E4529B858F7B22CC');
        $this->addSql('ALTER TABLE pet DROP FOREIGN KEY FK_E4529B8592EA9615');
        $this->addSql('ALTER TABLE pet ADD CONSTRAINT FK_E4529B857DE6EAAB FOREIGN KEY (pregnancy_id) REFERENCES pet_baby (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pet ADD CONSTRAINT FK_E4529B858C6A5980 FOREIGN KEY (hat_id) REFERENCES inventory (id)');
        $this->addSql('ALTER TABLE pet ADD CONSTRAINT FK_E4529B858F7B22CC FOREIGN KEY (tool_id) REFERENCES inventory (id)');
        $this->addSql('ALTER TABLE pet ADD CONSTRAINT FK_E4529B8592EA9615 FOREIGN KEY (spirit_companion_id) REFERENCES spirit_companion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE hollow_earth_player DROP INDEX IDX_E7F1524B6DE487A8, ADD UNIQUE INDEX UNIQ_E7F1524B6DE487A8 (chosen_pet_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE hollow_earth_player DROP INDEX UNIQ_E7F1524B6DE487A8, ADD INDEX IDX_E7F1524B6DE487A8 (chosen_pet_id)');
        $this->addSql('ALTER TABLE pet DROP FOREIGN KEY FK_E4529B858F7B22CC');
        $this->addSql('ALTER TABLE pet DROP FOREIGN KEY FK_E4529B8592EA9615');
        $this->addSql('ALTER TABLE pet DROP FOREIGN KEY FK_E4529B857DE6EAAB');
        $this->addSql('ALTER TABLE pet DROP FOREIGN KEY FK_E4529B858C6A5980');
        $this->addSql('ALTER TABLE pet ADD CONSTRAINT FK_E4529B858F7B22CC FOREIGN KEY (tool_id) REFERENCES inventory (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE pet ADD CONSTRAINT FK_E4529B8592EA9615 FOREIGN KEY (spirit_companion_id) REFERENCES spirit_companion (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE pet ADD CONSTRAINT FK_E4529B857DE6EAAB FOREIGN KEY (pregnancy_id) REFERENCES pet_baby (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE pet ADD CONSTRAINT FK_E4529B858C6A5980 FOREIGN KEY (hat_id) REFERENCES inventory (id) ON UPDATE NO ACTION ON DELETE SET NULL');
    }
}
