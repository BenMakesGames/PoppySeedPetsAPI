<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201201050545 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fireplace ADD stocking_appearance VARCHAR(20) NOT NULL, ADD stocking_color_a VARCHAR(6) NOT NULL, ADD stocking_color_b VARCHAR(6) NOT NULL');

        $this->addSql('
            UPDATE fireplace
            SET
                stocking_color_a=LPAD(CONV(ROUND(RAND()*16777215),10,16),6,0),
                stocking_color_b=LPAD(CONV(ROUND(RAND()*16777215),10,16),6,0),
                stocking_appearance=IF(RAND() < 0.5, \'tasseled\', \'fluffed\')
        ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fireplace DROP stocking_appearance, DROP stocking_color_a, DROP stocking_color_b');
    }
}
