<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210329000000 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql("
            UPDATE merit
            SET
                name='Friend of the World',
                description='%pet.name% believes in friendship! They don\'t like to end relationships, and will agree to relationship changes instead of breaking up.'
            WHERE name='Naïve'
            LIMIT 1
        ");
    }

    public function down(Schema $schema) : void
    {
    }
}
