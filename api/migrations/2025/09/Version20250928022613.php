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

final class Version20250928022613 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE beehive DROP FOREIGN KEY FK_75878082D0A34B1A');
        $this->addSql('ALTER TABLE beehive DROP FOREIGN KEY FK_75878082F7E16CD4');
        $this->addSql('DROP INDEX IDX_75878082D0A34B1A ON beehive');
        $this->addSql('DROP INDEX IDX_75878082F7E16CD4 ON beehive');
        $this->addSql('ALTER TABLE beehive DROP requested_item_id, DROP alternate_requested_item_id, DROP interaction_power, CHANGE flower_power flower_power DOUBLE PRECISION NOT NULL, CHANGE royal_jelly_progress royal_jelly_progress DOUBLE PRECISION NOT NULL, CHANGE honeycomb_progress honeycomb_progress DOUBLE PRECISION NOT NULL, CHANGE misc_progress misc_progress DOUBLE PRECISION NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE beehive ADD requested_item_id INT NOT NULL, ADD alternate_requested_item_id INT NOT NULL, ADD interaction_power INT NOT NULL, CHANGE flower_power flower_power INT NOT NULL, CHANGE royal_jelly_progress royal_jelly_progress INT NOT NULL, CHANGE honeycomb_progress honeycomb_progress INT NOT NULL, CHANGE misc_progress misc_progress INT NOT NULL');
        $this->addSql('ALTER TABLE beehive ADD CONSTRAINT FK_75878082D0A34B1A FOREIGN KEY (requested_item_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE beehive ADD CONSTRAINT FK_75878082F7E16CD4 FOREIGN KEY (alternate_requested_item_id) REFERENCES item (id)');
        $this->addSql('CREATE INDEX IDX_75878082D0A34B1A ON beehive (requested_item_id)');
        $this->addSql('CREATE INDEX IDX_75878082F7E16CD4 ON beehive (alternate_requested_item_id)');
    }
}
