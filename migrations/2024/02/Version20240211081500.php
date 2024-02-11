<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240211081500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'red envelope item description';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE `item` SET `description` = 'Your pets find these scattered around the island during the Lunar New Year celebrations. How did they get there? Why are they so dang hard to open? These envelopes raise more questions than they answer. (To be fair, that was easy to do, since they didn\'t _answer_ any questions!)' WHERE `item`.`id` = 1220;");
    }

    public function down(Schema $schema): void
    {
    }
}
