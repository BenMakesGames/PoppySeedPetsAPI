<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240625233000 extends AbstractMigration
{
    public const IconUpdates = [
        [ 'title' => 'Moneys', 'icon' => 'fa-solid fa-coins' ],
        [ 'title' => 'Recycling', 'icon' => 'fa-solid fa-recycle' ],
        [ 'title' => 'Market', 'icon' => 'fa-solid fa-store' ],
        [ 'title' => 'Fireplace', 'icon' => 'fa-solid fa-fireplace' ],
        [ 'title' => 'Greenhouse', 'icon' => 'fa-solid fa-bag-seedling' ],
        [ 'title' => 'Beehive', 'icon' => 'fa-solid fa-bee' ],
        [ 'title' => 'Account & Security', 'icon' => 'fa-solid fa-lock-keyhole' ],
        [ 'title' => 'Grocer', 'icon' => 'fa-solid fa-apple-whole' ],
        [ 'title' => 'Hattier', 'icon' => 'fa-solid fa-hat-beach' ],
        [ 'title' => 'Satyr Dice', 'icon' => 'fa-regular fa-dice' ],
        [ 'title' => 'Earth Day', 'icon' => 'fa-solid fa-tree-deciduous' ],
        [ 'title' => 'Fae-kind', 'icon' => 'fa-solid fa-person-dress-fairy' ],
        [ 'title' => 'Trader', 'icon' => 'fa-solid fa-scale-balanced' ],
        [ 'title' => 'Shirikodama', 'icon' => 'fa-solid fa-circle-small' ],
        [ 'title' => 'Halloween', 'icon' => 'fa-solid fa-jack-o-lantern' ],
        [ 'title' => 'Stocking Stuffing Season', 'icon' => 'fa-solid fa-stocking' ],
        [ 'title' => 'Birdbath', 'icon' => 'fa-regular fa-bird' ],
    ];

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        foreach(self::IconUpdates as $update)
        {
            $this->addSql(<<<EOSQL
            UPDATE user_activity_log_tag
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
            UPDATE user_activity_log_tag
            SET emoji = ""
            WHERE title = "{$update['title']}";
            EOSQL);
        }
    }
}
