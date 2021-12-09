<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211208231422 extends AbstractMigration
{
    const DREAMS = [
        [
            'In a dream, %dreamer% was %wandering% %location1%, when they spotted %a_pet_or_monster%. It whispered something, but %dreamer% can\'t remember what, and gave %dreamer% %item%.',
            '%a_pet_or_monster% gave this to %dreamer% in a dream.',
        ],
        [
            'While %wandering% %location1% in a dream, %dreamer% spotted %item%. They reached down and grabbed it, and when they looked up, they were %location2%.',
            '%dreamer% found this in %location1% in a dream.',
        ],
        [
            '%dreamer% dreamed they tripped over %item%, and tumbled into a pit %location1%. The %item% fell in, too, and %dreamer% grabbed it, and ate it.',
            '%dreamer% ate this while falling in a dream.',
        ],
        [
            'In a dream, %dreamer% and a friend were %wandering% %location1%. The friend reached into their pocket and pulled out something covered in cloth. %dreamer% lifted up the cloth, and found %item%. When they looked up, their friend was gone.',
            '%dreamer% received this from a friend in a dream.'
        ],
        [
            '%dreamer% dreamed that they were making out with %a_species% on %surface% %location1%. %Item_with_article% got in the way, so %dreamer% tossed it aside.',
            '%dreamer%, um, found this in a dream.'
        ],
        [
            'In a dream, %dreamer% got in a fight with %a_species% %location1%. The %species% threw %item% at %dreamer%, and declared victory! >:(',
            'A stupid %species% threw this at %dreamer% in a dream.',
        ],
        [
            'In a dream, %dreamer% and a friend went out to eat, and ordered %item_with_article% and %a_food_or_drink%. When the %item% arrived, it was %more% than expected!',
            '%dreamer% ordered this at a restaurant in a dream.',
        ],
        [
            '%dreamer% saw their parents in a dream, but couldn\'t make them out. They hummed a familiar tune, and handed %dreamer% %item%.',
            '%dreamer% got this from their parents in a dream.',
        ],
        [
            'In a dream, %dreamer% found a secret compartment %location1%. They crawled inside, and arrived %location2%. On %surface%, there was %item%. %dreamer% took it.',
            '%dreamer% found this on %surface% %location2% in a dream.',
        ],
        [
            'In a dream, %dreamer% bumped into %a_pet_or_monster%, causing them to drop %item_with_article%. %dreamer% %adverb% picked it up, and tried to call out, but their voice wasn\'t working.',
            '%dreamer% %adverb% picked this up in a dream.'
        ],
        [
            'In a dream, %dreamer% was approached by a huge %item%. They %adverb% ran away; as they did so, the %item% shrank. Eventually, %dreamer% stopped, and picked it up.',
            '%dreamer% was chased by this in a dream. (It was bigger in the dream...)'
        ],
        [
            '%location1% in a dream, %dreamer% looked in a mirror. They were %more% than usual. Also, there was %item_with_article% on their head!',
            '%dreamer% saw this on their head while looking in a mirror in a dream.'
        ],

        // and some new dreams!
        [
            'In a dream, %dreamer% was %wandering% %location1%, and found themselves %location2%, surrounded by %plural_stuff%! %dreamer% spotted %item% in the distance, reached out for it, then woke up.',
            '%dreamer% found this amidst %plural_stuff% in a dream.'
        ],
        [
            'In a dream, %dreamer% was in a room full of %random_stuff%, kissing %a_species%, but the %species% was actually %item_with_article%? Or something? And then they woke up...',
            '%dreamer% was kissing this in a dream?? (Don\'t ask; dreams are weird.)'
        ],
        [
            '%dreamer% thought they were exploring %location1% in a dream, but they were actually %monster%, but super-tiny, exploring %surface%. They found %item_with_article% while wandering around (it was also super-tiny), and woke up.',
            '%dreamer% found this on %surface% in a dream.'
        ],
        [
            '%dreamer% got lost in a maze full of %random_stuff% in a dream. They wandered around for what felt like forever before finally finding the %item% they had been sent to find there. Then they woke up.',
            'In a dream, %dreamer% was sent on a quest to find this... and succeeded!'
        ]
    ];

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dream (id INT AUTO_INCREMENT NOT NULL, description LONGTEXT NOT NULL, item_description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        foreach(self::DREAMS as $dream)
        {
            $this->addSql(
                'INSERT INTO dream (description, item_description) VALUES (:description, :itemDescription)',
                [ 'description' => $dream[0], 'itemDescription' => $dream[1] ]
            );
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE dream');
    }
}
