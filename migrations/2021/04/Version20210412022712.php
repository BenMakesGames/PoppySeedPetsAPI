<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210412022712 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_style (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(40) NOT NULL, background_color VARCHAR(6) NOT NULL, pet_info_background_color VARCHAR(6) NOT NULL, speech_bubble_background_color VARCHAR(6) NOT NULL, text_color VARCHAR(6) NOT NULL, primary_color VARCHAR(6) NOT NULL, text_on_primary_color VARCHAR(6) NOT NULL, tab_bar_background_color VARCHAR(6) NOT NULL, link_and_button_color VARCHAR(6) NOT NULL, button_text_color VARCHAR(6) NOT NULL, dialog_link_color VARCHAR(6) NOT NULL, warning_color VARCHAR(6) NOT NULL, gain_color VARCHAR(6) NOT NULL, bonus_and_spice_color VARCHAR(6) NOT NULL, bonus_and_spice_selected_color VARCHAR(6) NOT NULL, input_background_color VARCHAR(6) NOT NULL, input_text_color VARCHAR(6) NOT NULL, INDEX IDX_D17F4332A76ED395 (user_id), UNIQUE INDEX user_id_name_idx (user_id, name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_style ADD CONSTRAINT FK_D17F4332A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user_style');
    }
}
