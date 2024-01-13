<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240112194500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'renaming Chinese New Year Box to Lunar New Year Box';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<EOSQL
        UPDATE `item` SET `name` = 'Lunar New Year Box',`use_actions` = '[[\\"Open\\",\\"box/lunarNewYear/#/open\\"]]' WHERE `item`.`id` = 922;
        EOSQL);

        $this->addSql("UPDATE `pet_activity_log_tag` SET `title` = 'Lunar New Year' WHERE `pet_activity_log_tag`.`id` = 72;");
    }

    public function down(Schema $schema): void
    {
    }
}
