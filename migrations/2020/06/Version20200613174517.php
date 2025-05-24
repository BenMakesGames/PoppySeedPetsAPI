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

use App\Enum\RelationshipEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200613174517 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet_relationship ADD energy INT NOT NULL');

        $this->addSql('UPDATE pet_relationship SET energy=energy+100 WHERE relationship_goal=\'' . RelationshipEnum::Mate . '\'');
        $this->addSql('UPDATE pet_relationship SET energy=energy+80 WHERE relationship_goal=\'' . RelationshipEnum::FWB . '\'');
        $this->addSql('UPDATE pet_relationship SET energy=energy+70 WHERE relationship_goal=\'' . RelationshipEnum::BFF . '\'');
        $this->addSql('UPDATE pet_relationship SET energy=energy+50 WHERE relationship_goal=\'' . RelationshipEnum::Friend . '\'');
        $this->addSql('UPDATE pet_relationship SET energy=energy+20 WHERE relationship_goal=\'' . RelationshipEnum::FriendlyRival . '\'');

        $this->addSql('UPDATE pet_relationship SET energy=energy+30 WHERE current_relationship=\'' . RelationshipEnum::Mate . '\'');
        $this->addSql('UPDATE pet_relationship SET energy=energy+20 WHERE current_relationship=\'' . RelationshipEnum::FWB . '\'');
        $this->addSql('UPDATE pet_relationship SET energy=energy+18 WHERE current_relationship=\'' . RelationshipEnum::BFF . '\'');
        $this->addSql('UPDATE pet_relationship SET energy=energy+12 WHERE current_relationship=\'' . RelationshipEnum::Friend . '\'');
        $this->addSql('UPDATE pet_relationship SET energy=energy+5 WHERE current_relationship=\'' . RelationshipEnum::FriendlyRival . '\'');

        $this->addSql('UPDATE pet_relationship SET energy=energy+FLOOR(RAND() * 30)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet_relationship DROP energy');
    }
}
