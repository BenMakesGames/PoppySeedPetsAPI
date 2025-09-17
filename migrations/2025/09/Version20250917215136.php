<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250917215136 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // bag of beans
        $this->addSql(<<<'EOSQL'
        UPDATE `item` SET `description` = 'This little bag is full of beans.\n\nSame, honestly.' WHERE `item`.`id` = 134; 
        EOSQL);

        // liquid-hot magma
        $this->addSql(<<<'EOSQL'
        UPDATE `item` SET `description` = 'By a pure coincidence of physics, the HEAT contained in a bucket of Liquid-hot Magma is precisely the amount needed to melt virtually any metal object. When correctly used at a Forge, of course! (You shouldn\'t just melt things with Liquid-hot Magma in your living room! Goodness me!)' WHERE `item`.`id` = 353; 
        EOSQL);

        // merchant's other cap
        $this->addSql(<<<'EOSQL'
        UPDATE `item` SET `description` = 'The first one wasn\'t good enough, I guess??\n\nC\'mon, merchants - why y\'all gotta\' be so picky?' WHERE `item`.`id` = 1464; 
        EOSQL);

        // imperturbable toucan
        $this->addSql(<<<'EOSQL'
        UPDATE `item` SET `description` = 'What are those, um, British guards, or whatever, with the hats? Where you can, like, dance around and shit and they won\'t even care? I guess? (Is that just a movie thing?)\n\nAnyway, this toucan is like that. But definitely for real - not just in a movie.' WHERE `item`.`id` = 1460; 
        EOSQL);

        // creepy mask day
        $this->addSql(<<<'EOSQL'
        UPDATE `item` SET `description` = 'Whoa! The concept of a day, _made physical?!_ What sorcery is th-- oh, wait: it\'s just a piece of paper.\n\nYou gotta\' be more clear about this stuff! (What a misleading item name! (Gosh!))' WHERE `item`.`id` = 1406; 
        EOSQL);

        // beat-seeking claymore
        $this->addSql(<<<'EOSQL'
        UPDATE `item` SET `description` = '_Nn-ts! Nn-ts! Nn-ts!_' WHERE `item`.`id` = 1420; 
        EOSQL);

        // curious cutlass
        $this->addSql(<<<'EOSQL'
        UPDATE `item` SET `description` = 'What do Oranges and Avocados have in common?\n\nWho the heck knows! I mean, this cutlass, apparently, but it\'s refusing to answer any questions on the matter!' WHERE `item`.`id` = 1314; 
        EOSQL);

        // super-wrinkled cloth
        $this->addSql(<<<'EOSQL'
        UPDATE `item` SET `description` = 'I once passed out trying to imagine a cloth more wrinkled than this one. True story; true facts.' WHERE `item`.`id` = 1323; 
        EOSQL);

        // coquito
        $this->addSql(<<<'EOSQL'
        UPDATE `item` SET `description` = 'So, what, this is like a tiny coqu? (Isn\'t that what the \"-ito\" suffix does? (I dunno - I took French in high school; not Spanish.))' WHERE `item`.`id` = 1319; 
        EOSQL);

        // the science of ensmallening
        $this->addSql(<<<'EOSQL'
        UPDATE `item` SET `description` = 'Curiously, reading this book will embiggen your mind rather than ensmallen it! Reading\'s just that powerful, y\'all.' WHERE `item`.`id` = 1322; 
        EOSQL);

        // Tile: Very Cool Beans
        $this->addSql(<<<'EOSQL'
        UPDATE `item` SET `description` = 'Augh! _SO_ cool! Whew!' WHERE `item`.`id` = 1325;
        EOSQL);

        // cheshire scarf
        $this->addSql(<<<'EOSQL'
        UPDATE `item` SET `description` = 'Given the ambiguous nature of Fluff, it\'s totally possible this scarf _is_ made from a Cheshire cat. But equally maybe a goat? (Cheshire goat???)' WHERE `item`.`id` = 1336; 
        EOSQL);

        // red red
        $this->addSql(<<<'EOSQL'
        UPDATE `item` SET `description` = 'With a name like this, you\'d think it had Reds in it - like practically brimming with the things!\n\nBut nope; not even one iota of Red is in this Red Red.\n\nThe world\'s a confusing place.' WHERE `item`.`id` = 1344; 
        EOSQL);

        // hot pot
        $this->addSql(<<<'EOSQL'
        UPDATE `item` SET `description` = 'If you haven\'t tried hot pot irl, you simply must. Ideally, go with a few friends and do the hot pot & grill combo. It can be pricey, though! Definitely a special occasions kind of thing. But totes worth it.' WHERE `item`.`id` = 1290; 
        EOSQL);

        // pizza box
        $this->addSql(<<<'EOSQL'
        UPDATE `item` SET `description` = 'Just like a real Pizza Box, you can never be 100% sure about what kind of pizza will be inside until you open it. (Some kind of quantum mechanics shenanigans, I guess.)' WHERE `item`.`id` = 1308; 
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
