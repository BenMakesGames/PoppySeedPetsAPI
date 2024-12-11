<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241211190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('UPDATE pet SET affection_expressions = \'\'');

        echo "\n";
        echo "***********************************************************************************\n\n";
        echo "Don't forget to run app:assign-affection-expressions to assign new pet expressions!\n\n";
        echo "***********************************************************************************\n\n";

        $this->addSql(<<<EOSQL
        UPDATE `item` SET `description` = 'The wand that smiles back!', `use_actions` = '[[\"Wave\", \"smilingWand\", \"page\"]]' WHERE `item`.`id` = 461;
        EOSQL);
    }

    public function down(Schema $schema): void
    {
    }
}
