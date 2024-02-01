<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240201190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'for pets that have crazy-negative social time (saga jellings that "forgot" affectionless)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE pet_house_time SET social_energy=-60*24 WHERE social_energy < -60 * 24');
    }

    public function down(Schema $schema): void
    {
    }
}
