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

final class Version20240927173700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Yellowy Lime
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'So, what, _is_ it a lime, or is it a lemon? Or a yuzu!? EXPLAIN YOURSELF, FRUIT!' WHERE `item`.`id` = 1205;
        EOSQL);

        // Naner
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'A common fruit to find a tropical island, really. Nothing to get too fussed about one way or another.' WHERE `item`.`id` = 4;
        EOSQL);

        // Egg
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'What kind of bird might this egg have grown up to be? Who knows! It\'s like nature\'s original loot box, and you\'re just gonna\' _turn it into a flan_, or some odd business!' WHERE `item`.`id` = 14;
        EOSQL);

        // Blueberry Wine
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'The label warns: \"Contents may cause drinker to temporarily perceive the true nature of reality. Also pairs well with cheese.\"' WHERE `item`.`id` = 64;
        EOSQL);

        // Iron Ore
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'Billions of years of stellar evolution led to this iron\'s creation before you and your pets dug it up. It\'s witnessed the birth and death of stars, but, you know, it\'s cool with being turned into a sword, or whatever.' WHERE `item`.`id` = 88;
        EOSQL);

        // Corn Syrup
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'Was this extracted from the dreams of cornstalks, or the nightmares of nutritionists? Either way is fine — it only has a subtle effect on the flavor.' WHERE `item`.`id` = 25;
        EOSQL);

        // Cocoa Powder
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'Contains trace amounts of theobromine, a mood-lifting compound. Also contains not-so-trace amounts of deliciousness, a different kind of mood-lifting compound.' WHERE `item`.`id` = 116;
        EOSQL);

        // Mini Chocolate Chip Cookies
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'Archaeologists insist chocolate was once used as currency in ancient Mesoamerica. Now we just put it in cookies. Progress??' WHERE `item`.`id` = 549;
        EOSQL);

        // Scroll of Fruit
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'WARNING: Contents under magical pressure.' WHERE `item`.`id` = 123;
        EOSQL);

        // Regex
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'I know you\'ve been looking for help finding yourself, but unfortunately this object is only good at finding substrings. (I don\'t _think_ you\'re a substring?)' WHERE `item`.`id` = 192;
        EOSQL);

        // Garden Shovel
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'Respect the shovel, for it has seen things. It knows the whispers of the weeds and the skeletons in your compost.' WHERE `item`.`id` = 238;
        EOSQL);

        // Flute
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'A hollow stick that turns breath into music. Or noise, depending on your skill level.' WHERE `item`.`id` = 256;
        EOSQL);

        // Plastic Idol
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'Is it blasphemy or brilliance to 3D print a fertility goddess? The universe hasn\'t decided yet.' WHERE `item`.`id` = 266;
        EOSQL);

        // XOR
        $this->addSql(<<<EOSQL
            UPDATE `item` SET `description` = 'Doubles as a very confusing paperweight.' WHERE `item`.`id` = 281;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
