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
final class Version20200201204025 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet_species ADD sheds_id INT NOT NULL');

        // fluff
        $this->addSql('UPDATE pet_species SET sheds_id=34 WHERE id IN (1, 2, 5, 9, 11, 15, 19, 21, 22, 23, 25, 27, 29)');

        // scales
        $this->addSql('UPDATE pet_species SET sheds_id=35 WHERE id IN (8, 14, 32, 34, 36)');

        // feathers
        $this->addSql('UPDATE pet_species SET sheds_id=144 WHERE id IN (4, 6, 12, 17, 18, 26, 30, 31)');

        // sugar (Cotton Candy and Jelephant)
        $this->addSql('UPDATE pet_species SET sheds_id=12 WHERE id IN (3, 35)');

        // yellow dye (Rainbow Dolphin, only)
        $this->addSql('UPDATE pet_species SET sheds_id=94 WHERE id IN (20)');

        // glass (Hexastrephidae, only)
        $this->addSql('UPDATE pet_species SET sheds_id=142 WHERE id IN (24)');

        // tentacle (Tentacat, only)
        $this->addSql('UPDATE pet_species SET sheds_id=253 WHERE id IN (28)');

        // useless fizz
        $this->addSql('UPDATE pet_species SET sheds_id=301 WHERE id IN (16)');

        // pointer
        $this->addSql('UPDATE pet_species SET sheds_id=188 WHERE id IN (39)');

        // quintessence
        $this->addSql('UPDATE pet_species SET sheds_id=108 WHERE id IN (7, 13, 37)');

        // chantarelle
        $this->addSql('UPDATE pet_species SET sheds_id=61 WHERE id IN (10)');

        // chantarelle
        $this->addSql('UPDATE pet_species SET sheds_id=161 WHERE id IN (38)');

        // tomato
        $this->addSql('UPDATE pet_species SET sheds_id=133 WHERE id IN (33)');

        $this->addSql('ALTER TABLE pet_species ADD CONSTRAINT FK_BB4177F42BA517AC FOREIGN KEY (sheds_id) REFERENCES item (id)');
        $this->addSql('CREATE INDEX IDX_BB4177F42BA517AC ON pet_species (sheds_id)');

        $this->addSql("INSERT INTO merit (name, description) VALUES
('Burps Moths', 'When fed, %pet.name% may burp up a Moth...'),
('Naïve', '%pet.name% believes in friendship! And always agrees to relationship changes...'),
('Gourmand', '%pet.name% has a slightly-larger stomach.'),
('Spectral', '%pet.name% is... not entirely opaque...'),
('Prehensile Tongue', 'Remember all those times you thought \"man, I wish I had a third arm\"? %pet.name% has never had to think that.'),
('Lolligovore', '%pet.name% really likes eating Tentacles.'),
('Hyperchromatic', '%pet.name%\\'s colors shift and change over time, sometimes abruptly.'),
('Dreamwalker', '%pet.name% can pull objects out of their dreams.'),
('Extroverted', '%pet.name% will join more groups than most.'),
('Sheds', '%pet.name% sheds.'),
('Darkvision', '%pet.name% can see in the dark without the help of a tool.');");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pet_species DROP FOREIGN KEY FK_BB4177F42BA517AC');
        $this->addSql('DROP INDEX IDX_BB4177F42BA517AC ON pet_species');
        $this->addSql('ALTER TABLE pet_species DROP sheds_id');
    }
}
