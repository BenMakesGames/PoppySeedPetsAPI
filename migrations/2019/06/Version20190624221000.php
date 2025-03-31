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
final class Version20190624221000 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pet_species (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(40) NOT NULL, image VARCHAR(40) NOT NULL, description LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pet ADD species_id INT NOT NULL');

        $this->addSql("
            INSERT INTO `pet_species` (`id`, `name`, `image`, `description`) VALUES
            (1, 'Desikh', 'monotreme/desikh', 'An unusually-intelligent monotreme.Scientists have yet to determine the function of its arm-like appendage, though there is evidence to suggest that it serves some purpose in Desikh mating rituals.'),
            (2, 'Roundish', 'mammal/roundish', 'In nature, this round creature is often confused for a vase. It uses this disguise to avoid detection by predators, which are largely unaware of the existence of vases, and so pay it no attention whatsoever.'),
            (3, 'Cotton Candy', 'elemental/cotton-candy', 'Animated cotton candy. How the first one came to be is unknown, but there is evidence that the species has been on Earth for tens of thousands of years.'),
            (4, 'Chickie', 'bird/chickie', 'This bird perpetually has the appearance of a young chick, though it is a separate species from the common chicken.'),
            (5, 'Mole', 'mammal/mole', 'Just a run-of-the-mill mole.'),
            (6, 'Oft', 'bird/odd-flying-thing', 'It\'s said that, for a while, no one had a name for this rare creature, and that it came to be colloquially known as an \\\"odd flying thing\\\"; that it went so long without an \\\"official\\\" name, that the unofficial one stuck, but was often abbreviated as \\\"OFT\\\" in publications, to save on typing.That\'s what\'s said, anyway.'),
            (7, 'Triangle', 'elemental/triangle', 'A being of pure, euclidean triangleness. Some people say that this creature\'s existence is evidence in support of simulationism, but this view is not widely accepted.'),
            (8, 'Fish with Legs', 'fish/ba-ha', 'It isn\'t the first time this has happened in nature. It probably won\'t be the last.'),
            (9, 'Mousie', 'mammal/much-cuter-mousie', 'Though bearing a striking resemblance to the common _Mus musculus_, the two species rarely produce offspring successfully; when they do, it\'s always infertile.You know, like that whole horse/donkey/mule thing.'),
            (10, 'Mushroom', 'fungus/mushroom', 'Fungi have been on the earth for so long, it\'s no surprise that at least _one_ species gained sentience...');
        ");

        $this->addSql("ALTER TABLE `pet_species` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE pet_species');
        $this->addSql('ALTER TABLE pet DROP species_id');
    }
}
