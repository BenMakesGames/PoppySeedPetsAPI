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
final class Version20201228004927 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item ADD treasure_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E4DF05F8E FOREIGN KEY (treasure_id) REFERENCES item_treasure (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1F1B251E4DF05F8E ON item (treasure_id)');
        $this->addSql('ALTER TABLE item_treasure DROP FOREIGN KEY FK_ADC8A2B4126F525E');
        $this->addSql('DROP INDEX UNIQ_ADC8A2B4126F525E ON item_treasure');
        $this->addSql('ALTER TABLE item_treasure DROP item_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E4DF05F8E');
        $this->addSql('DROP INDEX UNIQ_1F1B251E4DF05F8E ON item');
        $this->addSql('ALTER TABLE item DROP treasure_id');
        $this->addSql('ALTER TABLE item_treasure ADD item_id INT NOT NULL');
        $this->addSql('ALTER TABLE item_treasure ADD CONSTRAINT FK_ADC8A2B4126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ADC8A2B4126F525E ON item_treasure (item_id)');
    }
}
