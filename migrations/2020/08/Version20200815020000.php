<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200815020000 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // add plant yields
        $this->addSql('
            INSERT INTO `plant_yield` (`id`, `plant_id`, `min`, `max`) VALUES
            (1, 1, 3, 5),
            (2, 2, 3, 5),
            (3, 3, 3, 5),
            (4, 4, 4, 6),
            (5, 5, 4, 5),
            (6, 6, 3, 5),
            (7, 7, 4, 6),
            (8, 8, 1, 3),
            (9, 9, 3, 5),
            (10, 10, 2, 4),
            (11, 11, 4, 6),
            (12, 12, 4, 6),
            (13, 13, 3, 5),
            (14, 14, 2, 4),
            (15, 15, 3, 5),
            (17, 17, 3, 5),
            (18, 18, 1, 3),
            (19, 19, 3, 5),
            (21, 21, 3, 5),
            (22, 22, 3, 4),
            (23, 23, 3, 5),
            (24, 24, 3, 4),
            (25, 25, 2, 4),
            (26, 26, 3, 5),
            (27, 27, 3, 3),
            (28, 28, 3, 5),
            (29, 29, 3, 5),
            (30, 30, 3, 5),
            (31, 31, 2, 4),
            (32, 32, 2, 4),
            (33, 33, 4, 6),
            (34, 34, 4, 5),
            (64, 10, 1, 1),
            (65, 16, 2, 3),
            (66, 31, 1, 1),
            (67, 25, 1, 1),
            (68, 8, 1, 1),
            (69, 18, 1, 1),
            (70, 32, 1, 1),
            (71, 30, 1, 1);
        ');

        $this->addSql('
            INSERT INTO `plant_yield_item` (`id`, `plant_yield_id`, `item_id`, `percent_chance`) VALUES
            (1, 1, 15, 100),
            (2, 2, 1, 100),
            (3, 3, 2, 100),
            (4, 4, 5, 100),
            (5, 5, 3, 100),
            (6, 6, 17, 100),
            (7, 7, 4, 100),
            (8, 8, 11, 100),
            (9, 9, 42, 100),
            (10, 10, 13, 100),
            (11, 11, 133, 100),
            (12, 12, 248, 100),
            (13, 13, 24, 100),
            (14, 14, 7, 100),
            (15, 15, 130, 100),
            (17, 17, 363, 100),
            (18, 18, 437, 100),
            (19, 19, 32, 100),
            (21, 21, 511, 100),
            (22, 22, 477, 100),
            (23, 23, 523, 100),
            (24, 24, 550, 100),
            (25, 25, 23, 100),
            (26, 26, 169, 100),
            (27, 27, 177, 100),
            (28, 28, 131, 100),
            (29, 29, 132, 100),
            (30, 30, 356, 100),
            (31, 31, 33, 100),
            (32, 32, 253, 100),
            (33, 33, 140, 100),
            (34, 34, 8, 100),
            (64, 64, 34, 17),
            (65, 64, 239, 17),
            (66, 64, 65, 8),
            (67, 64, 87, 25),
            (68, 64, 13, 33),
            (69, 65, 10, 5),
            (70, 65, 356, 5),
            (71, 65, 170, 5),
            (72, 65, 65, 5),
            (73, 65, 6, 5),
            (74, 65, 8, 5),
            (75, 65, 42, 5),
            (76, 65, 14, 5),
            (77, 65, 34, 5),
            (78, 65, 41, 5),
            (79, 65, 4, 5),
            (80, 65, 262, 5),
            (81, 65, 1, 5),
            (82, 65, 121, 5),
            (83, 65, 196, 5),
            (84, 65, 113, 5),
            (85, 65, 2, 5),
            (86, 65, 12, 5),
            (87, 65, 253, 5),
            (88, 65, 38, 5),
            (100, 66, 33, 80),
            (101, 66, 35, 15),
            (102, 66, 41, 5),
            (103, 67, 23, 80),
            (104, 67, 325, 20),
            (105, 68, 11, 90),
            (106, 68, 36, 9),
            (107, 68, 37, 1),
            (108, 69, 437, 80),
            (109, 69, 354, 20),
            (110, 70, 253, 80),
            (111, 70, 156, 8),
            (112, 70, 230, 8),
            (113, 70, 324, 4),
            (114, 71, 356, 80),
            (115, 71, 95, 20);
        ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

    }
}
