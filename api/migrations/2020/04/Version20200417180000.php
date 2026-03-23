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
final class Version20200417180000 extends AbstractMigration
{
    private const RECYCLE_VALUES = [
        28 => [ 510 ],
        25 => [ 509 ],
        20 => [ 187 ],
        19 => [ 556 ],
        18 => [ 504 ],
        17 => [ 443 ],
        16 => [ 406, 420, 487 ],
        15 => [ 636 , 442, 461 ],
        14 => [ 260, 466, 485, 517 ],
        13 => [ 258, 259, 383, 465 ],
        12 => [ 403, 404, 419 ],
        11 => [ 208 ],
        10 => [ 391, 395, 405, 553, 554 ],
        9 => [ 233, 242, 340, 450, 490, 497, 498, 555 ],
        8 => [ 154, 207, 237, 257, 292, 390, 418, 445, 448, 449, 459, 471, 472, 484, 492, 518 ],
        7 => [ 186, 384, 401, 402, 473, 474, 481, 486, 541, 542, 552 ],
        6 => [ 153, 224, 227, 234, 235, 236, 285, 286, 332, 372, 387, 388, 483, 507, 513, 514, 516 ],
        5 => [ 114, 118, 152, 193, 241, 276, 334, 361, 364, 441, 460, 467, 503, 522 ],
        4 => [ 77, 197, 228, 229, 247, 267, 272, 284, 377, 386, 407, 411, 451, 480, 491, 501, 551 ],
        3 => [ 69, 78, 123, 124, 125, 126, 127, 128, 129, 139, 143, 192, 238, 264, 283, 344, 367, 400, 452, 453, 462 ],
        2 => [ 101, 102, 103, 106, 107, 155, 209, 256, 287, 328, 336, 365, 410, 463 ],
        1 => [ 68, 76, 89, 91, 93, 94, 95, 142, 204, 265, 266, 277, 278, 279, 330, 331, 360, 366, 470, 479, 502, 639 ]
    ];

    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        foreach(self::RECYCLE_VALUES as $value=>$itemIds)
            $this->addSql('UPDATE item SET recycle_value=' . $value . ' WHERE id IN (' . implode(', ', $itemIds) . ') LIMIT ' . count($itemIds));
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
