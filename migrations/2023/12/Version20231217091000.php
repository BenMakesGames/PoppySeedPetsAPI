<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231217091000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'juice descriptions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE `item` SET `description` = 'Oh, there\\'s a little slice of Red in it - how faaaancy!' WHERE `item`.`id` = 680;");
        $this->addSql("UPDATE `item` SET `description` = 'A twisty straw! I almost forgot those things existed...' WHERE `item`.`id` = 683;");
        $this->addSql("UPDATE `item` SET `image` = 'juice/carrot' WHERE `item`.`id` = 681;");
        $this->addSql("UPDATE `item` SET `description` = 'Hm? The glowing? Oh, that\\'s fine. That\\'s just the Green Dye. Don\\'t even worry about it.' WHERE `item`.`id` = 347;");

    }

    public function down(Schema $schema): void
    {
    }
}
