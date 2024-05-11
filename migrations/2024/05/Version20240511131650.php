<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240511131650 extends AbstractMigration
{
    public const IconUpdates = [
        [ 'title' => 'Project-E', 'icon' => 'fa-solid fa-alien-8bit' ],
        [ 'title' => 'Fireplace', 'icon' => 'fa-solid fa-fireplace' ],
        [ 'title' => '3D Printing', 'icon' => 'fa-solid fa-cubes' ],
        [ 'title' => 'Crafting', 'icon' => 'fa-solid fa-scissors' ],
        [ 'title' => 'Astronomy Lab', 'icon' => 'fa-solid fa-telescope' ],
        [ 'title' => 'Beehive', 'icon' => 'fa-solid fa-bee' ],
        [ 'title' => 'Dream', 'icon' => 'fa-solid fa-snooze' ],
        [ 'title' => 'Dumpster-diving', 'icon' => 'fa-solid fa-dumpster' ],
        [ 'title' => 'House Too Full', 'icon' => 'fa-solid fa-box-open-full' ],
        [ 'title' => 'Gathering', 'icon' => 'fa-solid fa-basket-shopping-simple' ],
        [ 'title' => 'Greenhouse', 'icon' => 'fa-solid fa-bag-seedling' ],
        [ 'title' => 'Park Event', 'icon' => 'fa-solid fa-flag-checkered' ],
        [ 'title' => 'Heatstroke', 'icon' => 'fa-solid fa-sun-haze' ],
        [ 'title' => 'Pi Day', 'icon' => 'fa-solid fa-pi' ],
        [ 'title' => 'Stealth', 'icon' => 'fa-solid fa-shoe-prints' ],
        [ 'title' => 'Fighting', 'icon' => 'fa-solid fa-swords' ],
        [ 'title' => 'Eating', 'icon' => 'fa-solid fa-utensils' ],
        [ 'title' => 'Fishing', 'icon' => 'fa-solid fa-fishing-rod' ],
        [ 'title' => 'The Umbra', 'icon' => 'fa-solid fa-moon-over-sun' ],
        [ 'title' => 'Location: Cryovolcano', 'icon' => 'fa-regular fa-volcano' ],
        [ 'title' => 'Location: Escaping Icy Moon', 'icon' => 'fa-regular fa-rocket' ],
        [ 'title' => 'Electronics', 'icon' => 'fa-solid fa-robot' ],
        [ 'title' => 'Programming', 'icon' => 'fa-solid fa-code' ],
        [ 'title' => 'Hunting', 'icon' => 'fa-solid fa-bow-arrow' ],
        [ 'title' => 'Level-up', 'icon' => 'fa-regular fa-book-arrow-up' ],
        [ 'title' => 'Location: The Deep Sea', 'icon' => 'fa-solid fa-wave' ],
        [ 'title' => 'Dark', 'icon' => 'fa-solid fa-eye-slash' ],
        [ 'title' => 'Location: Under a Bridge', 'icon' => 'fa-solid fa-bridge-water' ],
        [ 'title' => 'Location: At Home', 'icon' => 'fa-solid fa-house' ],
        [ 'title' => 'Fae-kind', 'icon' => 'fa-solid fa-person-dress-fairy' ],
        [ 'title' => 'Petting', 'icon' => 'fa-regular fa-hand-heart' ],
        [ 'title' => 'Sick', 'icon' => 'fa-solid fa-flask-round-poison' ],
        [ 'title' => 'Moneys', 'icon' => 'fa-solid fa-coins' ],
        [ 'title' => 'Heart Dimension', 'icon' => 'fa-solid fa-book-heart' ],
        [ 'title' => 'Break-up', 'icon' => 'fa-solid fa-heart-crack' ],
        [ 'title' => 'Shedding', 'icon' => 'fa-solid fa-broom-wide' ],
        [ 'title' => 'Tri-D Chess', 'icon' => 'fa-solid fa-chess' ],
    ];

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pet_activity_log_tag CHANGE emoji emoji VARCHAR(100) NOT NULL');

        foreach(self::IconUpdates as $update)
        {
            $this->addSql(<<<EOSQL
            UPDATE pet_activity_log_tag
            SET emoji = "{$update['icon']}"
            WHERE title = "{$update['title']}";
            EOSQL);
        }
    }

    public function down(Schema $schema): void
    {
        foreach(self::IconUpdates as $update)
        {
            $this->addSql(<<<EOSQL
            UPDATE pet_activity_log_tag
            SET emoji = ""
            WHERE title = "{$update['title']}";
            EOSQL);
        }

        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pet_activity_log_tag CHANGE emoji emoji VARCHAR(12) NOT NULL');
    }
}
