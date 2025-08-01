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
final class Version20220317231523 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE greenhouse ADD butterflies_dismissed_on DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD bees_dismissed_on DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP can_use_bee_netting, DROP has_bee_netting');
        $this->addSql('ALTER TABLE greenhouse_plant ADD pollinators VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE greenhouse ADD can_use_bee_netting TINYINT(1) NOT NULL, ADD has_bee_netting TINYINT(1) NOT NULL, DROP butterflies_dismissed_on, DROP bees_dismissed_on');
        $this->addSql('ALTER TABLE greenhouse_plant DROP pollinators');
    }
}
