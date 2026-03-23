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

final class Version20230902084500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<EOSQL
UPDATE `item` SET `description` = 'It has a texture that smells like moonlight, and tastes of Beethoven\'s 10 symphony.' WHERE `item`.`id` = 359;
UPDATE `item` SET `description` = 'Legend speaks of a firework crafted by moonbeams and wishes?? You must be thinking of a more-different firework.' WHERE `item`.`id` = 83;
UPDATE `item` SET `description` = 'Kissed by the flame,\nseasoned by the sea.\nNature\'s culinary\npoetry.' WHERE `item`.`id` = 120;
UPDATE `item` SET `description` = 'Many a living room knight has wielded this very weapon, fighting dragons and shadows alike.' WHERE `item`.`id` = 78;
UPDATE `item` SET `description` = 'Every rusted spot is a remnant of an adventure; every dent, a story of close encounters and narrow escapes on stormy nights and treacherous shores.\n\n_Buuuuuuut_... it\'d probably be more useful if it was repaired. Sorry/not-sorry, treacherous shores: your story was givin\' me nightmares, anyway!' WHERE `item`.`id` = 105;
UPDATE `item` SET `description` = 'Born from wine\'s wilder side, this zesty elixir holds the power to transform dishes and, some say, douse fiery dragon breath!\n\nOr, perhaps it\'s just the secret behind grandma\'s famous pickles... but where\'s the fun in that?' WHERE `item`.`id` = 146;
UPDATE `item` SET `description` = 'Comfort in a bowl.' WHERE `item`.`id` = 172;
UPDATE `item` SET `description` = 'You were probably hoping for a blade that strikes as swiftly and deadly as its namesake.\n\nStill: it does alright.' WHERE `item`.`id` = 186;
UPDATE `item` SET `description` = 'It\'s like having a digital Mary Poppins!' WHERE `item`.`id` = 190;
UPDATE `item` SET `description` = 'The forest\'s finest foliage, and a splash of mammalian magic.' WHERE `item`.`id` = 210;

UPDATE `item` SET `description` = 'There\'s no functional difference between a pair of Yellow Scissors and a pair of Green Scissors. Still: the yellow ones are obviously _way_ better.\n\nI mean: c\'mon! They\'re _yellow!_' WHERE `item`.`id` = 234;
UPDATE `item` SET `description` = 'The first time I bit into an Onion Ring, it was like the humble onion had thrown off its earthy robe and donned a shimmering golden dress, and I was like: \"damn, son, that\'s a fine dress,\" and the Onion Ring was like, \"boy, I know it.\"\n\nYou know how it is.' WHERE `item`.`id` = 245;
UPDATE `item` SET `description` = 'The kind kids play in. Not the kind cats poop in.\n\nThat\'d be an unfortunate different kinds of thing to get mixed up.' WHERE `item`.`id` = 271;
UPDATE `item` SET `description` = 'Imagine if a harmonica and a piano had a baby. And the baby oozed music. Instead of... whatever it is babies are usually oozing.' WHERE `item`.`id` = 286;
UPDATE `item` SET `description` = 'It\'s like berries were having a rave in an oven, and dough was invited, too.\n\n... you always host the _weirdest_ parties.' WHERE `item`.`id` = 296;
UPDATE `item` SET `description` = 'If popcorn had a dating profile, \"Buttered\" would be its most swiped-right pic.' WHERE `item`.`id` = 300; 
UPDATE `item` SET `description` = 'I once wore a Tinfoil Hat to a party. Not for the alien protection, but because it matched my shoes. Little did I know, it not only blocked extraterrestrial eavesdroppers, but also dodged all the gossip. Now, if I can just find shoes that repel unsolicited advice...' WHERE `item`.`id` = 330;
UPDATE `item` SET `description` = 'There is a place in the Umbra where birds are the jewelers of the sky, and the Ruby Feather is their masterpiece. Every time it catches the light, stories of soaring above enchanted forests and gliding over shimmering seas come to life.\n\nIt also makes a nifty bookmark! :D' WHERE `item`.`id` = 345;
UPDATE `item` SET `description` = 'Best eaten on date night, even if it\'s just a date with your couch.' WHERE `item`.`id` = 351;
UPDATE `item` SET `description` = 'Using it may not grant you elvish grace (let\'s be real: there\'s no recovering from your \"little outburst\" at the dance last week), but you\'ll surely be able to read fine print like a boss!' WHERE `item`.`id` = 390;

UPDATE `item` SET `description` = 'Legend has it, in a land of endless winters, someone decided they\'d eat snowflakes if they could, so they made Laufabrauð. Delicate, intricate, and just as unique. Just be sure to enjoy before it melts! (It _does_ melt, right??)' WHERE `item`.`id` = 409;
UPDATE `item` SET `description` = 'It\'s like nature\'s WiFi, for bugs! (Please note: does not boost human WiFi. Tried it. Didn\'t work. Just got weird looks.)' WHERE `item`.`id` = 425; 
UPDATE `item` SET `description` = 'Each spoonful is like diving into a festival of flavors where everyone\'s invited - even that odd bean you never talk to.' WHERE `item`.`id` = 440; 
UPDATE `item` SET `description` = 'I tried to give my Slice of Bread a relaxing bath (don\'t ask why), and the next thing I knew, it was flaunting around, dripping in Egg and Creamy Milk, and demanding to be drizzled with Butter while it rolled around in Sugar! Turns out, I had accidentally stumbled upon the recipe for French Toast. Best kitchen mishap ever! Well: bathroom mishap. You know what I mean!' WHERE `item`.`id` = 446;
UPDATE `item` SET `description` = 'So there I was, trying to give a business presentation, when suddenly my boss pounced on the wall! Turns out he was a cat this whole time, and no one even knew!\n\nAnyway, so that\'s why I always bring a laser pointer to interviews, now. You can never be too careful!' WHERE `item`.`id` = 460;
UPDATE `item` SET `description` = 'I once thought my sword skills were top-notch until I swung and missed... a stationary melon. Then I upgraded to a laser-guided sword. Now, every fruit in my kitchen fears me, and that melon? It\'s a very precise fruit salad.\n\nBeware, melons.\n\nBeware.' WHERE `item`.`id` = 466;
EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
