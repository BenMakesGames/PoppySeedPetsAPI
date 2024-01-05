<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240104210001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'fairy floss! (2 of 2)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `use_actions` = '[[\"Say Hello\",\"fairy/#/hello\"],[\"Build a Fireplace\",\"fairy/#/buildFireplace\"],[\"Ask about Quintessence\",\"fairy/#/quintessence\"],[\"Make Fairy Floss\",\"fairy/#/makeFairyFloss\"]]' WHERE `item`.`id` = 333; 
        EOSQL);

        $this->addSql('UPDATE `pet_species` SET `sheds_id` = 1358 WHERE `pet_species`.`id` = 3;');
    }

    public function down(Schema $schema): void
    {
    }
}
