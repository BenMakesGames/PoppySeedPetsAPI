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
final class Version20201201050545 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fireplace ADD stocking_appearance VARCHAR(20) NOT NULL, ADD stocking_color_a VARCHAR(6) NOT NULL, ADD stocking_color_b VARCHAR(6) NOT NULL');

        $this->addSql('
            UPDATE fireplace
            SET
                stocking_color_a=LPAD(CONV(ROUND(RAND()*16777215),10,16),6,0),
                stocking_color_b=LPAD(CONV(ROUND(RAND()*16777215),10,16),6,0),
                stocking_appearance=IF(RAND() < 0.5, \'tasseled\', \'fluffed\')
        ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fireplace DROP stocking_appearance, DROP stocking_color_a, DROP stocking_color_b');
    }
}
